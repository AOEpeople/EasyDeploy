<?php
require_once(dirname(__FILE__).'/../Classes/Utils.php');
EasyDeploy_Utils::includeAll();

//***** Relevant Inputs ******
$environment = 'production';
$systemPath = '/var/www/yourdomain/production/';

$buildNr = EasyDeploy_Utils::userInput('Enter Build Nr that you want to deploy: ');
$packageSource = 'ssh://user@yourbuildserver.de:/builds/'.$buildNr.'/aoemedia.tar.gz';

//*******   Deploy *******

$server = new EasyDeploy_RemoteServer('www.yourdomain.de');
$deployer = new EasyDeploy_DeployService();
$deployer->setEnvironmentName($environment);
$deployer->setSystemPath($systemPath);
$deployer->setDeliveryFolder('/opt/webapp/deliveries');
$deployer->setDeployerUnixGroup('www-data');
$deployer->deploy($server, $buildNr, $packageSource);

