<?php
require_once dirname(__FILE__).'/../Classes/Utils.php';
require_once dirname(__FILE__).'/../Classes/DeployService.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * test case.
 */
class DeployServiceTest extends PHPUnit_Framework_TestCase {
	
	
	/**
	 * @test
	 */
	public function canDeployFromWeb() {
		$localServerMock = $this->getMock('EasyDeploy_LocalServer');
		$installStrategyMock = $this->getMock('EasyDeploy_InstallStrategy_Interface',array('installSteps'));
		$deployService = new EasyDeploy_DeployService($installStrategyMock);
		$localServerMock = $this->getMock('EasyDeploy_AbstractServer',array('isDir','wgetDownload','run','isFile'));
		$localServerMock->expects($this->any())->method('isDir')->will($this->returnValue(TRUE));
		$localServerMock->expects($this->once())->method('wgetDownload')->will($this->returnValue(TRUE));
		$installStrategyMock->expects($this->once())->method('installSteps')->will($this->returnValue(TRUE));	
		$deployService->setSystemPath('/dummy');
		$deployService->setEnvironmentName('dummy');
		$deployService->deploy($localServerMock,'test','http://www.mydomain.de/testpackage.tar.gz');
	}

	/**
	 * @test
	 */
	public function canThrowExceptionIfUnknownSource() {
		$this->setExpectedException('EasyDeploy_UnknownSourceFormatException');		
		$localServerMock = $this->getMock('EasyDeploy_LocalServer');
		$installStrategyMock = $this->getMock('EasyDeploy_InstallStrategy_Interface');
		$deployService = new EasyDeploy_DeployService($installStrategyMock);
		$localServerMock = $this->getMock('EasyDeploy_AbstractServer',array('isDir','isFile'));
		$localServerMock->expects($this->any())->method('isDir')->will($this->returnValue(TRUE));
		$deployService->deploy($localServerMock,'test','@unknown source');
	}
	

}

