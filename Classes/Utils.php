<?php

/**
 * Static helper functions
 *
 * @author Daniel Pötzinger
 */
class EasyDeploy_Utils
{
    /**@+
     * Message types
     *
     * @var int
     */
    const MESSAGE_TYPE_INFO    = 1;
    const MESSAGE_TYPE_WARNING = 2;
    const MESSAGE_TYPE_ERROR   = 3;
    /**@-*/

    /**
     * Helper to include all EasyDeploy Files
     * @param string $message
     * @return string
     */
    static public function userInput($message)
    {
        $result = self::readFromCommandLine($message);
        if ($result == '') {
            echo self::formatMessage('Empty values not allowed!', self::MESSAGE_TYPE_WARNING) . chr(10);
            return self::userInput($message);
        }
        return $result;
    }

    /**
     * Helper to let a user select between different options
     * @param $message
     * @param $options
     */
    static public function userSelectionInput($message, array $options)
    {
        echo $message . PHP_EOL;
        $validKeys = array();
        foreach ($options as $k => $v) {
            $validKeys[] = $k;
            echo '    [' . $k . '] ' . $v . PHP_EOL;
        }
        $result = self::userInput('Please select');
        while (!in_array($result, $validKeys)) {
            $result = self::userInput('WRONG Input - Please select');
        }
        return $options[$result];
    }

    /**
     * Includes all relevant Classes and initialises date timezone
     * @return void
     */
    static public function includeAll()
    {
        require_once(dirname(__FILE__) . '/Colors.php');

        require_once(dirname(__FILE__) . '/RemoteServer.php');
        require_once(dirname(__FILE__) . '/LocalServer.php');
        require_once(dirname(__FILE__) . '/DeployService.php');

        require_once(dirname(__FILE__) . '/Exception/UnknownSourceFormatException.php');
        require_once(dirname(__FILE__) . '/Exception/CommandFailedException.php');

        require_once(dirname(__FILE__) . '/InstallStrategy/Interface.php');
        require_once(dirname(__FILE__) . '/InstallStrategy/PHPInstaller.php');
        require_once(dirname(__FILE__) . '/InstallStrategy/WebProjectPHPInstaller.php');

        require_once(dirname(__FILE__) . '/Helper/Downloader.php');

        require_once(dirname(__FILE__) . '/Rollback/Environment.php');
        require_once(dirname(__FILE__) . '/Rollback/RollbackService.php');

        self::printWelcomeScreen();

        if (ini_get('date.timezone') == '') {
            echo self::formatMessage('Warning - timezone not set -using Europe/Berlin', self::MESSAGE_TYPE_INFO) . PHP_EOL;
            date_default_timezone_set('Europe/Berlin');
        }
    }

    /**
     * Gets a value from command line arguments
     * If not set it returns false
     * @param string $key
     * @return string or FALSE
     */
    static public function getParameter($key)
    {
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
    static public function getParameterOrInput($key, $message)
    {
        $result = self::getParameter($key);
        if ($result === FALSE) {
            return self::userInput($message);
        }
        return $result;
    }

    /**
     * Gets a value from command line arguments, If not set it promts for it on commandline
     *
     * @param string $key
     * @param $default
     *
     * @internal param string $message
     * @return string
     */
    static public function getParameterOrDefault($key, $default)
    {
        $result = self::getParameter($key);
        if ($result === FALSE) {
            return $default;
        }
        return $result;
    }

    /**
     * @static
     * @param string $key
     * @param string $message
     * @param array $options
     * @return string
     * @throws Exception
     */
    static public function getParameterOrUserSelectionInput($key, $message, array $options)
    {
        $result = self::getParameter($key);
        if ($result !== FALSE) {
            if (in_array($result, array_keys($options))) {
                return $result;
            } else {
                throw new Exception('Given Parameter ' . $key . ' is not an allowed value. Allowed:' .implode(' ', array_keys($options)));
            }
        } else {
            $result = self::userSelectionInput($message, $options);
        }
        return $result;
    }

    /**
     * Makes sure that paths ends with /
     * @param string $dir
     * @return string
     */
    static public function appendDirectorySeparator($dir)
    {
        //prepend with "/"
        if (substr($dir, -1, 1) != '/') {
            $dir .= '/';
        }
        return $dir;
    }

    /**
     * Parses command line parameters in the format --key="value"
     * @return array
     */
    static private function getArvParameters()
    {
        $result = array();
        $params = $GLOBALS ['argv'];
        // could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly
        reset($params);
        foreach ($params as $p) {
            $pname = substr($p, 1);
            $value = true;
            if ($pname{0} == '-') {
                // long-opt (--<param>)
                $pname = substr($pname, 1);
                if (strpos($p, '=') !== false) {
                    // value specified inline (--<param>=<value>)
                    list ($pname, $value) = explode('=', substr($p, 2), 2);
                }
            }
            $result [$pname] = $value;
        }
        return $result;
    }

    /**
     * @static
     * @param string $label
     * @return string
     */
    static public function readFromCommandLine($label = '')
    {
        print $label . chr(10) . chr(9);

        $fp = fopen('php://stdin', 'r');
        $line = trim(fgets($fp));
        // $line = stream_get_line($fp, 8192, "\n");
        fclose($fp);

        return $line;
    }

    /**
     * @static
     * @param string $message
     * @param int $messageType
     * @return string
     */
    static public function formatMessage($message, $messageType = 0)
    {
        $foregroundColor = 'white';
        $backgroundColor = 'black';
        switch ($messageType) {
            case self::MESSAGE_TYPE_ERROR:
                $foregroundColor = 'white';
                $backgroundColor = 'red';
                break;
            case self::MESSAGE_TYPE_WARNING:
                $foregroundColor = 'yellow';
                $backgroundColor = 'black';
                break;
            case self::MESSAGE_TYPE_INFO:
                $foregroundColor = 'blue';
                $backgroundColor = 'black';
                break;
        }

        return EasyDeploy_Colors::getColoredString($message, $foregroundColor, $backgroundColor);
    }

    /**
     * @static
     * @return void
     */
    static public function printWelcomeScreen()
    {

        $message = <<<EOT
    _   ___  ___
   /_\ / _ \| __|
  / _ \ (_) | _|
 /_/ \_\___/|___|
\033[1;33m\033[40m  P r o j e c t   D e p l o y m e n t   \033[0m


EOT;
        print $message;
    }
}
