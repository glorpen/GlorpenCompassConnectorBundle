<?php
namespace Glorpen\CompassConnectorBundle\Connector;

use Glorpen\CompassConnectorBundle\Connector\BaseConnector;

class Symfony2Connector extends BaseConnector {
	
	protected $config, $kernel;
	
	public function __construct(array $config){
		parent::__construct($config);
	}
	
	public function initialize(){
		$root = '/mnt/sandbox/workspace-php/TendersSystem/app';
		require_once $root.'/bootstrap.php.cache';
		require_once $root.'/AppKernel.php';
	}
	
	public function getKernel(){
		if($this->kernel === null){
			$this->kernel = new \AppKernel('dev', false);
			$this->kernel->init();
			$this->kernel->boot();
		}
		
		return $this->kernel;
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
		return preg_replace('#/+#', '/', $dir.'/'.$uri);
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
		return array_merge(parent::getConfiguration(), array(
			'sass_path' => dirname($this->getKernel()->getRootDir()),
		));
	}
}
