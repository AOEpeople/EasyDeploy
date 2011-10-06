<?php

class EasyDeploy_Utils {
	
	/**
	 * @param string $type
	 * @param array $setting the concrete setting
	 * @return string
	 */
	static public function userInput($message) {		
		echo $message.PHP_EOL;
		$result =  readline('  ?');
		if (empty($result)) {
				echo 'Empty values not allowed!'.chr(10);
				return self::userInput($message);
		}		
		return $result;				
	}
	
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
	
	static public function appendDirectorySeperator($dir) {
		//prepend with "/"
		if (substr($dir,-1,1) != '/') {
			$dir.='/';
		}
		return $dir;
	}


}
