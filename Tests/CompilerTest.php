<?php
namespace Glorpen\CompassConnectorBundle\Tests;

use Assetic\Asset\FileAsset;

use Glorpen\CompassConnectorBundle\Filter\CompassConnectorFilter;

class CompilerTest extends \PHPUnit_Framework_TestCase {
	public function testSomething(){
		$filter = new CompassConnectorFilter(
			__DIR__.'/cache',
			'/home/arkus/.gem/ruby/1.9.1/bin/compass',
			__DIR__.'/compiler.php'
		);
		$asset = new FileAsset(__DIR__.'/Resources/scss/app.scss');
		$filter->filterDump($asset);
		$asset->getContent();
	}
}