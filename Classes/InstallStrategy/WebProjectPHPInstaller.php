<?php
/**
 * Installer Strategie for the PHP Installer. 
 * Used for Webprojects, where the environmentname is appended to the systemPath per convention
 */
class EasyDeploy_InstallStrategy_WebProjectPHPInstaller extends EasyDeploy_InstallStrategy_PHPInstaller {
	
	/**
	 * Gets relevant system path (path for installing the package) based on the infos in the deployservice
	 * 
	 * @param EasyDeploy_DeployService $deployService
	 * @return string
	 */
	protected function getSystemPath(EasyDeploy_DeployService $deployService) {
		return $deployService->getSystemPath(). '/' . $deployService->getEnvironmentName();
	}	
}