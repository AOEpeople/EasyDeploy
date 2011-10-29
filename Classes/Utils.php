<?php

/**
 * Static helper functions
 * 
 * @author Daniel Pötzinger
 */
class EasyDeploy_Utils {
	
	/**
	 * Helper to include all EasyDeploy Files
	 */
	static public function includeAll() {
		require_once(dirname(__FILE__).'/RemoteServer.php');
		require_once(dirname(__FILE__).'/LocalServer.php');
		require_once(dirname(__FILE__).'/DeployService.php');
		require_once(dirname(__FILE__).'/Utils.php');
		require_once(dirname(__FILE__).'/InstallStrategie/Interface.php');
		require_once(dirname(__FILE__).'/InstallStrategie/PHPInstaller.php');
		
		if (ini_get('date.timezone') == '') {
			echo 'Warning - timezone not set -using Europe/Berlin';
			date_default_timezone_set('Europe/Berlin');
		}
				
	}
	
	/**
	 * @param string $type
	 * @param array $setting the concrete setting
	 * @return string
	 */
	static public function userInput($message) {		
		echo $message.PHP_EOL;
		$result =  self::readline('  ?');
		if (empty($result)) {
				echo 'Empty values not allowed!'.chr(10);
				return self::userInput($message);
		}		
		return $result;				
	}
	
	/**
	 * Gets a value from command line arguments
	 * If not set it returns false
	 * @param string $key
	 * @return string or FALSE
	 */
	static public function getParameter($key) {
		$params = self::getArvParameters();
		if (!isset($params[$key])) {
			return FALSE;
		}
		return $params[$key];		
	}
	
	/**
	 * Gets a value from command line arguments, If not set it promts for it on commandline
	 * 
	 * @param string $key
	 * @param string $message
	 * @return string
	 */
	static public function getParameterOrInput($key, $message) {
		$result = self::getParameter($key);
		if ($result === FALSE) {
			return self::userInput($message);
		}
	}
	
	/**
	 * Gets a value from command line arguments, If not set it promts for it on commandline
	 * 
	 * @param string $key
	 * @param string $message
	 * @return string
	 */
	static public function getParameterOrDefault($key, $default) {
		$result = self::getParameter($key);
		if ($result === FALSE) {
			return $default;
		}
	}
	
	/**
	 * Makes sure that paths ends with /
	 * @param string $dir
	 * @return string
	 */
	static public function appendDirectorySeperator($dir) {
		//prepend with "/"
		if (substr($dir,-1,1) != '/') {
			$dir.='/';
		}
		return $dir;
	}
	
	/**
	 * Readline version (with fallback to fgets ( SSTDIN ) )
	 * @param unknown_type $prompt
	 */
	public static function readline($prompt=null){
		if (function_exists('readline')) {
			return readline($prompt);
		}
		else {
			echo $prompt;
    		return rtrim( fgets( STDIN ), "\n" );
		}
  	}
	
	/**
	 * Parses command line parameters in the format --key="value" 
	 * @return array
	 */
	static private function getArvParameters() {
		$result = array ();
		$params = $GLOBALS ['argv'];
		// could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
		reset ( $params );
		foreach ( $params as $p ) {
			$pname = substr ( $p, 1 );
			$value = true;
			if ($pname {0} == '-') {
				// long-opt (--<param>)
				$pname = substr ( $pname, 1 );
				if (strpos ( $p, '=' ) !== false) {
					// value specified inline (--<param>=<value>)
					list ( $pname, $value ) = explode ( '=', substr ( $p, 2 ), 2 );
				}
			}
			$result [$pname] = $value;
		}
		return $result;
	}


}
