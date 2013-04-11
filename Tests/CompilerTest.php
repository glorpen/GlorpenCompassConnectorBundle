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

use Assetic\Asset\FileAsset;

use Glorpen\CompassConnectorBundle\Filter\CompassConnectorFilter;

/**
 * @author Arkadiusz Dzięgiel
 */
class CompilerTest extends \PHPUnit_Framework_TestCase {
	public function testSomething(){
		$filter = new CompassConnectorFilter(
			__DIR__.'/cache',
			getenv("HOME").'/.gem/ruby/1.9.1/bin/compass',
			'Glorpen\CompassConnectorBundle\Tests\SymfonyConnectorStub'
		);
		
		$filter->setSassRoot(__DIR__.'/..');
		$filter->setVendorsPath(__DIR__.'/Resources/public/vendors');
		$filter->setGeneratedImagesPath(__DIR__.'/cache/generated_images');
		
		$asset = new FileAsset(__DIR__.'/Resources/scss/app.scss');
		$filter->filterDump($asset);
		$asset->getContent();
	}
}