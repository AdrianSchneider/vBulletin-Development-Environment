<?php

abstract class VDE_Command
{
    const ARG_REQUIRED = 0;
    const ARG_PROMPT   = 1;
    const ARG_OPTIONAL = 2;
    
    /**
     * @var    string        Name of command
     */
    static protected $name;
    
    /**
     * @var    string        Alias for command (for BC)
     */
    static protected $alias;
    
    /**
     * @var    string        Description of command
     */
    static protected $description;
    
    /**
     * @var    array         Argument configuration
     */
    static protected $arguments = array();

    /**
     * @var      vB_Registry
     */
    protected $registry;
    
    /**
     * @var      VDE_CLI
     */
    protected $cli;
    
    /**
     * @var    array        Color codes for FG use
     */
    static private $fgColors = array(
        'black' => '0;30',
        'dark_gray' => '1;30',
        'red' => '0;31',
        'bold_red' => '1;31',
        'green' => '0;32',
        'bold_green' => '1;32',
        'brown' => '0;33',
        'yellow' => '1;33',
        'blue' => '0;34',
        'bold_blue' => '1;34',
        'purple' => '0;35',
        'bold_purple' => '1;35',
        'cyan' => '0;36',
        'bold_cyan' => '1;36',
        'white' => '1;37',
        'bold_gray' => '0;37',
    );
    
    /**
     * @var    array        Color codes for BG use
     */
    static private $bgColors = array(
        'black' => '40',
        'red' => '41',
        'magenta' => '45',
        'yellow' => '43',
        'green' => '42',
        'blue' => '44',
        'cyan' => '46',
        'light_gray' => '47',
    );
    
    /**
     * @param    vB_Registry
     */
    public function __construct(vB_Registry $registry, VDE_Cli $cli)
    {
        $this->registry = $registry;
        $this->cli = $cli;
    }
    
    /**
     * @return    string        Name of called script
     */
    static public function getName()
    {
        return static::$name;
    }
    
    /**
     * @return    string        Description of called script
     */
    static public function getDescription()
    {
        return static::$description;
    }
    
    /**
     * @return    string        Alias of called script
     */
    static public function getAlias()
    {
        return static::$alias;
    }
    
    /**
     * Validates and returns the arguments based on command requirements
     * @param    array        Relevant $argv vars
     * @return   array        Validated $argv
     */
    public function getArguments(array $argv)
    {
        foreach (static::$arguments as $num => $argument) {
            list($vargument, $require, $description) = $argument;
            
            if ($require === self::ARG_REQUIRED and !isset($argv[$num])) {
                throw new VDE_CLI_Exception("$vargument is required");
            }
            
            if ($require === self::ARG_PROMPT and !isset($argv[$num])) {
                $argv[$num] = $this->ask($description);
            }
        }
        
        return $argv;
    }        
    
    /**
     * Writes output
     * @param    string        String to print
     */
    public function write($message)
    {
        echo $message;
    }
    
    /**
     * Writes output with a line ending
     * @param    string        String to output
     */
    public function writeln($message)
    {
        echo $message . PHP_EOL;
    }
    
    /**
     * Wraps $text in $color
     * @param    string        Color code (in $fgColors)
     * @param    string        Text tow rap
     * @return   string        Colored text
     */
    public function fgColor($color, $text)
    {
        if (!isset(self::$fgColors[$color])) {
            throw new Exception(sprintf('"%s" is not a valid fg color', $color));
        }
        
        return "\033[" . self::$fgColors[$color] . 'm' . $text . "\033[0m";
        return sprintf('\033[%sm%s\033[0m', self::$fgColors[$color], $text);
    }
    
    /**
     * Wraps $text in $color
     * @param    string        Color code (in $bgColors)
     * @param    string        Text tow rap
     * @return   string        Colored text
     */
    public function bgColor($color, $text)
    {
        if (!isset(self::$bgColors[$color])) {
            throw new Exception(sprintf('"%s" is not a valid bg color', $color));
        }
        
        return sprintf('\033[%sm%s\033[0m', self::$bgColors[$color], $text);
    }
    
    /**
     * Prompts the user for input
     * @param    string        Message to prompt with
     * @param    mixed         Default value (if null, require input)
     * @return   string        User input
     */
    public function ask($message, $default = null)
    {
        echo "$message ";
        $handle = fopen('php://stdin', 'r');
        $out = trim(fgets($handle));
        
        if ($out === '' and $default === null) {
            echo "This field is required" . PHP_EOL;
            return $this->ask($message, $default);
        }
        
        return $out;
    }
}