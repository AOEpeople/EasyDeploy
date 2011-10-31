<?php
require_once(dirname(__FILE__).'/UnknownSourceFormatException.php');

/**
 * Common Deploy Service that can be used to deploy.
 * It first downloads the package and then installs the package using a given Install Strategie
 * @author Daniell Pötzinger
 */
class EasyDeploy_DeployService {

	/**
	 * @var array
	 */
	private $allowedEnvironments = array(
		'staging',
		'production'
	);

	/**
	 * @var string
	 */
	private $createBackupBeforeInstalling = '1';

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
	 * @var EasyDeploy_InstallStrategy_Interface
	 */
	private $installStrategy;

	/**
	 * @var string
	 */
	private $projectName;

	/**
	 * @param EasyDeploy_InstallStrategy_Interface|null $installStrategy
	 * @return EasyDeploy_DeployService
	 */
	public function __construct(EasyDeploy_InstallStrategy_Interface $installStrategy = NULL) {
		if (is_null($installStrategy)) {
			$this->setInstallStrategy(new EasyDeploy_InstallStrategy_PHPInstaller());
		} else {
			$this->setInstallStrategy($installStrategy);
		}
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

		$downloadedReleaseDirectory = $this->download($server, $packageSourcePath, $this->deliveryFolder . '/' . $releaseName);
		$this->installPackage($server, $downloadedReleaseDirectory);
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
			$command= 'rsync -avz '.$parsedUrlParts['user'].'@'.$parsedUrlParts['host'].':'.$path.' '.$to;
			$server->run($command, TRUE);
		}
		else if (is_file($from)) {
			$server->copy($from,$to);
		}
		else {
			throw new EasyDeploy_UnknownSourceFormatException($from.' File not existend or it is a unknown source deglaration!');
		}
		
	
		//fix permissions of downloaded package
		if (isset($this->deployerUnixGroup)) {
			$server->run('chgrp '.$this->deployerUnixGroup.' '. $to);
		}
		return $to.$baseName;
	}

	/**
	 * Deploys to the given server
	 * @param \EasyDeploy_AbstractServer|\EasyDeploy_RemoteServer $server
	 * @param $packagePath
	 *
	 */
	public function installPackage(EasyDeploy_AbstractServer $server, $packagePath) {		
		if (!isset($this->systemPath) || $this->systemPath == '') {
			throw new Exception('SystemPath not set');
        }
        
		if (!isset($this->environmentName) || $this->environmentName == '') {
			throw new Exception('Environment name not set');
        }
		$this->pathToLocalConf = sprintf($this->pathToLocalConf, $this->systemPath . '/' . $this->environmentName);
		if (is_file($this->pathToLocalConf)) {
			$server->run('echo "" >> ' . sprintf($this->pathToLocalConf, $this->systemPath . '/' . $this->environmentName));
		}

		// get package and copy to deliveryfolder
		$packageBaseName = pathinfo($packagePath, PATHINFO_BASENAME);
		$packageFileName = substr($packageBaseName, 0, strpos($packageBaseName, '.'));
		$packageDeliveryFolder = pathinfo($packagePath, PATHINFO_DIRNAME);
		$releaseVersion = basename($packageDeliveryFolder);

		// unzip package
		$releasePackageName = $server->run('find ' . $packageDeliveryFolder . ' -type f -name "' . $this->projectName . '-' . $releaseVersion . '*.tar.gz" | sort | tail -n 1', FALSE, TRUE);
		$server->run('cd ' . $packageDeliveryFolder . '; tar -xzf ' . $releasePackageName);

		$this->installStrategy->installSteps($packageDeliveryFolder, $this->projectName, $this, $server);
			
		// delete unzipped folder
		$server->run('rm -rf ' . $packageDeliveryFolder . '/' . $this->projectName);
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
		if (!in_array($environmentName, $this->allowedEnvironments)) {
			throw new UnexpectedValueException('Environment must be: ' . PHP_EOL . '- ' . implode(PHP_EOL . '- ', $this->allowedEnvironments) . PHP_EOL . PHP_EOL);
		}
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
	 * @return EasyDeploy_InstallStrategy_Interface
	 */
	public function getInstallStrategy() {
		return $this->installStrategy;
	}

	/**
	 * @param \EasyDeploy_InstallStrategy_Interface|\the $installStrategy $installStrategy to set
	 *
	 */
	public function setInstallStrategy(EasyDeploy_InstallStrategy_Interface $installStrategy) {
		$this->installStrategy = $installStrategy;
	}

	/**
	 * Default is set to "1"
	 *
	 * @param string $createBackup
	 * @return void
	 */
	public function setCreateBackupBeforeInstalling($createBackup) {
		$createBackup = strtolower($createBackup);
		if ($createBackup === 'n' || $createBackup === 'no' || $createBackup === '0') {
			$this->createBackupBeforeInstalling = '0';
		}
	}

	/**
	 * Indicate that a fresh backup of master system should be done
	 * before the installation starts.
	 *
	 * @return string
	 */
	public function getCreateBackupBeforeInstalling() {
		return $this->createBackupBeforeInstalling;
	}

	/**
	 * @param string $projectName
	 * @return void
	 */
	public function setProjectName($projectName) {
		$this->projectName = $projectName;
	}

	/**
	 * @return void
	 */
	public function getProjectName() {
		return $this->projectName;
	}
}
