<?php
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
	private $host;
	
	/**
	 * @param string $host
	 */
	public function __construct($host) {
		$this->host = $host;
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
			$shellCommand = 'ssh -t';
		}
		else {
			$shellCommand = 'ssh';
		}
		$shellCommand .= ' ' . $this->host.' '.escapeshellarg($command);
		echo ' ['.$shellCommand.']'.PHP_EOL;	
		$result = $this->executeCommand( $shellCommand, $returnOutput );
		if ($result['returncode'] != 0 ) {
			throw new EasyDeploy_CommandFailedException($result['error']);
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
		$this->executeCommand( 'scp '.escapeshellarg($from).' '.$this->host.':'.escapeshellarg($to) );
	}
	
	/**
	 * @param string $dir
	 * @return boolean
	 */
	public function isDir($dir) {
		try {
			$output = $this->run('ls -al '.$dir, FALSE, TRUE);
		}
		catch(EasyDeploy_CommandFailedException $e) {
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
	
	

	
}
