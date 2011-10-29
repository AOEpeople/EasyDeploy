<?php

class EasyDeploy_InstallStrategie_PHPInstaller implements EasyDeploy_InstallStrategie_Interface {
	
	public function installSteps($packageDeliveryFolder, $packageFileName, EasyDeploy_DeployService $deployService, EasyDeploy_AbstractServer $server) {
		//fix permissions
		$server->run('chmod -R ug+x '.$packageDeliveryFolder.'/'.$packageFileName.'/installbinaries');
		
		$additionalParameters ='';
		if (EasyDeploy_Utils::getParameter('createbackup') !== FALSE) {
			$additionalParameters .= '--createNewMasterBackup=1';
		}
		
		//install package
		$server->run('php '.$packageDeliveryFolder.'/'.$packageFileName.'/installbinaries/install.php --systemPath="'.$deployService->getSystemPath().'"  --backupstorageroot="'.$deployService->getBackupstorageroot().'" --environmentName="'.$deployService->getEnvironmentName().'"  '.$additionalParameters, TRUE);
	}
	
	
}