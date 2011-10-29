<?php

require_once(dirname(__FILE__).'/AbstractServer.php');

/**
 * Represents a local server 
 * 
 * 
 * @author Daniel Pötzinger
 */
class EasyDeploy_LocalServer extends EasyDeploy_AbstractServer {
	
	
	/**
	 * Runs the given command remotely
	 * @throws EasyDeploy_CommandFailedException
	 * @param string $command
	 * @param boolean $withInteraction   set to true if the command should stay open and wait for STDIN
	 * @param boolean $returnOutput		set to true if you need the result - otherwise its directed to STDOUT
	 */
	public function run($command, $withInteraction = FALSE, $returnOutput = FALSE) {
		
		$shellCommand = $command;
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
	 * @param string $from
	 * @param string $to
	 */
	public function copyLocalFile($from,$to) {
		$this->executeCommand( 'cp '.escapeshellarg($from).' '.escapeshellarg($to) );
	}
	
	/**
	 * @param string $dir
	 * @return boolean
	 */
	public function isDir($dir) {
		return is_dir($dir);
	}
	
	/**
	 * @param string $dir
	 * @return boolean
	 */
	public function isFile($dir) {
		return is_file($dir);
	}
	
	
	
	
}
