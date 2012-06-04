<?php

class VDE_Command_ProjectImport extends VDE_Command
{
    static protected $name = 'project:import';
    static protected $description = 'Import an existing product from vBulletin into VDE';
    static protected $alias = 'port';
    
    static protected $arguments = array(
        array('id',    self::ARG_PROMPT, 'Product ID?'),
        array('path',  self::ARG_PROMPT, 'Path to generate project at?')
    );
    
    public function run($id, $path)
    {
        if (!is_dir($path)) {
            $this->createProjectDir($path);
        }
        
        require_once(DIR . '/includes/vde/porter.php');
        $porter = new VDE_Porter();
        $porter->port($id, $path);
    }
    
    protected function createProjectDir($path)
    {
        $this->write("Attempting to create project directory... ");
        if (mkdir($path, 0777, true)) {
            $this->writeln("Done!");
        } else {
            $this->writeln("Could not create project directory '$path'");
            exit(1);
        }
    }
}