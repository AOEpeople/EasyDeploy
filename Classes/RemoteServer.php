<?php
/**
 * Copyright notice
 *
 * (c) 2011 AOE media GmbH <dev@aoemedia.de>
 * All rights reserved
 *
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 */

require_once(dirname(__FILE__).'/AbstractServer.php');

/**
 * Represents a remote server and therefore requires the hostname in the
 * All commands are executed remote on this server using ssh pipeing
 * Usage:
 * 	$aoemedia = new EasyDeploy_RemoteServer('www.aoemedia.de');
 *  $aoemedia->run('ls -al');
 *  
 * As a precondition the executing user should have a valid ssh key (and password less ssh login setuped)
 * 
 * @author Daniel Pötzinger 
 */
class EasyDeploy_RemoteServer extends EasyDeploy_AbstractServer {

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var string
	 */
	private $userName;

	/**
	 *
	 * @param string $host
	 * @param string $userName
	 * @return \EasyDeploy_RemoteServer
	 */
	public function __construct($host, $userName = '') {
		$this->host = $host;
		$this->userName = $userName;
	}
	
	/**
	 * Runs the given command remotely
	 * @throws EasyDeploy_CommandFailedException
	 * @param string $command
	 * @param boolean $withInteraction   set to true if the command should stay open and wait for STDIN
	 * @param boolean $returnOutput		set to true if you need the result - otherwise its directed to STDOUT
	 */
	public function run($command, $withInteraction = FALSE, $returnOutput = FALSE) {
		if ($withInteraction) {
			$shellCommand = 'ssh -t -A';
		}
		else {
			$shellCommand = 'ssh -A';
		}
		$shellCommand .= ' ' . ((!is_null($this->userName)) ? $this->userName . '@' : '') . $this->host.' '.escapeshellarg($command);

		echo ' ['.$shellCommand.']'.PHP_EOL;	
		$result = $this->executeCommand( $shellCommand, $returnOutput );
		if ($result['returncode'] != 0 ) {
			throw new EasyDeploy_Exception_CommandFailedException($result['error']);
		}
		if ($returnOutput) {
			return $result['out'];
		}
	}
	
	/**
	 * copys a local file to the server
	 * 
	 * @param string $from
	 * @param string $to
	 */
	public function copyLocalFile($from,$to) {
		if (!is_file($from)) {
			throw new Exception($from.' is not a file');
		}
		$this->executeCommand( 'rsync -avz '.escapeshellarg($from).' '.$this->host.':'.escapeshellarg($to) );
	}
	
	/**
	 * @param string $dir
	 * @return boolean
	 */
	public function isDir($dir) {
		try {
			$output = $this->run('ls -al '.$dir, FALSE, TRUE);
		}
		catch(EasyDeploy_Exception_CommandFailedException $e) {
			if (strpos( $e->getMessage(),'No such file or directory') !== FALSE) {
				return false;
			}
			else {
				throw $e;
			}
		}
		
		if (strpos($output,$dir) !== FALSE) {
			//is a file
			return false;
		}
		return true;
	}
	/**
	 * @param string $dir
	 * @return boolean
	 */
	public function isFile($dir) {
		$output = $this->run('ls -al '.$dir, FALSE, TRUE);
		if (strpos($output,'No such file or directory') !== FALSE) {
			return false;
		}
		if (strpos($output,$dir) !== FALSE) {
			return true;
		}
		return false;
	}

	/**
	 * SSH user name.
	 *
	 * @param string $userName
	 * @return void
	 */
	public function setUserName($userName) {
		$this->userName = $userName;
	}

	/**
	 * @param string $directory
	 * @return boolean
	 */
	public function isLink($directory) {
		$isLink = false;

		try {
			$this->run('if [ -L "' . $directory . '" ] ; then exit 0; else exit 1; done');
			$isLink = true;
		} catch (EasyDeploy_Exception_CommandFailedException $e) {
		}

		return $isLink;
	}
}
