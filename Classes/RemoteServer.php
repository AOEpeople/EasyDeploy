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

require_once(dirname(__FILE__) . '/AbstractServer.php');

/**
 * Represents a remote server and therefore requires the hostname in the
 * All commands are executed remote on this server using ssh pipeing
 * Usage:
 *     $aoemedia = new EasyDeploy_RemoteServer('www.aoemedia.de');
 *     $aoemedia->run('ls -al');
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
	 * @var string
	 */
	private $privateKey;

	/**
	 * @param string $host
	 * @param string $userName
	 * @param string $privateKey
	 * @return EasyDeploy_RemoteServer
	 */
	public function __construct($host, $userName = null, $privateKey = null) {
		$this->host       = $host;
		$this->userName   = $userName;
		$this->privateKey = $privateKey;
	}

	/**
	 * Runs the given command remotely
	 *
	 * @param string $command
	 * @param boolean $withInteraction set to true if the command should stay open and wait for STDIN
	 * @param boolean $returnOutput set to true if you need the result - otherwise its directed to STDOUT
	 * @param string $appendOutputToFile set to a valid existing file (on current OS context) to write the output directly to a file
	 * @throws EasyDeploy_Exception_CommandFailedException
	 * @return string|null
	 */
	public function run($command, $withInteraction = false, $returnOutput = false, $appendOutputToFile = null) {
		if ($withInteraction) {
			$shellCommand = 'ssh -t -A';
		} else {
			$shellCommand = 'ssh -A';
		}
		if (!is_null($this->privateKey)) {
			$shellCommand .= ' -i ' . $this->privateKey;
		}
		$shellCommand .= ' ' . ((!is_null($this->userName)) ? $this->userName . '@' : '') . $this->host . ' ' . escapeshellarg($command);

		$result = $this->executeCommand($shellCommand, $returnOutput, $this->getStreamDescriptor($returnOutput, $appendOutputToFile));
		if ($result['returncode'] != 0) {
			throw new EasyDeploy_Exception_CommandFailedException('"' . $command . '" failed:' . $result['error']);
		}
		if ($returnOutput) {
			return $result['out'];
		}

		return null;
	}

	/**
	 * Copy a local file to the server
	 *
	 * @param string $from
	 * @param string $to
	 * @throws EasyDeploy_Exception_CommandFailedException
	 * @throws Exception
	 */
	public function copyLocalFile($from, $to) {
		if (!is_file($from)) {
			throw new Exception($from . ' is not a file');
		}

		$command = 'rsync -avz ' . escapeshellarg($from) . ' ' . $this->host . ':' . escapeshellarg($to);
		$result  = $this->executeCommand($command, true);

		if ($result['returncode'] != 0) {
			throw new EasyDeploy_Exception_CommandFailedException($command . ' ' . $result['error']);
		}
	}

	/**
	 * Copy a local dir to the server
	 *
	 * @param string $from
	 * @param string $to
	 * @throws EasyDeploy_Exception_CommandFailedException
	 */
	public function copyLocalDir($from, $to) {
		$command = 'rsync --delete -avz ' . escapeshellarg($from) . ' ' . $this->host . ':' . escapeshellarg($to);
		$result  = $this->executeCommand($command, true);

		if ($result['returncode'] != 0) {
			throw new EasyDeploy_Exception_CommandFailedException($command . ' ' . $result['error']);
		}
	}

	/**
	 * Verify $target using $condition using bash if statement
	 *
	 * @param string $target
	 * @param string $condition
	 * @return bool
	 */
	protected function _bashIf($target, $condition) {
		try {
			$command = 'if [ '. $condition . ' "' . $target . '" ] ; then echo 1; else echo 0; fi';
			if (trim($this->run($command, false, true)) == 1) {
				return true;
			}
		} catch (EasyDeploy_Exception_CommandFailedException $e) {
		}

		return false;
	}

	/**
	 * Verify whether given $target exists
	 *
	 * @param string $target
	 * @return bool
	 */
	public function exists($target) {
		return $this->_bashIf($target, '-e');
	}

	/**
	 * Verify whether $target is directory
	 *
	 * @param string $target
	 * @return bool
	 */
	public function isDir($target) {
		return $this->_bashIf($target, '-d');
	}

	/**
	 * Verify whether $target is link
	 *
	 * @param string $target
	 * @return bool
	 */
	public function isLink($target) {
		return $this->_bashIf($target, '-L');
	}

	/**
	 * Verify whether $target is file
	 *
	 * @param string $target
	 * @return bool
	 */
	public function isFile($target) {
		return $this->_bashIf($target, '-f');
	}

	/**
	 * Get current working directory
	 *
	 * @return string
	 */
	public function getCwd() {
		try {
			return $this->run('pwd', false, true);
		} catch (EasyDeploy_Exception_CommandFailedException $e) {
		}

		return null;
	}

	/**
	 * SSH user name
	 *
	 * @param string $userName
	 * @return $this
	 */
	public function setUserName($userName) {
		$this->userName = $userName;

		return $this;
	}

}
