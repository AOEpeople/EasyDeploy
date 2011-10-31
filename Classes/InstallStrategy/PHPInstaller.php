<?php

class EasyDeploy_InstallStrategy_PHPInstaller implements EasyDeploy_InstallStrategy_Interface {

	public function installSteps($packageDeliveryFolder, $packageFileName, EasyDeploy_DeployService $deployService, EasyDeploy_AbstractServer $server) {
		// fix permissions
		$server->run('chmod -R ug+x '.$packageDeliveryFolder.'/'.$packageFileName.'/installbinaries');
			// install package
		$server->run('php ' . $packageDeliveryFolder . '/' . $packageFileName . '/installbinaries/install.php \
			--systemPath="' . $deployService->getSystemPath() . '/' . $deployService->getEnvironmentName() . '" \
			--backupstorageroot="' . $deployService->getBackupstorageroot() . '" \
			--environmentName="' . $deployService->getEnvironmentName() . '" \
			--createNewMasterBackup=' . $deployService->getCreateBackupBeforeInstalling(), TRUE);
		}
}