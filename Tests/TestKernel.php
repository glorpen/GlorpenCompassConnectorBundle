<?php
namespace Glorpen\Assetic\CompassConnectorBundle\Tests;

use Symfony\Component\HttpKernel\Bundle\Bundle;

use Symfony\Component\Config\Loader\LoaderInterface;

use Symfony\Component\HttpKernel\Kernel;

class TestBundle extends Bundle {
	public function getPath() {
		return __DIR__.'/TestBundle';
	}
}

class TestKernel extends Kernel {
	
	public function registerBundles(){
		return array(
				new TestBundle()
		);
	}
	
	public function registerContainerConfiguration(LoaderInterface $loader){
		
	}
	
}
