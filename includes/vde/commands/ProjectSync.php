<?php

class VDE_Command_ProjectSync extends VDE_Command
{
    static protected $name = 'project:sync';
    static protected $description = "Sync's your external project (ex: local git repo) path with vBulletin.";
    
    static protected $arguments = array(
        array('dir', self::ARG_PROMPT, 'Directory to sync?'),
        array('ignoreCVS', self::ARG_OPTIONAL, 'Ignore VCS (Git, SVN) files?')
    );
    
    /**
     * @var    string        Project dir being watched
     */
    protected $baseDir;
    
    /**
     * Uses watchmedo to watch the $watchDir filesystem for changes
     * Upon changes, it triggers a call to vde project:sync:callback which copies the
     * file to vBulletin.
     * 
     * @param    string        Watch directory (local git repository outside of vBulletin)
     */
    public function run($watchDir, $ignoreVcs = true)
    {
        if (!is_dir($this->dir = $watchDir)) {
            throw new VDE_CLI_Exception(sprintf('"%s" does not exist', $watchDir));
        }
        
        while (true) {
            foreach ($this->getProjectFiles($watchDir) as $file) {
                if ($this->isModified($file)) {
                    $this->copy($file);
                    $this->writeln(sprintf(' - "%s" has been modified!', $this->fgColor('green', $file)));
                }
            }
        }
        
        $this->writeln(sprintf(
            'Watching "%s" for changes...',
            $this->fgColor('brown', $watchDir)
        ));
    }
    
    protected function getProjectFiles($dir)
    {
        $paths = array();
        $iterator = new RecursiveIteratorIterator(RecursiveDirectoryIterator($dir));
        foreach ($iterator as $path => $info) {
            $paths[] = $path;
        }
        
        return $paths;
    }
    
    /**
     * Check to see if a file has been modified
     * @param    string        Working copy file
     * @return   boolean       TRUE if modified from vBulletin copy
     */
    protected function isModified($file)
    {
        $relativePath = str_replace($this->baseDir, '', $file);
        if (!file_exists(DIR . $relativePath)) {
            return true;
        }
        return md5_file($file) !== md5(DIR . $relativePath);
    }
    
    /**
     * Copies a file (full path) to vBulletin
     * @param    string        File to copy
     */
    protected function copy($file)
    {
        $relativePath = str_replace($this->baseDir, '', $file);
        $newFile = DIR . $relativePath;
        $newDir = dirname($newFile);
        
        if (!is_dir($newDir)) {
            mkdir($newDir, 0777, true);
        }

        copy($file, $newFile);
    }
}