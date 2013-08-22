<?php
namespace Glorpen\Assetic\CompassConnectorBundle\Resolver;

use Glorpen\Assetic\CompassConnectorFilter\CompassProcess;

use Symfony\Component\DependencyInjection\Exception\ScopeWideningInjectionException;

use Symfony\Component\DependencyInjection\Exception\LogicException;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Templating\Asset\PackageInterface;

use Symfony\Component\Finder\Finder;

use Symfony\Component\HttpKernel\KernelInterface;

use Glorpen\Assetic\CompassConnectorFilter\Resolver\SimpleResolver;

class SymfonyResolver extends SimpleResolver {
	
	protected $kernel;
	protected $container;
	protected $publicDir;
	
	public function __construct(KernelInterface $kernel, $outputDir, $vendorPrefix, PackageInterface $asseticPackage = null) {
		$this->kernel = $kernel;
		$this->vendorPrefixPath = $vendorPrefix;
		
		if($asseticPackage){
			$appPrefix = rtrim(current(explode('?',$asseticPackage->getUrl(''))), '/');
		} else {
			$appPrefix = '';
		}
		$this->setAppPrefix($appPrefix);
		$this->setVendorPrefix($appPrefix.$vendorPrefix);
		$this->publicDir = $outputDir;
		
		parent::__construct(null, $outputDir);
	}
	
	public function listVPaths($vpath, $mode){
		
		list($pre, $post) = explode('*', $vpath, 2);
		$info = $this->resolveVPath($pre, $mode, 'image');
		
		$finder = Finder::create()->in($info->path)->files();
		$ret = array();
		foreach($finder as $f){
			if($mode == CompassProcess::MODE_ABSOLUTE){
				$ret[] = '/'.$info->resource.'/'.$f->getBasename();
			} else {
				$ret[] = '@'.($info->bundle?($info->bundle.':'):'').$info->resource.'/'.$f->getBasename();
			}
		}
		
		return $ret;
	}
	
	/**
	 * Only used for importing/reading local files/directories. Cannot be converted to url.
	 * @param string $path
	 * @return StdClass|null
	 */
	private function resolveAppPath($vpath){
		$appResources = $this->kernel->getRootDir().'/Resources';
		$path = realpath($appResources.'/'.trim($vpath,'/'));
		return (object) array(
				'resource' => trim($vpath,'/'),
				'path' => $path,
				'bundle' => null,
				'postfix' => null
		);
	}
	
	/**
	 * Resolves dir or file path.
	 * @param string $vpath
	 * @param boolean $isVendor
	 * @param string $type
	 * @return StdClass|null
	 */
	protected function resolveVPath($vpath, $mode, $type=null){
		
		$isVendor = $mode == CompassProcess::MODE_VENDOR;
		
		$postfix = '';
		if(strpos($vpath, '?')!==false){
			list($vpath, $postfix) = explode('?', $vpath);
		}
		
		$appResources = $this->kernel->getRootDir().'/Resources';
		$bundlesToSearch = array();
		
		switch($mode){
			case CompassProcess::MODE_VENDOR:
				$path = $vpath;
				foreach($this->kernel->getBundles() as $b){
					$bundlesToSearch[] = $b->getName();
				}
				break;
			case CompassProcess::MODE_APP:
				if(strpos($vpath, ':') === false){ //global resource path
					return $this->resolveAppPath($vpath);
				} else { //bundle path
					list($bundle, $path) = explode(':', $vpath,2);
					$bundlesToSearch[] = $bundle;
				}
				break;
			case CompassProcess::MODE_ABSOLUTE:
				$realpath = $this->publicDir.'/'.$vpath;
				return (object) array(
						'resource' => trim($vpath,'/'),
						'path' => $realpath,
						'bundle' => null,
						'postfix' => $postfix
				);
				break;
		}
		
		foreach($bundlesToSearch as $bundleName){
			try{
				$resourcePath = ($isVendor?'public/'.$this->vendorDir.'/'.$this->{"vendor".ucfirst($type)."sDir"}.'/':'').trim($path,'/'); // public/....
				$filePath = $this->kernel->locateResource('@'.$bundleName.'/Resources/'.$resourcePath, $appResources, true);
				
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
	
	public function getFilePath($vpath, $mode, $type){
		if($r=$this->resolveVPath($vpath, $mode, $type)){
			return $r->path;
		}
	}
	
	public function getUrl($vpath, $mode, $type){
		
		$isVendor = $mode == CompassProcess::MODE_VENDOR;
		
		$vpathFile = '';
		$isGeneratedImage = $type == CompassProcess::TYPE_GENERATED_IMAGE;
		if($isGeneratedImage){
			if($isVendor){
				return parent::getUrl($vpath, $isVendor, $type);
			} else {
				$vpathFile = '/'.basename($vpath);
				$vpath = dirname($vpath);
			}
		}
		
		$info = $this->resolveVPath($vpath, $mode, $type);
		
		if(!$info) throw new \RuntimeException(sprintf('Could not resolve "%s"', $vpath));
		if(!$isGeneratedImage && (!$mode==CompassProcess::MODE_ABSOLUTE && (strpos($info->resource,'public')!==0 || $info->bundle === null))){
			throw new \RuntimeException(sprintf('Resolved path for "%s" is not public', $vpath));
		}
		
		$prefix = $this->appPrefix.($isGeneratedImage?$this->generatedPrefix:'');
		
		if($isGeneratedImage && $info->bundle === null){
			$dirpath = 'global/'.$info->resource; //sprites from app/Resource or web/
		} elseif ($mode == CompassProcess::MODE_ABSOLUTE){
			$dirpath = trim($vpath,'/');
		} else {
			$dirpath = $this->getBundleWebPrefix($info->bundle).substr($info->resource, 7);
		}
		
		return $prefix.'/'.$dirpath.$info->postfix.$vpathFile;
	}
	
	private function getBundleWebPrefix($name){
		return 'bundles/'.strtolower(substr($name,0,-6)).'/';
	}
	
	public function getOutFilePath($vpath, $type, $isVendor){
		
		if(!$isVendor){
			if(strpos($vpath, ':') === false){ //global resource path
				return $this->outputDir.'/'.$this->generatedDir.'/global/'.$vpath;
			} else {
				list($bundle, $path) = explode(':', $vpath,2);
				$info = $this->resolveVPath($bundle.':', CompassProcess::MODE_APP, null);
				return $this->outputDir.'/'.$this->generatedDir.'/'.$this->getBundleWebPrefix($info->bundle).preg_replace('#.*?public/#','/', $path);
			}
		} else {
			return parent::getOutFilePath($vpath, $type, $isVendor);
		}
	}
}
