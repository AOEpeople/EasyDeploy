<?php


/**
 * Download Helper Functions
 */
class EasyDeploy_Helper_Downloader {


	/**
	 * Downloads the specified file (from) to the local directoy (to)
	 * $from can be a local file or a remote file (http:// and ssh:// supported)
	 *
	 * @param EasyDeploy_AbstractServer $server
	 * @param string $from  supports local files, http:// and ssh:// protocols
	 * @param string $to
     * @param string $deployerUnixGroup if set permissions are fixed
	 * @return string The path to the downloaded file
	 * @throws EasyDeploy_Exception_UnknownSourceFormatException
	 * @throws Exception
	 */
	public function download(EasyDeploy_AbstractServer $server, $from, $to, $deployerUnixGroup = NULL) {
		$baseName = pathinfo($from,PATHINFO_BASENAME);
		$to = EasyDeploy_Utils::appendDirectorySeperator($to);

		if ($server->isFile($to.$baseName)) {
			echo 'File "'.$to.$baseName.'" already exists! Skipping transfer!';
			return $to.$baseName;
		}
		if (!$server->isDir($to)) {
			$server->run('mkdir '.$to);
			if (!$server->isDir($to)) {
				throw new Exception('Targetfolder "'.$to.'" does not exist on server!');
			}
			if (isset($deployerUnixGroup)) {
                  $server->run('chgrp '.$deployerUnixGroup.' '.$to);
                  $server->run('chmod g+rws '.$to);
			}
		}

		// download depending on schema
		if(strpos($from,'http://') === 0) {
			$parsedUrlParts=parse_url($from);
			if (array_key_exists('port', $parsedUrlParts)) {
				$parsedUrlParts['host'] = $parsedUrlParts['host'] . ':' . $parsedUrlParts['port'];
			}
			$server->wgetDownload($parsedUrlParts['scheme'].'://'.$parsedUrlParts['host'].$parsedUrlParts['path'], $to, @$parsedUrlParts['user'], @$parsedUrlParts['pass']);
		}
		else if (strpos($from,'ssh://') === 0) {
			//ssh://user@server:path
			$parsedUrlParts=parse_url($from);
			$path = substr($from,strrpos($from,':')+1);
			$command= 'rsync -avz '.$parsedUrlParts['user'].'@'.$parsedUrlParts['host'].':'.$path.' '.$to;
			$server->run($command, TRUE);
		}
		else if (is_file($from)) {
			$server->copyLocalFile($from,$to);
		}
		else {
			throw new EasyDeploy_Exception_UnknownSourceFormatException($from.' File does not exit or is an unknown source declaration!');
		}

		//fix permissions of downloaded package
		if (isset($deployerUnixGroup)) {
			$server->run('chgrp '.$deployerUnixGroup.' '. $to);
		}
		return $to.$baseName;
	}

}
