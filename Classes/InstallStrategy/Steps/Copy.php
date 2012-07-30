<?php

/**
 * Simply copy from package to target
 */
class EasyDeploy_InstallStrategy_Steps_Copy implements EasyDeploy_InstallStrategy_Steps_Interface {
	/**
	 * @var string
	 */
	private $sourceFolder;

	/**
	 * @abstract
	 * @param EasyDeploy_AbstractServer $server
	 * @param EasyDeploy_InstallStrategy_Steps_StepInfos $stepInfos
	 * @return void
	 */
	public function process(EasyDeploy_AbstractServer $server, EasyDeploy_InstallStrategy_Steps_StepInfos $stepInfos) {
		$targetFolder = $stepInfos->getDeployService()->getSystemPath();
		if (!$server->isDir($targetFolder)) {
			$server->run('mkdir -p '. $targetFolder);
		}
		$sourceFolder = $this->getSourceFolder($stepInfos);
		if (!$server->isDir($sourceFolder)) {
			throw new Exception('Source Folder: "'. $sourceFolder.'" is not existend. You may want to explicitly give on by ->setSourceFolder');
		}
		$sourceFolder = rtrim($sourceFolder,'/').'/';
		$targetFolder = rtrim($targetFolder, '/') . '/';
		$server->run('rsync -r '. $sourceFolder. ' '. $targetFolder);
	}

	/**
	 * Gets folder to deploy from
	 * Defaults to the extracted package
	 *
	 * @param EasyDeploy_InstallStrategy_Steps_StepInfos $stepInfos
	 * @return string
	 */
	private function getSourceFolder(EasyDeploy_InstallStrategy_Steps_StepInfos $stepInfos) {
		if (!empty($this->sourceFolder)) {
			$sourceFolder = $this->sourceFolder;
		}
		else {
			$sourceFolder = $stepInfos->getPackageDeliveryFolder() . '/' . $stepInfos->getPackageFileName();
		}
		return $sourceFolder;
	}

	/**
	 * @param string $sourceFolder
	 */
	public function setSourceFolder($sourceFolder) {
		$this->sourceFolder = $sourceFolder;
	}
}