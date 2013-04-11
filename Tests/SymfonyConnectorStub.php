<?php

/*
 * This file is part of the GlorpenCompassConnectorBundle package.
 *
 * (c) Akradiusz Dzięgiel
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Glorpen\CompassConnectorBundle\Tests;

use Glorpen\CompassConnectorBundle\Connector\Symfony2Connector;

use Assetic\Asset\FileAsset;

use Glorpen\CompassConnectorBundle\Filter\CompassConnectorFilter;

/**
 * @author Arkadiusz Dzięgiel
 */
class TestBundle {
	public function getName(){
		return 'SomeBundle';
	}
	public function getPath(){
		return __DIR__;
	}
}

/**
 * @author Arkadiusz Dzięgiel
 */
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

/**
 * @author Arkadiusz Dzięgiel
 */
class SymfonyConnectorStub extends Symfony2Connector {
	public function initialize(){
		
	}
	
	public function getKernel(){
		return new TestKernel();
	}
}