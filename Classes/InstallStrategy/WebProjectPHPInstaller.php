<?php
/**
 * Installer Strategie for the PHP Installer. 
 * Used for Webprojects, where the environmentname is appended to the systemPath per convention
 */
class EasyDeploy_InstallStrategy_WebProjectPHPInstaller implements EasyDeploy_InstallStrategy_Interface {
	
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
		if ($deployService->getCreateBackupBeforeInstalling()) {
			$additionalParameters =' --createNewMasterBackup=1';
		}
		$server->run($this->phpbinary . ' ' . $packageDeliveryFolder . '/' . $packageFileName . '/installbinaries/install.php \
			--systemPath="' . $deployService->getSystemPath() . '/' . $deployService->getEnvironmentName() . '" \
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
}