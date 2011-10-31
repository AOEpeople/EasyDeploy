<?php
require_once dirname(__FILE__) . '/EasyDeploy/Classes/Utils.php';
EasyDeploy_Utils::includeAll();

$projectName = '__PROJECT-NAME__';
$releaseDirectory = 'ssh://your.name@your.server:/path/to/systemstorage/%s/releases/%s/';
$deliveryDirectory = '/path/to/systemstorage/%s/delivery';
$backupDirectory = '/path/to/systemstorage/%s/backup';
$systemRootDirectory = '/var/www/' . $projectName;
$server = new EasyDeploy_LocalServer();

$releaseVersion = EasyDeploy_Utils::userInput('Enter release no you want to install: ');
$environment = EasyDeploy_Utils::userInput('Enter environment to install (staging|production): ');
$createBackup = EasyDeploy_Utils::userInput('Create backup before installing the release: ');

$deployer = new EasyDeploy_DeployService();
$deployer->setProjectName($projectName);
$deployer->setCreateBackupBeforeInstalling($createBackup);
$deployer->setDeliveryFolder(sprintf($deliveryDirectory, $projectName));
$deployer->setDeployerUnixGroup('www-data');
$deployer->setSystemPath($systemRootDirectory);
$deployer->setBackupstorageroot(sprintf($backupDirectory, $projectName));

try {
	$deployer->setEnvironmentName($environment);
	$deployer->deploy($server, $releaseVersion, sprintf($releaseDirectory, $projectName, $releaseVersion));
} catch (Exception $e) {
	print EasyDeploy_Utils::formatMessage(rtrim($e->getMessage()), EasyDeploy_Utils::MESSAGE_TYPE_ERROR) . PHP_EOL;
	print EasyDeploy_Utils::formatMessage('Exiting deployment for release: "' . $releaseVersion . '"', EasyDeploy_Utils::MESSAGE_TYPE_ERROR) . PHP_EOL . PHP_EOL;
}