<?php

class EasyDeploy_DeployService {
	/**
	 * 
	 * @var $deliveryFolder string
	 */
	private $deliveryFolder;
	/**
	 * Environmentname for the installation (e.g. "production") This might be required by the install process to adjust environment specifc settings
	 * @var unknown_type
	 */
	private $environmentName;
	/**
	 * Target path for the installation
	 * @var string
	 */
	private $systemPath;
	/**
	 * Path to available backups, that might be required by the Install Strategie
	 * @var string
	 */
	private $backupstorageroot ;
	/**
	 * name of the group that should be used to fix permissions
	 * @var string
	 */
	private $deployerUnixGroup;
	
	/**
	 * @var EasyDeploy_InstallStrategie_Interface
	 */
	private $installStrategie;
	
	public function __construct() {
		$this->setInstallStrategie(new EasyDeploy_InstallStrategie_PHPInstaller());
	}

	/**
	 * Deploys a package
	 * @param EasyDeploy_AbstractServer $server
	 * @param string $releaseName
	 * @param string $packageSourcePath
	 *  
	 */
	public function deploy(EasyDeploy_AbstractServer $server, $releaseName, $packageSourcePath) {	
		if (!$server->isDir($this->deliveryFolder)) {
			throw new Exception($this->deliveryFolder.' deliveryFolder not existend on server!');
		}
		$downloadedFile = $this->download($server, $packageSourcePath, $this->deliveryFolder.'/'.$releaseName);
		$this->installPackage($server, $downloadedFile);
	}
	
	/**
	 * Downloads the specified file (from) to the local directoy (to)
	 * $from can be a local file or a remote file (http:// and ssh:// supported)
	 * 
	 * @param EasyDeploy_AbstractServer $server
	 * @param string $from   
	 * @param string $to
	 * @return string	The path to the downloaded file
	 */
	public function download(EasyDeploy_AbstractServer $server, $from, $to) {
		$baseName=pathinfo($from,PATHINFO_BASENAME);
		$to = EasyDeploy_Utils::appendDirectorySeperator($to);
		
		//$fileName=substr($baseName,0,strpos($baseName,'.'));
		if (is_file($to.$baseName)) {
			echo 'File "'.$to.$baseName.'" already existend! Skipping transfer!';
			return $to.$baseName;
		}
		if (!$server->isDir($to)) {
			$server->run('mkdir '.$to);
			if (!$server->isDir($to)) {
				throw new Exception('Targetfolder "'.$to.'" not existend on server!');
			}
			if (isset($this->deployerUnixGroup)) {
                  $server->run('chgrp '.$this->deployerUnixGroup.' '.$to);
                  $server->run('chmod g+rws '.$to);
			}
		}
		
		
		// copy package to local deliveryfolder:
		if(strpos($from,'http://') === 0) {
			$parsedUrlParts=parse_url($from);
			$server->wgetDownload($parsedUrlParts['scheme'].'://'.$parsedUrlParts['path'], $to, @$parsedUrlParts['user'], @$parsedUrlParts['pass']);
		}
		else if (strpos($from,'ssh://') === 0) {
			//ssh://user@server:path
			$parsedUrlParts=parse_url($from);
			$path = substr($from,strrpos($from,':')+1);
			$command= 'scp '.$parsedUrlParts['user'].'@'.$parsedUrlParts['host'].':'.$path.' '.$to;
			$server->run($command, TRUE);
		}
		else if (is_file($from)) {
			$server->copy($from,$to);
		}
		else {
			throw new Exception($from.' File not existend or it is a unknown source deglaration!');
		}
		
	
		//fix permissions of downloaded package
		if (isset($this->deployerUnixGroup)) {
			$server->run('chgrp '.$this->deployerUnixGroup.' '.$to.$baseName);
		}
		return $to.$baseName;
	}
	
	/**
	 * Deploys to the given server
	 * @param EasyDeploy_RemoteServer $server
	 */
	public function installPackage(EasyDeploy_AbstractServer $server, $packagePath) {		
		if (!isset($this->systemPath) || $this->systemPath == '') {
                        throw new Exception('SystemPath not set');
        }
        
		if (!isset($this->environmentName) || $this->environmentName == '') {
                        throw new Exception('environment name not set');
        }

		//get package and copy to deliveryfolder
		$packageBaseName=pathinfo($packagePath,PATHINFO_BASENAME);
		$packageFileName=substr($packageBaseName,0,strpos($packageBaseName,'.'));
		$packageDeliveryFolder = pathinfo($packagePath,PATHINFO_DIRNAME);
		
		//unzip package
		$server->run('cd '.$packageDeliveryFolder.'; tar -xzf '.$packageDeliveryFolder.'/'.$packageBaseName);
		
		$this->installStrategie->installSteps($packageDeliveryFolder, $packageFileName, $this, $server);
			
		//delete unzipped folder
		$server->run('rm -rf '.$packageDeliveryFolder.'/'.$packageFileName);
	}
	/**
	 * @return the $deliveryFolder
	 */
	public function getDeliveryFolder() {
		return $this->deliveryFolder;
	}

	/**
	 * @return the $environmentName
	 */
	public function getEnvironmentName() {
		return $this->environmentName;
	}

	/**
	 * @return the $systemPath
	 */
	public function getSystemPath() {
		return $this->systemPath;
	}

	/**
	 * @return the $backupstorageroot
	 */
	public function getBackupstorageroot() {
		return $this->backupstorageroot;
	}

	

	/**
	 * @param $deliveryFolder the $deliveryFolder to set
	 */
	public function setDeliveryFolder($deliveryFolder) {
		$this->deliveryFolder = $deliveryFolder;
	}

	/**
	 * @param $environmentName the $environmentName to set
	 */
	public function setEnvironmentName($environmentName) {
		$this->environmentName = $environmentName;
	}

	/**
	 * @param $systemPath the $systemPath to set
	 */
	public function setSystemPath($systemPath) {
		$this->systemPath = $systemPath;
	}

	/**
	 * @param $backupstorageroot the $backupstorageroot to set
	 */
	public function setBackupstorageroot($backupstorageroot) {
		$this->backupstorageroot = $backupstorageroot;
	}

	/**
	 * @return the $deployerUnixGroup
	 */
	public function getDeployerUnixGroup() {
		return $this->deployerUnixGroup;
	}

	/**
	 * @param $deployerUnixGroup the $deployerUnixGroup to set
	 */
	public function setDeployerUnixGroup($deployerUnixGroup) {
		$this->deployerUnixGroup = $deployerUnixGroup;
	}
	/**
	 * @return EasyDeploy_InstallStrategie_Interface
	 */
	public function getInstallStrategie() {
		return $this->installStrategie;
	}

	/**
	 * @param $installStrategie the $installStrategie to set
	 */
	public function setInstallStrategie(EasyDeploy_InstallStrategie_Interface $installStrategie) {
		$this->installStrategie = $installStrategie;
	}

	



}
