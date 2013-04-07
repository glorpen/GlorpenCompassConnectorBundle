<?php
namespace Glorpen\CompassConnectorBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\DependencyInjection\Container;

use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Command\Command;

class CompileCommand extends ContainerAwareCommand {
	
	protected function configure(){
		$this
		->setName('compass-connector:compiler')
		->setDescription('CompassConnector compiler bin')
		;
	}
	
	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @throws \LogicException
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$dialog = $this->getHelperSet()->get('dialog');
		
		while(True){
			try {
				$line = $dialog->ask($output, "");
			} catch(\RuntimeException $e){
				exit(0);
			}
			$d = json_decode($line, true);
			
			try {
				$output->writeln(json_encode(call_user_func_array(array($this, lcfirst(Container::camelize($d['method']))), $d['args'])));
			} catch(\Exception $e){
				$output->writeln(json_encode(array(
					'error'=> $e->getMessage()."\n".$e->getTraceAsString()
				)));
			}
		}
	}
	
	protected function checkAbsoluteUrl($url){
		return preg_match('#^(([a-z0-9]+://)|(//)).*$#', $url) == 1;
	}
	protected function checkAppUrl($url){
		return strncmp($url, "/bundles/", 9)==0;
	}
	
	public function findImage($path){
		if($this->checkAbsoluteUrl($path)){
			return $path;
		}
		
		if($this->checkAppUrl($path)){
			preg_match('#^/bundles/([a-z0-9]+)/(.*)$#', $path, $matches);
			foreach($this->getContainer()->get("kernel")->getBundles() as $b){
				if($matches[1] === strtolower(substr($b->getName(),0,-6))){
					return implode(DIRECTORY_SEPARATOR, array($b->getPath(), 'Resources','public',$matches[2]));
				}
			}
			throw new \Exception('Bundle not found');
		} else {
			throw new \Exception('not yet implmeneted');
			//vendors
		}
	}
	
	public function getImageUrl($uri){
		if($this->checkAbsoluteUrl($uri) || $uri[0] == '/'){
			return $uri;
		} else {
			//vendors
			throw new \Exception('not yet implmeneted');
		}
	}
	
	public function findScss($path){
		if(strpos($path, ':')!==false){
			list($bundleName, $scssPath) = explode(':', $path);
			$bundle = $this->getContainer()->get("kernel")->getBundle($bundleName);
			$path = implode(DIRECTORY_SEPARATOR, array($bundle->getPath(), 'Resources','assets','css', $scssPath));
			$info = pathinfo($path);
			
			if(substr_compare($info['filename'], '.scss', -4)!==0){
				$info['filename'].='.scss';
			}
			
			if(file_exists($path = implode(DIRECTORY_SEPARATOR, array($info['dirname'], $info['filename'])))){
				return $path;
			} else {
				return implode(DIRECTORY_SEPARATOR, array($info['dirname'], '_'.$info['filename']));
			}
		} else {
			return $path;
		}
	}
	
	public function getConfiguration(){
		return array(
			'project_path' => $this->getContainer()->getParameter('assetic.filter.compass_connector.cache_path'),
			'sass_path' => dirname($this->getContainer()->getParameter('kernel.root_dir')),
			'css_path' => $this->getContainer()->getParameter('assetic.filter.compass_connector.cache_path').'/css'
		);
	}
}
