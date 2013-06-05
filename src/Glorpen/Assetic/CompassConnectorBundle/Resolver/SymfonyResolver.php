<?php
namespace Glorpen\Assetic\CompassConnectorBundle\Resolver;

use Symfony\Component\Templating\Asset\Package;

use Symfony\Component\Finder\Finder;

use Symfony\Component\HttpKernel\KernelInterface;

use Glorpen\Assetic\CompassConnectorFilter\Resolver\SimpleResolver;

class SymfonyResolver extends SimpleResolver {
	
	protected $kernel;
	
	public function __construct(KernelInterface $kernel, $outputDir, $scheme, $host, $baseUrl, $vendorPrefix) {
		$this->kernel = $kernel;
		
		$this->setAppPrefix("{$scheme}://{$host}{$baseUrl}");
		$this->setVendorPrefix($this->appPrefix.$vendorPrefix);
		
		parent::__construct(null, $outputDir);
	}
	
	//TODO: first search in vendor under web/
	
	public function listVPaths($vpath, $isVendor){
		
		list($pre, $post) = explode('*', $vpath, 2);
		$info = $this->resolveVPath($pre, $isVendor, 'image');
		
		$finder = Finder::create()->in($info->path)->files();
		$ret = array();
		foreach($finder as $f){
			$ret[] = '@'.$info->bundle.':'.$info->resource.'/'.$f->getBasename();
		}
		
		return $ret;
	}
	
	protected function resolveVPath($vpath, $isVendor, $type=null){
		
		$postfix = '';
		if(strpos($vpath, '?')!==false){
			list($vpath, $postfix) = explode('?', $vpath);
		}
		
		$bundlesToSearch = array();
		if($isVendor){
			$path = $vpath;
			foreach($this->kernel->getBundles() as $b){
				$bundlesToSearch[] = $b->getName();
			}
		} else {
			list($bundle, $path) = explode(':', $vpath,2);
			$bundlesToSearch[] = $bundle;
		}
		
		foreach($bundlesToSearch as $bundleName){
			try{
				$resourcePath = ($isVendor?'public/'.$this->vendorDir.'/'.$this->{"vendor".ucfirst($type)."sDir"}.'/':'').trim($path,'/');
				$filePath = $this->kernel->locateResource('@'.$bundleName.'/Resources/'.$resourcePath);
				
				return (object) array(
						'resource' => $resourcePath,
						'path' => $filePath,
						'bundle' => $bundleName,
						'postfix' => $postfix
				);
				
			} catch (\InvalidArgumentException $e) {
				//echo $e->getMessage();
			}
		}
	}
	
	protected function resolveBundlePath($vpath, $isVendor, $type=null){
		if($r=$this->resolveVPath($vpath, $isVendor, $type)){
			return $r->path;
		}
	}
	
	public function getFilePath($vpath, $isVendor, $type){
		return $this->resolveBundlePath($vpath, $isVendor, $type);
	}
	
	public function getUrl($vpath, $isVendor, $type){
		$vpathFile = '';
		if($type == 'generated_image'){
			if($isVendor){
				return parent::getUrl($vpath, $isVendor, $type);
			} else {
				$vpathFile = '/'.basename($vpath);
				$vpath = dirname($vpath);
			}
		}
		
		$info = $this->resolveVPath($vpath, $isVendor, $type);
		
		if(!$info) throw new \RuntimeException();
		if(strpos($info->resource,'public')!==0){
			throw new \RuntimeException();
		}
		
		$prefix = $type == 'generated_image'?$this->generatedPrefix:$this->appPrefix;
		
		return $prefix.'/'.$this->getBundleWebPrefix($info->bundle).substr($info->resource, 7).$info->postfix.$vpathFile;
	}
	
	private function getBundleWebPrefix($name){
		return 'bundles/'.strtolower(substr($name,0,-6)).'/';
	}
	
	public function getOutFilePath($vpath, $type, $isVendor){
		
		if(!$isVendor){
			list($bundle, $path) = explode(':', $vpath,2);
			$info = $this->resolveVPath($bundle.':', false);
			return $this->outputDir.'/'.$this->generatedDir.'/'.$this->getBundleWebPrefix($info->bundle).preg_replace('#.*?public/#','/', $path);
		} else {
			return parent::getOutFilePath($vpath, $type, $isVendor);
		}
	}
}
