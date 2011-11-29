<?php

/**
 * Default Installer Strategie for the PHP Installer: Just executes the installscript that is delivered within the package
 */
class EasyDeploy_InstallStrategy_PHPInstaller implements EasyDeploy_InstallStrategy_Interface {
	
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
		$server->run('php ' . $packageDeliveryFolder . '/' . $packageFileName . '/installbinaries/install.php \
			--systemPath="' . $deployService->getSystemPath()  . '" \
			--backupstorageroot="' . $deployService->getBackupstorageroot() . '" \
			--environmentName="' . $deployService->getEnvironmentName() . '"'.$additionalParameters, TRUE);
	}
}