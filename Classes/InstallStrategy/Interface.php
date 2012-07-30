<?php

interface EasyDeploy_InstallStrategy_Interface {
	public function installSteps($packageDeliveryFolder, $packageFileName, EasyDeploy_DeployService $deployService, EasyDeploy_AbstractServer $server);
}