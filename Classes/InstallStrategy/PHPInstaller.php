<?php

/**
 * Default Installer Strategie for the PHP Installer: Just executes the installscript that is delivered within the package
 */
class EasyDeploy_InstallStrategy_PHPInstaller implements EasyDeploy_InstallStrategy_Interface {
	
	/**
	 * @var boolean
	 */
	private $createBackupBeforeInstalling = true;
	
	/**
	 * @var string
	 */
	protected $phpbinary = 'php';

	/**
	 * @param string $packageDeliveryFolder
	 * @param string $packageFileName
	 * @param EasyDeploy_DeployService $deployService
	 * @param EasyDeploy_AbstractServer $server
	 */
	public function installSteps($packageDeliveryFolder, $packageFileName, EasyDeploy_DeployService $deployService, EasyDeploy_AbstractServer $server) {
		// fix permissions
		$server->run('chmod -R ug+x '.$packageDeliveryFolder.'/'.$packageFileName.'/installbinaries');
		// install package
		$additionalParameters = '';
		if ($this->createBackupBeforeInstalling) {
			$additionalParameters =' --createNewMasterBackup=1';
		}
		$server->run($this->phpbinary . ' ' . $packageDeliveryFolder . '/' . $packageFileName . '/installbinaries/install.php \
			--systemPath="' . $this->getSystemPath( $deployService )  . '" \
			--backupstorageroot="' . $deployService->getBackupstorageroot() . '" \
			--environmentName="' . $deployService->getEnvironmentName() . '"'.$additionalParameters, TRUE);
	}

	public function setPHPBinary($bin) {
		if (file_exists($bin) && is_executable($bin)) {
			$this->phpbinary = $bin;
		} else {
			print EasyDeploy_Utils::formatMessage('PHP binary '.$bin.' does not exist or is not executable.', EasyDeploy_Utils::MESSAGE_TYPE_WARNING);
		}
	}
	
	/**
	 * Default is set to true
	 * 
	 * @depreciated this is a concept of the install strategy - you should pass a initialised strategie
	 * 
	 * @param boolean $createBackup
	 * @return void
	 */
	public function setCreateBackupBeforeInstalling($createBackup) {
		$this->createBackupBeforeInstalling = (boolean) $createBackup;
	}
	
	
	/**
	 * Gets relevant system path (path for installing the package) based on the infos in the deployservice
	 * 
	 * @param EasyDeploy_DeployService $deployService
	 * @return string
	 */
	protected function getSystemPath(EasyDeploy_DeployService $deployService) {
		return $deployService->getSystemPath();
	}
}