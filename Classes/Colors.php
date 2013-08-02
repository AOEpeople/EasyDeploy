<?php
/**
 * @author Dmytro Zavalkin <dmytro.zavalkin@aoemedia.de>
 * @see http://www.if-not-true-then-false.com/2010/php-class-for-coloring-php-command-line-cli-scripts-output-php-output-colorizing-using-bash-shell-colors/
 */
class EasyDeploy_Colors
{
    /**
     * Foreground colors
     *
     * @var array
     */
    private static $foregroundColors = array(
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    );

    /**
     * Background colors
     *
     * @var array
     */
    private static $backgroundColors = array(
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    );

    /**
     * Make given string colored with given colors
     *
     * @param string $string
     * @param string $foregroundColor
     * @param string $backgroundColor
     * @return string
     */
    public static function getColoredString($string, $foregroundColor = null, $backgroundColor = null)
    {
        $coloredString = "";

        // check if given foreground color found
        if (isset(self::$foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . self::$foregroundColors[$foregroundColor] . "m";
        }
        // check if given background color found
        if (isset(self::$backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . self::$backgroundColors[$backgroundColor] . "m";
        }

        // add string and end coloring
        $coloredString .= $string . "\033[0m";

        return $coloredString;
    }

    /**
     * Return all foreground color names
     *
     * @return array
     */
    public static function getForegroundColors()
    {
        return array_keys(self::$foregroundColors);
    }

    /**
     * Return all background color names
     *
     * @return array
     */
    public static function getBackgroundColors()
    {
        return array_keys(self::$backgroundColors);
    }
}
