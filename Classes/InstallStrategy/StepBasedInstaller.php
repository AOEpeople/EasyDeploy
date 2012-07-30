<?php
/**
 * Installer Strategie for the PHP Installer.
 * Used for Webprojects, where the environmentname is appended to the systemPath per convention
 */
class EasyDeploy_InstallStrategy_StepBasedInstaller implements EasyDeploy_InstallStrategy_Interface {

	/**
	 * @var array
	 */
	protected $installSteps;

	/**
	 * @param string $packageDeliveryFolder
	 * @param string $packageFileName
	 * @param EasyDeploy_DeployService $deployService
	 * @param EasyDeploy_AbstractServer $server
	 */
	public function installSteps($packageDeliveryFolder, $packageFileName, EasyDeploy_DeployService $deployService, EasyDeploy_AbstractServer $server) {
		$stepInfos = new EasyDeploy_InstallStrategy_Steps_StepInfos();
		$stepInfos->setDeployService($deployService);
		$stepInfos->setPackageDeliveryFolder($packageDeliveryFolder);
		$stepInfos->setPackageFileName($packageFileName);

		foreach ($this->installSteps as $key => $step) {
			echo EasyDeploy_Utils::formatMessage('Install Step: '.$key, EasyDeploy_Utils::MESSAGE_TYPE_INFO);
			$step->process($server, $stepInfos);
		}
	}

		/**
	 * Adds a Install Step
	 * @param string $key
	 * @param EasyDeploy_InstallStrategy_Steps_Interface $step
	 */
	public function addStep($key, EasyDeploy_InstallStrategy_Steps_Interface $step) {
		$this->installSteps[$key] = $step;
	}

}