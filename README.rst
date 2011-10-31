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
	
	require_once(dirname(__FILE__).'EasyDeploy/Classes/Utils.php');
	EasyDeploy_Utils::includeAll();
	
	$server = new EasyDeploy_RemoteServer('www.aoemedia.de');
	
	$deployer = new EasyDeploy_DeployService();
	$deployer->setEnvironmentName('production');
	$deployer->setSystemPath('/var/www/aoemedia/production/');
	$deployer->setDeliveryFolder('/deliveries');
	$deployer->setDeployerUnixGroup('www-data');
	
	//now deploy the package 
	$packageSource = 'ssh://user@yourbuildserver.de:/builds/6/aoemedia.tar.gz';
	$releaseName= 'build6';
	$deployer->deploy($server, releaseName, $packageSource);

This will install the package on a remote server. 
On the remote Server the package is downloaded via rsync from "yourbuildserver.de" to a deliverfolder and then the package is untared and the installation is started.


Lets make it a bit nicer:
::
	$buildNr = EasyDeploy_Utils::userInput('Enter Build Nr that you want to deploy: ');
	$packageSource = 'ssh://user@yourbuildserver.de:/builds/'.$buildNr.'/aoemedia.tar.gz';


If you dont want to deploy to a remoteserver but you are already logged in to the liveserver just replace the Server class:
::
	$server = new EasyDeploy_LocalServer();


Supported Packagepaths
------------------------------

In the example above the installationpackage was available on a buildserver. There are other possibilities:

a) Local file:
  Example Package Path: /home/user/mypackage.tar.gz
  
b) Web:
  Example Package Path: http://user:password@host.de/path/mypackage.tar.gz
  
c) SSH (SCP)
  Example Package Path: ssh://user@host.de:/path/mypackage.tar.gz
