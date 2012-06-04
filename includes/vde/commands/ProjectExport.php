<?php

class VDE_Command_ProjectExport extends VDE_Command
{
    static protected $name = 'project:export';
    static protected $description = 'Export a VDE project into a product XML and release directory.';
    static protected $alias = 'build';
    
    static protected $arguments = array(
        array('in_path', self::ARG_PROMPT, 'Where is your project located?'),
        array('out_path', self::ARG_PROMPT, 'Where would you like to build your project?')
    );
    
    public function run($inPath, $outPath)
    {
        try { 
            $project = new VDE_Project($inPath);
            $project->buildPath = $outPath;
        } catch (VDE_Project_Exception $e) {
            throw new VDE_CLI_Exception($e->getMessage());
        }
            
        $builder = new VDE_Builder($this->registry);
        $builder->build($project);
    }
}