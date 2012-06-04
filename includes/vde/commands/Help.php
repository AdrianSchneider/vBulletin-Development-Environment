<?php

class VDE_Command_Help extends VDE_Command
{
    static protected $name = 'help';
    static protected $description = 'Prints out this help menu.';
    
    public function run()
    {
        $this->writeln($this->fgColor('bold_green', "VDE Command Line Tool"));
        $this->writeln("Usage: vde.php command:name arg1, arg2...");
        
        echo PHP_EOL;
        
        $commands = array();
        foreach ($this->cli->getCommands() as $command => $commandClass) {
            if ($command == 'help') {
                continue;
            }
            
            $command = $this->fgColor('green', $command);
            $argStrings = array();
            
            foreach ($this->getCommandArguments($commandClass) as $argument) {
                if ($argument->isOptional()) {
                    $argStrings[] = sprintf(
                        '%s=%s', 
                        $this->fgColor(
                            'brown', 
                            $argument->getName()
                        ), 
                        $argument->getDefaultValue()
                    );
                } else {
                    $argStrings[] = $this->fgColor('brown', $argument->getName());
                }
            }
            
            if ($argStrings) {           
                $this->writeln("$command (" . implode(', ', $argStrings) . ")");
            } else {
                $this->writeln($command);
            }
            
            $description = $commandClass::getDescription() ?: "no help available";
            $this->writeln($this->fgColor('bold_gray', "    $description"));
            $this->writeln("");
        }
        
        echo PHP_EOL;
    }
    
    protected function getCommandArguments($className)
    {
        $refMethod = new ReflectionMethod($className, 'run');
        return $refMethod->getParameters();
    }
}