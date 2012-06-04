<?php

class VDE_Command_ProjectSyncCallback extends VDE_Command
{
    static protected $name = 'project:sync:callback';
    static protected $visibility = 'private';
    
    static protected $arguments = array(
        array('dir', self::ARG_REQUIRED, 'Directory to sync?'),
        array('file', self::ARG_REQUIRED, 'File changed')
    );
    
    public function run($file, $basePath)
    {
        $relativePath = str_replace($basePath, '', $file);
        copy($file, DIR . $relativePath);
 
        $this->writeln(" - $relativePath was modified");
    }
}