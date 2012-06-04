<?php

class VDE_Command_Run extends VDE_Command
{
    static protected $name = 'run';
    static protected $description = 'Runs a PHP script in the vBulletin environment (cron jobs, etc.).';
    
    static protected $arguments = array(
        array('file',    self::ARG_PROMPT, 'File to execute?'),
    );
    
    public function run($file)
    {
        if (!file_exists($file)) {
            throw new VDE_CLI_Exception("$file does not exist");
        }
        
        include $file;
    }
}