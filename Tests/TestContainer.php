<?php
namespace Glorpen\CompassConnectorBundle\Tests;

use Symfony\Component\DependencyInjection\ScopeInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpKernel\Kernel;

class TestBundle {
	public function getName(){
		return 'SomeBundle';
	}
	public function getPath(){
		return __DIR__;
	}
}

class TestContainer extends \PHPUnit_Framework_TestCase implements ContainerInterface {
	
	protected $kernel, $bundle;
	
	protected function getSomeBundle(){
		if($this->bundle === null){
			$this->bundle = new TestBundle;
		}
		return $this->bundle;
	}
	
	protected function getKernel(){
		if($this->kernel === null){
			$this->kernel = $this->getMockForAbstractClass(
					'Symfony\Component\HttpKernel\Kernel',array(),'',false,false,true,array('getBundle','getBundles')
			);
			
			
			
			$this->kernel
			->expects($this->any())
			->method("getBundle")
			->will($this->returnValue($this->getSomeBundle()));
			
			$this->kernel
			->expects($this->any())
			->method("getBundles")
			->will($this->returnValue(array($this->getSomeBundle())));
		}
		
		return $this->kernel;
	}
	
	public function set($id, $service, $scope = self::SCOPE_CONTAINER){}
	public function get($id, $invalidBehavior = self::EXCEPTION_ON_INVALID_REFERENCE){
		switch($id){
			case 'kernel': return $this->getKernel();
		}
	}
	public function has($id){}
	public function getParameter($name){
		switch($name){
			case 'kernel.root_dir': return __DIR__;
			case 'assetic.filter.compass_connector.cache_path': return __DIR__.'/cache';
			case 'assetic.filter.compass_connector.vendors.path': return __DIR__.'/Resources/public/vendors';
			case 'assetic.filter.compass_connector.vendors.web': return '/vendors';
			case 'assetic.filter.compass_connector.generated_images.path': return __DIR__.'/cache/generated_images';
			case 'assetic.filter.compass_connector.generated_images.web': return '/generated_images';
		}
	}
	public function hasParameter($name){}
	public function setParameter($name, $value){}
	public function enterScope($name){}
	public function leaveScope($name){}
	public function addScope(ScopeInterface $scope){}
	public function hasScope($name){}
	public function isScopeActive($name){}
}