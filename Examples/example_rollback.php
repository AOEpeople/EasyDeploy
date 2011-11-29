<?php
require_once dirname(__FILE__) . '/EasyDeploy/Classes/Utils.php';
EasyDeploy_Utils::includeAll();
/**
 * Directory containing the following structure:
 * <example>
 * $ ls -1 /path/to/www
 * production -> production-a
 * production-a
 * production-b
 * 
 * staging -> staging-a
 * staging-a
 * staging-b
 * 
 * </example>
 */
$systemRootDirectory = '/path/to/www';
$environment = 	EasyDeploy_Utils::userSelectionInput('Select the Environment that you want to roll back:',array('staging','production'));

$rollbackService = new EasyDeploy_RollbackService();
$rollbackService->setEnvironment($environment);
$rollbackService->setSystemPath($systemRootDirectory);
$rollbackService->process(new EasyDeploy_LocalServer());