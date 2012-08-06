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
	$server->run('ls -al');

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

So a simple Script, that only uses the server object to execute shell commands could look like this:
::
	<?php
	require_once dirname(__FILE__) . '/EasyDeploy/Classes/Utils.php';
	EasyDeploy_Utils::includeAll();
	$server = new EasyDeploy_RemoteServer('www.myproject.com');
	$server->wgetDownload('http://buildserver/job/build/latest/mypackage.tar.gz','/etc/apps/delivery');
	$server->run('tar -xzf /etc/apps/delivery/mypackage.tar.gz');
	$server->run('cp -R /etc/apps/delivery/mypackage /system/path');


Of course this won't be a typical deployment script - but gives some idea.
EasyDeploy has another class "DeployService". The purpose of this class is to trigger a deploymentprocess following this steps:

* A Package is downloaded from a given path (see below for the supported syntax) to a given deliver folder on the server. Therefore a subfolder with the given releasename is created in the delivery folder.
* The package is unpacked
* An InstallationStrategy is triggered to Install this package. This InstallStrategy can read all the relevant properties from the DeployService - like the name of the environment, targetpath for the installation...

So a deployment script could look like this:
::
	<?php
	require_once dirname(__FILE__) . '/EasyDeploy/Classes/Utils.php';
	EasyDeploy_Utils::includeAll();


	//where to get the package from
	$packagePath = 'ssh://your.name@yourbuildserver.de:/path/releases/latest/myproject.tar.gz';
	// local path on server where the package should be downloaded to before it is installed:
	$deliveryDirectory = '/path/delivery';
	//target path for the installation (where should it install to)
	$systemPath = '/var/www/project/production';

	//more properties the instalations strategie might want to know:
	$projectName = 'myproject';
	$backupDirectory = '/path/backup';
	$environmentName='production';
	$releaseVersion='release1';


	$server = new EasyDeploy_RemoteServer('www.myproject.com');
	$deployer = new EasyDeploy_DeployService();
	$deployer->setProjectName($projectName);
	$deployer->setDeliveryFolder($deliveryDirectory);
	$deployer->setDeployerUnixGroup('www-data');
	$deployer->setSystemPath($systemPath);
	$deployer->setBackupstorageroot($backupDirectory);
	$deployer->setEnvironmentName($environmentName);
	try {
		$deployer->deploy($server, $releaseVersion, $packagePath);
	} catch (EasyDeploy_CommandFailedException $e) {
		print EasyDeploy_Utils::formatMessage(rtrim($e->getMessage()), EasyDeploy_Utils::MESSAGE_TYPE_ERROR) . PHP_EOL;
		print EasyDeploy_Utils::formatMessage('Exiting deployment for release: "' . $releaseVersion . '"', EasyDeploy_Utils::MESSAGE_TYPE_ERROR) . PHP_EOL . PHP_EOL;
	}

This will install the package on a remote server.

The implemented flow in the DeployService is as following:
* On the remote Server the package is downloaded (via rsync or other supported methods - see below)
* It is stored on the concrete server in a deliverfolder
* The package is untared
* The installation is started, using the configured InstallStrategy

Supported Packagepaths
------------------------------

In the example above the $packagePath was available on a build server. There are other possibilities:

a) Local file:
  Example Package Path: /home/user/mypackage.tar.gz

b) Web:
  Example Package Path: http://user:password@host.de/path/mypackage.tar.gz

c) SSH (RSYNC is used to copy)
  Example Package Path: ssh://user@host.de:/path/mypackage.tar.gz

d) SSH to a folder
  Example Package Path: ssh://user@host.de:/path/
  (all files in that path will be transfered)


User Input
------------------------------
If you need user input to get some values you need, you can use the Utils Class like this:
::
	EasyDeploy_Utils::userInput('Your input');
	EasyDeploy_Utils::userSelectionInput('Select between',array('option1','option2'));

You can also get Parameters that are passed to the Installscript (like deploy.php --parameter=value )
::
	$value = EasyDeploy_Utils::getParameter('parameter');

	$value = EasyDeploy_Utils::getParameterOrInput('parameter','Enter the value for Parameter');

	$value = EasyDeploy_Utils::getParameterOrDefault('makebackup',1);

Using Installstrategie
------------------------------

The InstallStrategie Object is responsible to Install the extracted Installation package.
The PHPInstaller Strategie that ships with the Tool is bound to our specific Installationscripts:

* Per convention every package is self installable and the strategie just calls the Installscript in the package.

However you might want to use a own Installstrategie (maybe just a simple one that copys files to the target systemPath). So you can write your own Strategie and pass this to the Deployservice:
::
	<?php
	$deployer = new EasyDeploy_DeployService(new MyOwnInstallByCopyStrategie());

There is also a common Step Based Install strategy that can be used:
::
	<?php
	$installStrategy = new EasyDeploy_InstallStrategy_StepBasedInstaller();
	//add step that simply copies content from the extracted package to the given systemPath
	$installStrategy->addStep('CopyApp', new EasyDeploy_InstallStrategy_Steps_Copy());

	$deployer = new EasyDeploy_DeployService($installStrategy);
	...


Advanced EasyDeploy Use-Cases
------------------------------

With this Toolset you could build new Deploymentscripts and solve some use-cases like:

* Provide Walkthrough Installation Scripts that stops and ask for certain User Input
* Deploy to several Servers:
* * You can simple loop through an array of servers and deploy to them
* * Together with Tools like Threadi ( https://github.com/danielpoe/Threadi ) you can open seperate processes for each server
* Deploy different packages: For example you might want to Deploy a WebApplication, then a Varnishconfiguration and afer this some Cronjobs..
* Reuseable:
* * Use the same deploymentscript to deploy your devboxes (e.g. use in a Vagrant site cookbook) or to fixed production infrastructure
* * Pass Parameters to have it run automatically or use a guided installation process for manualy trigged deployments

