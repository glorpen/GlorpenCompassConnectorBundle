<?php
namespace Glorpen\CompassConnectorBundle\Tests;

use Assetic\Asset\FileAsset;

use Glorpen\CompassConnectorBundle\Filter\CompassConnectorFilter;

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