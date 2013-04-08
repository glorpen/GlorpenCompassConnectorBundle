<?php
namespace Glorpen\CompassConnectorBundle\Filter;

use Assetic\Filter\BaseProcessFilter;

use Assetic\Asset\AssetInterface;

class CompassConnectorFilter extends BaseProcessFilter {
	
	protected $plugins = array(), $homeDir;
	protected $cache, $compassBin, $connector, $sassRoot;
	
	protected $generatedImagesPath, $generatedImagesWeb, $env, $vendorsPath, $vendorsWeb;
	protected $asseticFix;
	
	public function __construct($cachePath, $compassBin, $connector){
		$this->cache = $cachePath;
		$this->compassBin = $compassBin;
		$this->connector = $connector;
		
		$this->generatedImagesPath = $this->cache.'/generatedImages';
		$this->generatedImagesWeb = "/";
		$this->vendorsPath = $this->cache.'/vendors';
		$this->vendorsWeb = "/vendors";
		
		$this->sassRoot = $this->cache;
		$this->env = 'development';
		
		$this->asseticFix = false;
	}
	
	public function setPlugins(array $plugins){
		$this->plugins = $plugins;
	}
	
	public function setHome($dir){
		$this->homeDir = $dir;
	}
	
	public function setGeneratedImagesPath($path){
		$this->generatedImagesPath = $path;
	}
	public function setGeneratedImagesWeb($uri){
		$this->generatedImagesWeb = $uri;
	}
	
	public function enableAsseticMtimeFix($set = true){
		$this->asseticFix = $set;
	}
	
	/**
	 * production or development
	 * @param unknown $env
	 */
	public function setEnvironment($env){
		$this->env = $env;
	}
	public function setVendorsPath($path){
		$this->vendorsPath = $path;
	}
	public function setVendorsWeb($path){
		$this->vendorsWeb = $path;
	}
	/**
	 * Scss files root, probably your project root.
	 * @param string $path
	 */
	public function setSassRoot($path){
		$this->sassRoot = $path;
	}
	
	/**
	 * @param AssetInterface $asset
	 */
	public function filterLoad(AssetInterface $asset) {}
	
	protected function buildConnector(){
		$data = file_get_contents($this->connector);
		$data = strtr($data,array('CONNECTOR_CONFIG' => var_export(array(
			'cache_path' => $this->cache,
			'generated_images.path' => $this->generatedImagesPath,
			'generated_images.web' => $this->generatedImagesWeb,
			'vendors.path' => $this->vendorsPath,
			'vendors.web' => $this->vendorsWeb,
			'sass_root' => $this->sassRoot,
			'env' => $this->env
		),true)));
		
		file_put_contents($this->getConnectorPath(), $data);
	}
	
	protected function getConnectorPath(){
		@mkdir($this->cache, 0777, true);
		return $this->cache.'/'.'connector.php';
	}
	
	/**
	 * @param AssetInterface $asset
	 */
	public function filterDump(AssetInterface $asset) {
		
		$this->buildConnector();
		
		$fpath = $asset->getSourceRoot().'/'.$asset->getSourcePath();
		$path = strtr($fpath, array(realpath($this->sassRoot)=>''));
		$path = ltrim($path, "/");
		
		if($this->asseticFix){
			@touch($fpath);
		}
		
		$pb = $this->createProcessBuilder(array($this->compassBin,'compile'));
		$pb->inheritEnvironmentVariables();
		$pb->setEnv('COMPASS_CONNECTOR', 'php '.$this->getConnectorPath());
		
		if($this->homeDir){
			$pb->setEnv('HOME', $this->homeDir);
		}
		
		foreach($this->plugins as $plugin){
			$pb->add('-r')->add($plugin);
		}
		$pb->add('-r')->add('compass-connector');
		$pb->add($fpath);
		
		$pb->getProcess()->run(/*function($status, $output){
			echo $output;
		}*/);
		
		$cssPath = implode(DIRECTORY_SEPARATOR, array($this->cache,'css',str_replace('.scss','.css',$path)));
		$asset->setContent(file_get_contents($cssPath));
	}

}
