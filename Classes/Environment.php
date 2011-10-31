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


/**
 *
 * 
 * @author: Michael Klapper <michael.klapper@aoemedia.de>
 * @date: 31.10.11
 * @time: 15:00
 */
class EasyDeploy_Environment {

	/**
	 * @var array
	 */
	protected $environmentSuffix = array('-a', '-b');

	/**
	 * @var string
	 */
	protected $environment;

	/**
	 * @var string
	 */
	protected $activeEnvironment;

	/**
	 * @var string
	 */
	protected $inactiveEnvironment;

	/**
	 * @var string
	 */
	protected $systemPath;

	/**
	 * @var EasyDeploy_AbstractServer
	 */
	protected $server;

	/**
	 * @param \EasyDeploy_AbstractServer $server
	 * @param string|null $environment
	 * @param string|null $systemPath
	 * @return \EasyDeploy_Environment
	 */
	public function __construct(EasyDeploy_AbstractServer $server, $environment = null, $systemPath = null) {
		$this->server = $server;
		$systemPath = rtrim($systemPath, '/') . '/';

		if (!$this->server->isLink($systemPath . $environment) || !$this->server->isDir($systemPath . $environment)) {
			throw new UnexpectedValueException();
		}

		$this->setEnvironment($environment);
		$this->setSystemPath($systemPath);
	}

	/**
	 * Get the active environment instance.
	 *
	 * This can be:
	 * - production-a
	 * - production-b
	 * - staging-a
	 * - staging-b
	 *
	 * The active environment instance is "production-a"
	 * <example>
	 * production -> production-a
	 * production-a
	 * production-b
	 * </example>
	 *
	 * @return string
	 */
	public function getActiveEnvironment() {
		if (is_null($this->activeEnvironment)) {
			$this->setActiveEnvironment();
		}

		return $this->activeEnvironment;
	}

	/**
	 * @return void
	 */
	protected function setActiveEnvironment() {
		$this->activeEnvironment = basename(readlink($this->systemPath . $this->environment));
	}



	/**
	 * Find the inactive environment instance, this instance is used to apply the release package.
	 *
	 * The inactive environment in the following example is "production-b":
	 * <example>
	 * production -> production-a
	 * production-a
	 * production-b
	 * </example>
	 *
	 * @return void
	 */
	protected function setInactiveEnvironment() {

		if (is_null($this->activeEnvironment)) {
			$this->setActiveEnvironment();
		}

		$activeInstanceSuffix = substr($this->activeEnvironment, -2);

			// TODO add support for generic instances like a,b,c,d ..
		foreach ($this->environmentSuffix as $instance) {
			if ($instance != $activeInstanceSuffix) {
				$this->inactiveEnvironment = $this->environment . $instance;
				break;
			}
		}

		if (is_null($this->inactiveEnvironment) || $this->activeEnvironment == $this->inactiveEnvironment) {
			throw new RuntimeException('Could not detect inactive environment instance!');
		}
	}

	/**
	 * @return string
	 */
	public function getInactiveEnvironment() {
		if (is_null($this->inactiveEnvironment)) {
			$this->setInactiveEnvironment();
		}

		return $this->inactiveEnvironment;
	}

	/**
	 * @param string $systemPath
	 */
	public function setSystemPath($systemPath) {
		$this->systemPath = $systemPath;
	}

	/**
	 * @return string
	 */
	public function getSystemPath() {
		return $this->systemPath;
	}

	/**
	 * @param string $environment
	 */
	public function setEnvironment($environment) {
		$this->environment = $environment;
	}

	/**
	 * @return string
	 */
	public function getEnvironment() {
		return $this->environment;
	}
}
