<?php
/**
 * Wrapper for Informations passed between Steps
 */
class EasyDeploy_InstallStrategy_Steps_StepInfos  {
	/**
	 * @var string
	 */
	protected $packageDeliveryFolder;

	/**
	 * @var string
	 */
	protected $packageFileName;

	/**
	 * @var EasyDeploy_DeployService
	 */
	protected $deployService;


	/**
	 * @param \EasyDeploy_DeployService $deployService
	 */
	public function setDeployService($deployService) {
		$this->deployService = $deployService;
	}

	/**
	 * @return \EasyDeploy_DeployService
	 */
	public function getDeployService() {
		return $this->deployService;
	}

	/**
	 * @param string $packageDeliveryFolder
	 */
	public function setPackageDeliveryFolder($packageDeliveryFolder) {
		$this->packageDeliveryFolder = $packageDeliveryFolder;
	}

	/**
	 * @return string
	 */
	public function getPackageDeliveryFolder() {
		return $this->packageDeliveryFolder;
	}

	/**
	 * @param string $packageFileName
	 */
	public function setPackageFileName($packageFileName) {
		$this->packageFileName = $packageFileName;
	}

	/**
	 * @return string
	 */
	public function getPackageFileName() {
		return $this->packageFileName;
	}
}