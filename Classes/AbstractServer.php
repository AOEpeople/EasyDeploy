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

require_once(dirname(__FILE__) . '/Exception/CommandFailedException.php');

/**
 * Basic Server Class representing a Servernode
 */
abstract class EasyDeploy_AbstractServer {
	/**
	 * @var bool
	 */
	protected $logCommandsToScreen = true;

	/**
	 * @var string
	 */
	protected $internalTitle = '';

	/**
	 * Runs the given command remotely
	 * @throws EasyDeploy_Exception_CommandFailedException
	 * @param string $command
	 * @param boolean $withInteraction set to true if the command should stay open and wait for STDIN
	 * @param boolean $returnOutput set to true if you need the result - otherwise its directed to STDOUT
	 */
	abstract public function run($command, $withInteraction = false, $returnOutput = false);

	/**
	 * copies a local file to the server
	 *
	 * @param string $from
	 * @param string $to
	 */
	abstract public function copyLocalFile($from, $to);

	/**
	 * copies a local file to the server
	 *
	 * @param string $from
	 * @param string $to
	 */
	abstract public function copyLocalDir($from, $to);

	/**
	 * @param string $dir
	 * @return boolean
	 */
	abstract public function isDir($dir);

	/**
	 * @abstract
	 * @param string $directory
	 * @return boolean
	 */
	abstract public function isLink($directory);

	/**
	 * @param string $dir
	 * @return boolean
	 */
	abstract public function isFile($dir);

	/**
	 * @param string $command
	 * @param bool $returnOutput
	 * @param resource $stdOutStreamDescriptor
	 * @param array $stdErrorStreamDescriptor
	 * @return array
	 */
	protected function executeCommand($command, $returnOutput = false, $stdOutStreamDescriptor = STDOUT,
		$stdErrorStreamDescriptor = array("pipe", "w")
	) {
		if ($this->getLogCommandsToScreen()) {
			print EasyDeploy_Utils::formatMessage('[' . rtrim($command) . ']', EasyDeploy_Utils::MESSAGE_TYPE_INFO)
				. PHP_EOL;
		}
		$result         = array();
		$descriptorspec = array(
			0 => STDIN, // stdin is a pipe that the child will read from
			1 => $stdOutStreamDescriptor, // stdout is a pipe that the child will write to
			2 => $stdErrorStreamDescriptor // stderr is a file to write to
		);
		//if return Output is set we force the output stream descriptor
		if ($returnOutput) {
			$descriptorspec[1] = array("pipe", "w");
		}
		$process = proc_open($command, $descriptorspec, $pipes, null, null);

		if (is_resource($process)) {
			if ($returnOutput) {
				$result['out'] = stream_get_contents($pipes[1]);
				fclose($pipes[1]);
			} else {
				$result['out'] = '';
			}

			$result['error'] = stream_get_contents($pipes[2]);
			fclose($pipes[2]);

			// It is important that you close any pipes before calling
			// proc_close in order to avoid a deadlock
			$result['returncode'] = proc_close($process);
		} else {
			$result['returncode'] = '-1';
			$result['error']      = 'proc_open failed';
		}

		return $result;
	}

	/**
	 * @param $returnOutput
	 * @param $appendOutputToFile
	 * @return array|resource
	 */
	protected function getStreamDescriptor($returnOutput, $appendOutputToFile) {
		$stdOutStreamDescriptor = STDOUT;
		if ($returnOutput === false && isset($appendOutputToFile) && is_file($appendOutputToFile)) {
			$stdOutStreamDescriptor = array('file', $appendOutputToFile, 'a');
		}

		return $stdOutStreamDescriptor;
	}

	/**
	 * @return string
	 */
	public function getCurrentUsername() {
		return trim($this->run('whoami', false, true));
	}

	public function readLink($link) {
		return trim($this->run('readlink ' . $link, false, true));
	}

	/**
	 * Downloads a file from http with wget
	 *
	 * @param string $from the url that should be downloaded
	 * @param string $to download target
	 * @param string $user optional the http-auth user name
	 * @param string $password optional the http-auth password
	 * @throws EasyDeploy_Exception_CommandFailedException
	 */
	public function wgetDownload($from, $to, $user = null, $password = null) {
		$options = '';
		if (isset($user) && $user != '') {
			$options = '--auth-no-challenge --http-user="' . $user . '" --http-password="' . $password . '"';
		}
		$result = $this->run('cd ' . $to . '; wget ' . $options . ' ' . $from, false, false);
		if (strpos($result, 'Invalid')) {
			throw new EasyDeploy_Exception_CommandFailedException('Error while downloading with wget: "' . $result . '"');
		}
	}

	public function getHostname() {
		return trim($this->run('hostname', false, true));
	}

	/**
	 * @param boolean $logCommandsToScreen
	 */
	public function setLogCommandsToScreen($logCommandsToScreen) {
		$this->logCommandsToScreen = $logCommandsToScreen;
	}

	/**
	 * @return boolean
	 */
	public function getLogCommandsToScreen() {
		return $this->logCommandsToScreen;
	}

	/**
	 * @param string $internalTitle
	 */
	public function setInternalTitle($internalTitle) {
		$this->internalTitle = $internalTitle;
	}

	/**
	 * @return string
	 */
	public function getInternalTitle() {
		return $this->internalTitle;
	}
}
