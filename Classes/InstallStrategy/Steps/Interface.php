<?php

interface EasyDeploy_InstallStrategy_Steps_Interface {
	/**
	 * @abstract
	 * @param EasyDeploy_AbstractServer $server
	 * @param EasyDeploy_InstallStrategy_Steps_StepInfos $stepInfos
	 * @return void
	 */
	public function process(EasyDeploy_AbstractServer $server, EasyDeploy_InstallStrategy_Steps_StepInfos $stepInfos);
}