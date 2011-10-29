<?php
require_once(dirname(__FILE__).'/../Classes/Utils.php');
EasyDeploy_Utils::includeAll();

//***** Relevant Inputs ******
	// Instanciate the server where you want to deploy:
	$server = new EasyDeploy_LocalServer();
	//it can also be a remote server: $server = new EasyDeploy_RemoteServer('www.yourdomain.de');
	$buildNr = EasyDeploy_Utils::userInput('Enter Build Nr that you want to deploy: ');
	//get the user that should be used for the $packageSource: either it was given as --fetchinguser=myusername or the current user is used
	$user = EasyDeploy_Utils::getParameterOrDefault('fetchinguser',$server->getCurrentUsername());
	// Specify the source of the package that should be downloaded and installed:
	$packageSource = 'ssh://'.$user.'@yourbuildserver.de:/builds/'.intval($buildNr).'/aoemedia.tar.gz';
	echo 'Deploying '.$packageSource.PHP_EOL;
	
//*******   Deploy *******
	$deployer = new EasyDeploy_DeployService();
	$deployer->setEnvironmentName('production');
	$deployer->setSystemPath('/var/www/yourdomain/production/');
	$deployer->setDeliveryFolder('/opt/webapp/deliveries');
	$deployer->setDeployerUnixGroup('www-data');
	$deployer->deploy($server, $buildNr, $packageSource);

