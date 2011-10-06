<?php

interface EasyDeploy_InstallStrategie_Interface {
	public function installSteps($packageDeliveryFolder, $packageFileName, EasyDeploy_DeployService $deployService, EasyDeploy_AbstractServer $server);
}