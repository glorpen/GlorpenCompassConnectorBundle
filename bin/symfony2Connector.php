<?php

require_once dirname(dirname(__DIR__)).'/bootstrap.php.cache';
require_once dirname(dirname(__DIR__)).'/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

class Connector {
	
	protected $config, $kernel;
	
	public function __construct(array $config){
		$this->config = $config;
	}
	
	public function getKernel(){
		if($this->kernel === null){
			$this->kernel = new AppKernel('dev', false);
			$this->kernel->init();
			$this->kernel->boot();
		}
		
		return $this->kernel;
	}
	
	public function writeln($msg){
		echo $msg."\n";
	}
	
	public static function camelize($id){
		return preg_replace_callback('/(^|_|\.)+(.)/', function ($match) { return ('.' === $match[1] ? '_' : '').strtoupper($match[2]); }, $id);
	}
	
	public function execute() {
		while(True){
			$ret = fgets(STDIN, 4096);
			if (false === $ret) { //connector was disconnected
				exit(0);
			}
			$line = trim($ret);
			$d = json_decode($line, true);
			
			try {
				$this->writeln(json_encode(call_user_func_array(array($this, lcfirst(self::camelize($d['method']))), $d['args'])));
			} catch(\Exception $e){
				$this->writeln(json_encode(array(
					'error'=> $e->getMessage()."\n".$e->getTraceAsString()
				)));
			}
		}
	}
	
	protected function checkAbsoluteUrl($url){
		return preg_match('#^(([a-z0-9]+://)|(//)).*$#', $url) == 1;
	}
	protected function checkAppUrl($url){
		return strncmp($url, "/bundles/", 9)==0;
	}
	protected function checkRootUri($uri){
		return $this->checkAbsoluteUrl($uri) || @$uri[0] == '/';
	}
	
	protected function getVendorsPath(){
		$dir = $this->config['vendors.path'];
		return implode(DIRECTORY_SEPARATOR, array_merge(array($dir), func_get_args()));
	}
	
	protected function getVendorsWeb(){
		$dir = $this->config['vendors.web'];
		return implode('/', array_merge(array($dir), func_get_args()));
	}
	
	protected function getBundlePath($path){
		preg_match('#^/bundles/([a-z0-9]+)/(.*)$#', $path, $matches);
		foreach($this->getKernel()->getBundles() as $b){
			if($matches[1] === strtolower(substr($b->getName(),0,-6))){
				return implode(DIRECTORY_SEPARATOR, array($b->getPath(), 'Resources','public',$matches[2]));
			}
		}
		throw new \Exception('Bundle not found');
	}
	
	public function findImage($path){
		if($this->checkAbsoluteUrl($path)){
			return $path;
		}
		
		if($this->checkAppUrl($path)){
			return $this->getBundlePath($path);
		} else {
			//vendors
			return $this->getVendorsPath('images', $path);
		}
	}
	
	public function getStylesheetUrl($uri){
		if($this->checkRootUri($uri)) return $uri;
		//vendors
		return $this->getVendorsWeb($uri);
	}
	
	public function getImageUrl($uri){
		if($this->checkRootUri($uri)){
			return $uri;
		} else {
			//vendors
			return $this->getVendorsWeb('images', $uri);
		}
	}
	
	public function findScss($path){
		if(strpos($path, ':')!==false){
			list($bundleName, $scssPath) = explode(':', $path);
			$bundle = $this->getKernel()->getBundle($bundleName);
			$path = implode(DIRECTORY_SEPARATOR, array($bundle->getPath(), 'Resources','scss', $scssPath));
			$info = pathinfo($path);
			
			if(substr_compare($info['filename'], '.scss', -4)!==0){
				$info['filename'].='.scss';
			}
			
			if(file_exists($path = implode(DIRECTORY_SEPARATOR, array($info['dirname'], $info['filename'])))){
				return $path;
			} else {
				return implode(DIRECTORY_SEPARATOR, array($info['dirname'], '_'.$info['filename']));
			}
		} else {
			return $path;
		}
	}
	
	public function findGeneratedImage($uri){
		$dir = $this->config['generated_images.path'];
		return $dir.DIRECTORY_SEPARATOR.$uri;
	}
	
	public function getGeneratedImageUrl($uri){
		if($this->checkAbsoluteUrl($uri)) return $uri;
		
		$dir = $this->config['generated_images.web'];
		return $dir.$uri;
	}
	
	public function findSpritesMatching($path){
		$p = $this->getBundlePath($path);
		return glob($p); //TODO should it return /bundles/some/... path?
	}
	
	public function findSprite($path){
		return $path;
	}
	
	public function getFontUrl($uri){
		if($this->checkRootUri($uri)){
			return $uri;
		} else {
			return $this->getVendorsWeb('fonts', $uri);
		}
	}
	
	public function findFont($path){
		if($this->checkAppUrl($path)){
			return $this->getBundlePath($path);
		} else {
			return $this->getVendorsPath('fonts', $path);
		}
	}
	
	public function getConfiguration(){
		return array(
			'project_path' => $this->config['cache_path'],
			'sass_path' => dirname($this->getKernel()->getRootDir()),
			'css_path' => $this->config['cache_path'].'/css',
			'generated_images_path' => $this->config['generated_images.path'],
			'environment' => ':'.$this->config['env']
		);
	}
}

$c = new Connector(CONNECTOR_CONFIG);
$c->execute();
