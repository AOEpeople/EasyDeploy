<?php

require_once(dirname(__FILE__).'/CommandFailedException.php');

abstract class EasyDeploy_AbstractServer {
	
	
	/**
	 * Runs the given command remotely
	 * @throws EasyDeploy_CommandFailedException
	 * @param string $command
	 * @param boolean $withInteraction   set to true if the command should stay open and wait for STDIN
	 * @param boolean $returnOutput		set to true if you need the result - otherwise its directed to STDOUT
	 */
	abstract public function run($command, $withInteraction = FALSE, $returnOutput = FALSE);
	
	/**
	 * copys a local file to the server
	 * 
	 * @param string $from
	 * @param string $to
	 */
	 abstract public function copyLocalFile($from,$to);
	
	/**
	 * @param string $dir
	 * @return boolean
	 */
	abstract public function isDir($dir);
	
	/**
	 * @param string $dir
	 * @return boolean
	 */
	abstract public function isFile($dir);
	
	/**
	 * @param string $command
	 * @return array out, error, returncode
	 */
	protected function executeCommand($command, $returnOutput = FALSE) {
		$result = array();
		$descriptorspec = array(
		   0 => STDIN,  // stdin is a pipe that the child will read from
		   1 => STDOUT,  // stdout is a pipe that the child will write to
		   2 => array("pipe", "w") // stderr is a file to write to
		);
		if ($returnOutput) {
			$descriptorspec[1] = array("pipe", "w");
		}
		$process = proc_open($command, $descriptorspec, $pipes, NULL, NULL);
		
		if (is_resource($process)) {
			if ($returnOutput) {
			    $result['out'] = stream_get_contents($pipes[1]);
			    fclose($pipes[1]);
			}
			else {
				 $result['out'] = '';
			}
		    
		    $result['error'] = stream_get_contents($pipes[2]);
		    fclose($pipes[2]);
		
		    // It is important that you close any pipes before calling
		    // proc_close in order to avoid a deadlock
		    $result['returncode'] = proc_close($process);
		}
		else {
			 $result['returncode'] = '-1';
			 $result['error'] = 'proc_open failed';
		}
		return $result;
	}
	
	/**
	 * @return string
	 */
	public function getCurrentUsername() {
		return $this->run('whoami',FALSE,TRUE);
	}
	
	/**
	 * Downloads a file from http with wget
	 * @param string $from  the url that should be downloaded
	 * @param string $to  download target
	 * @param $user optional the http-auth user name
	 * @param $password optional the http-auth password
	 */
	public function wgetDownload($from,$to,$user=null,$password=null) {
		$options= '';
		if (isset($user) && $user != '') {
			$options = ' --http-user='.$user.' --http-password='.$password;
		}
		return $this->run('cd '.$to.'; wget '.$options. ' '.$from);
	}
	
}
