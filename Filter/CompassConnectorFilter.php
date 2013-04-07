<?php
namespace Glorpen\CompassConnectorBundle\Filter;

use Assetic\Filter\BaseProcessFilter;

use Assetic\Asset\AssetInterface;

class CompassConnectorFilter extends BaseProcessFilter {
	
	protected $plugins = array();
	protected $cache, $compassBin, $console;
	
	public function __construct($cachePath, $compassBin, $console){
		$this->cache = $cachePath;
		$this->compassBin = $compassBin;
		$this->console = $console;
	}
	
	public function setPlugins(array $plugins){
		$this->plugins = $plugins;
	}
	
	/**
	 * @param AssetInterface $asset
	 */
	public function filterLoad(AssetInterface $asset) {

	}
	
	/**
	 * @param AssetInterface $asset
	 */
	public function filterDump(AssetInterface $asset) {
		$path = $asset->getSourceRoot().'/'.$asset->getSourcePath();
		$path = strtr($path, array(dirname(dirname($this->console))=>''));
		$path = ltrim($path, "/");
		
		$pb = $this->createProcessBuilder(array($this->compassBin,'compile'));
		$pb->inheritEnvironmentVariables();
		$pb->setEnv('COMPASS_CONNECTOR', $this->console.' '.'compass-connector:compile');
		$pb->setEnv('HOME', '/home/arkus');
		foreach($this->plugins as $plugin){
			$pb->add('-r')->add($plugin);
		}
		$pb->add('-r')->add('zurb-foundation');
		$pb->add('-r')->add('compass-connector');
		//$pb->add($this->cache);
		$pb->add($path);
		
		$pb->getProcess()->run(function($status, $output){
			echo $output;
		});
		
		$cssPath = implode(DIRECTORY_SEPARATOR, array($this->cache,'css',str_replace('.scss','.css',$path)));
		$asset->setContent(file_get_contents($cssPath));
	}

}
