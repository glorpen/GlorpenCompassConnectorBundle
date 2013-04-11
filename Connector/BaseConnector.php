<?php

/*
 * This file is part of the GlorpenCompassConnectorBundle package.
 *
 * (c) Akradiusz Dzięgiel
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glorpen\CompassConnectorBundle\Connector;

/**
 * Base connector class.
 * @author Arkadiusz Dzięgiel
 */
abstract class BaseConnector {
	
	protected $config;
	
	public function __construct(array $config){
		$this->config = $config;
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
		return preg_replace('#/+#', '/', implode('/', array_merge(array($dir), func_get_args())));
	}
	
	abstract public function findImage($path);
	abstract public function getStylesheetUrl($uri);
	abstract public function getImageUrl($uri);
	abstract public function findScss($path);
	abstract public function findGeneratedImage($uri);
	abstract public function getGeneratedImageUrl($uri);
	abstract public function findSpritesMatching($path);
	abstract public function findSprite($path);
	abstract public function getFontUrl($uri);
	abstract public function findFont($path);
	
	public function getConfiguration(){
		return array(
			'project_path' => $this->config['cache_path'],
			'sass_path' => $this->config['sass_root'],
			'css_path' => $this->config['cache_path'].'/css',
			'generated_images_path' => $this->config['generated_images.path'],
			'environment' => ':'.$this->config['env']
		);
	}
}
