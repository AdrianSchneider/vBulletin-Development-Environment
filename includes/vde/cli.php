<?php

class VDE_CLI
{
    /**
     * @var    vB_Registry
     */
    protected $registry;
    
    /**
     * @var    array        arguments passed to PHP
     */
    protected $argv;
    
    /**
     * @var    array        Found commands
     */
    protected $commands;
    
    /**
     * @param    vB_Registry
     * @param    array       argv 
     */
    public function __construct(vB_Registry $registry, array $argv)
    {
        $this->registry = $registry;
        $this->argv = $argv;
    }
    
    /**
     * Returns a map of command:names to Command_Class_Names
     * @param    array        Commands
     */
    public function getCommands()
    {
        if (!empty($this->commands)) {
            return $this->commands;
        }
        
        $defined = get_declared_classes();
        $commands = array();
        
        foreach (glob(DIR . '/includes/vde/commands/*.php') as $script) {
            require_once $script;
        }
        
        foreach (array_diff(get_declared_classes(), $defined) as $class) {
            $commands[$class::getName()] = $class;
        }
        
        return $this->commands = $commands;
    }
    
    /**
     * Find a specific command class
     * @param    string        Command name
     * @return   string        Command class name
     * @throws   VDE_CLI_Exception when command not found
     */
    protected function findCommand($command)
    {
        $commands = $this->getCommands();

        foreach ($commands as $name => $class) {
            if ($alias = $class::getAlias()) {
                $commands[$alias] = $class;
            }
        }
        
        if (!isset($commands[$command])) {
            throw new VDE_CLI_Exception("Command '$command' not found");
        }
        
        return $commands[$command];
    }
    
    /**
     * Executes the chosen command
     */
    public function run()
    {
        $commandClass = $this->findCommand($this->argv[1] ?: 'help');
        $command = new $commandClass($this->registry, $this);
        
        try {
            return call_user_func_array(
                array($command, 'run'),
                $command->getArguments(array_slice($this->argv, 2))
            );
        } catch (VDE_CLI_Exception $e) {
            $command->writeln($command->fgColor('red', $e->getMessage()));
        }
    }
}

class VDE_CLI_Exception extends Exception
{
    
}