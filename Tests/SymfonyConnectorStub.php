<?php
namespace Glorpen\CompassConnectorBundle\Tests;

use Glorpen\CompassConnectorBundle\Connector\Symfony2Connector;

use Assetic\Asset\FileAsset;

use Glorpen\CompassConnectorBundle\Filter\CompassConnectorFilter;

class TestBundle {
	public function getName(){
		return 'SomeBundle';
	}
	public function getPath(){
		return __DIR__;
	}
}

class TestKernel {
	public function getBundle(){
		return new TestBundle();
	}
	public function getBundles(){
		return array($this->getBundle());
	}
	public function getRootDir(){
		return __DIR__;
	}
}

class SymfonyConnectorStub extends Symfony2Connector {
	public function initialize(){
		
	}
	
	public function getKernel(){
		return new TestKernel();
	}
}