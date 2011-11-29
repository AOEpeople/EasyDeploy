What is EasyDeploy?
=====================

EasyDeploy is a set of PHP Classes that should help to deploy projects.


The idea is that you can write simple deployscripts using EasyDeploy classes
that help to manage the transfer and installation of packages.


Example Usage
-------------

The easiest usage is to use the *Server Classes to execute commands. The command execution allows to wait for user inputs - just the way like you are used to in a typical shell.
::
	$server = new EasyDeploy_RemoteServer('www.aoemedia.de');
	echo "Now listing the content of my home directory:';
	server->run('ls -al');

In order to be able to run commands on a remoteserver you need to configure our ssh account to be able to login to the remoteserver via ssh without password:
(simply create your very own security key and paste the public file into a file called “.ssh/authorized_keys” in the home directory for the server’s deployment user. 
 See also:
 - http://kimmo.suominen.com/docs/ssh/
 - http://the.earth.li/~sgtatham/putty/0.60/htmldoc/Chapter8.html#pubkey
 )



Writing your Deployment Scripts
------------------------------


Imagine you want to deploy your latest project to a RemoteServer.
Following the concept of a Deployment-Pipeline your deployment or build process should already build an installationpackage that can be used to install the project.
But anything else is possible too.

So a simple script could look like this:
::
	<?php
	require_once dirname(__FILE__) . '/EasyDeploy/Classes/Utils.php';
	EasyDeploy_Utils::includeAll();

	$projectName = 'proalpha';
	$releaseDirectory = 'ssh://your.name@your.server:/path/releases/%s';
	$deliveryDirectory = '/path/delivery';
	$backupDirectory = '/path/backup';
	$systemRootDirectory = '/var/www/project';
	$server = new EasyDeploy_LocalServer();

	$releaseVersion = EasyDeploy_Utils::userInput('Enter release no you want to install: ');
	$environment = EasyDeploy_Utils::userInput('Enter environment to install (staging|production): ');
	$createBackup = EasyDeploy_Utils::userInput('Create backup before installing the release: ');

	$deployer = new EasyDeploy_DeployService();
	$deployer->setProjectName($projectName);
	$deployer->setCreateBackupBeforeInstalling($createBackup);
	$deployer->setDeliveryFolder($deliveryDirectory);
	$deployer->setDeployerUnixGroup('www-data');
	$deployer->setSystemPath($systemRootDirectory);
	$deployer->setBackupstorageroot($backupDirectory);

	try {
		$deployer->setEnvironmentName($environment);
		$deployer->deploy($server, $releaseVersion, sprintf($releaseDirectory, $releaseVersion));
	} catch (EasyDeploy_CommandFailedException $e) {
		print EasyDeploy_Utils::formatMessage(rtrim($e->getMessage()), EasyDeploy_Utils::MESSAGE_TYPE_ERROR) . PHP_EOL;
		print EasyDeploy_Utils::formatMessage('Exiting deployment for release: "' . $releaseVersion . '"', EasyDeploy_Utils::MESSAGE_TYPE_ERROR) . PHP_EOL . PHP_EOL;
	}

This will install the package on a remote server. 
On the remote Server the package is downloaded via rsync from "yourbuildserver.de" to a deliverfolder and then the package is untared and the installation is started.

Supported Packagepaths
------------------------------

In the example above the $releaseDirectory was available on a build server. There are other possibilities:

a) Local file:
  Example Package Path: /home/user/mypackage.tar.gz
  
b) Web:
  Example Package Path: http://user:password@host.de/path/mypackage.tar.gz
  
c) SSH (SCP)
  Example Package Path: ssh://user@host.de:/path/mypackage.tar.gz

  
User Input
------------------------------
::
	EasyDeploy_Utils::userInput('Your input');
	
::
	EasyDeploy_Utils::userInput('Select between',array('option1','option2'));

You can also get Parameters that are passed to the Installscript (like deploy.php --parameter=value )
::
	$value = EasyDeploy_Utils::getParameter('parameter');
	
	$value = EasyDeploy_Utils::getParameterOrInput('parameter','Enter the value for Parameter');

	$value = EasyDeploy_Utils::getParameterOrDefault('makebackup',1);  
  
Using own Installstrategie
------------------------------

The InstallStrategie Object is responsible to Install the extracted Installation package.
The PHPInstaller Strategie that ships with the Tool is bound to our specific Installationscripts: 
 - Per convention every package is self installable and the strategie just calls the Installscript in the package.
 
 However you might want to use a own Installstrategie (maybe just a simple one that copys files to the target systemPath). So you can write your own Strategie and pass this to the Deployservice:
 
::
	<?php
	$deployer = new EasyDeploy_DeployService(new MyOwnInstallByCopyStrategie());
	
