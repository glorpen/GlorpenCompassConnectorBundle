<?php
namespace Glorpen\Assetic\CompassConnectorBundle\Tests;

use Symfony\Component\Templating\Asset\PathPackage;

use Symfony\Component\Templating\Helper\CoreAssetsHelper;

use Glorpen\Assetic\CompassConnectorFilter\Filter;

use Assetic\Asset\FileAsset;

use Assetic\Asset\AssetCollection;

use Glorpen\Assetic\CompassConnectorBundle\Resolver\SymfonyResolver;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompilationTest extends WebTestCase {
	
	protected $myKernel;
	
	static protected function getKernelClass(){
		return 'Glorpen\Assetic\CompassConnectorBundle\Tests\TestKernel';
	}
	
	protected function getAssetCollection($filename){
	
		if(!$this->myKernel){
			$this->myKernel = self::createKernel();
			$this->myKernel->boot();
		}
		
		$package = $this->getMockBuilder('Symfony\Component\Templating\Asset\PackageInterface')
		->getMockForAbstractClass();
		
		$package->expects($this->any())
		->method('getUrl')->will($this->returnValue('http://test.host.com/some-prefix'));
		
		$resolver = new SymfonyResolver(
				$this->myKernel,
				implode(DIRECTORY_SEPARATOR,array(__DIR__,'Resources','web')),
				'vendor', $package
		);
	
		$css = new AssetCollection(array(
				new FileAsset(implode(DIRECTORY_SEPARATOR, array(__DIR__,'Resources','scss',$filename))),
		), array(
				new Filter($resolver, __DIR__.'/cache',$_SERVER['HOME'].'/.gem/ruby/1.9.1/bin/compass')
		));
		return $css;
	}
	
	public function testSimple(){
		$css = $this->getAssetCollection('test_simple.scss');
		$this->assertContains('color: red', $css->dump());
	}
	
	public function testSimpleImport(){
		$css = $this->getAssetCollection('test_simple_imports.scss');
		$data = $css->dump();
		$this->assertContains('color: red', $data);
		$this->assertContains('app-resource', $data);
	}
	
	public function testFonts(){
		$css = $this->getAssetCollection('test_fonts.scss');
		$out = $css->dump();
		
		$this->assertContains('/bundles/test/vendor/fonts/vendor_empty.ttf', $out);
		$this->assertContains('/bundles/test/fonts/empty.ttf', $out);
		$this->assertContains("'/this.eot'", $out);
	
		$this->assertContains("app-inline-font: url('data:font/truetype;base64", $out);
		$this->assertContains("vendor-inline-font: url('data:font/truetype;base64", $out);
	}
	
	public function testImages(){
		$css = $this->getAssetCollection('test_images.scss');
		$out = $css->dump();
	
		$this->assertContains("'http://test.host.com/some-prefix/bundles/test/vendor/images/vendor_1x1.png?", $out);
		$this->assertContains("'http://test.host.com/some-prefix/bundles/test/images/image.png?", $out);
		$this->assertContains('width-app: 10px;', $out);
		$this->assertContains('width-vendor: 10px;', $out);
		$this->assertContains("image-inline: url('data:image/png;base64,", $out);
		$this->assertContains("vendor-generated-image-busted: url('/generated/vendor_1x1.png?", $out);
		$this->assertContains("vendor-generated-image: url('/generated/vendor_1x1.png'", $out);
		$this->assertContains("generated-image-busted: url('/generated/bundles/test/1x1.png?", $out);
		$this->assertContains("generated-image: url('/generated/bundles/test/1x1.png'", $out);
		$this->assertContains('width-app-resource: 10px;', $out);
	}
	
	public function testSprites(){
		$css = $this->getAssetCollection('test_sprites.scss');
		$out = $css->dump();
	
		$this->assertContains('/generated/bundles/test/sprites/something-s3c0fcffb3c.png', $out);
		$this->assertContains('/generated/vendor-something-sf004878b50.png', $out);
		$this->assertContains('/generated/global/images/app-something-se5beb1abac.png', $out);
	}
}
