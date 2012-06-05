<?php

class VDE_Command_ProjectDisconnect extends VDE_Command
{
    static protected $name = 'project:sync:remove';
    static protected $description = "Deletes any synchronized files from the local install";
    
    static protected $arguments = array(
        array('dir', self::ARG_PROMPT, 'Directory to disconnect?')
    );
    
    /**
     * Deletes all files found in A from the vBulletin directory
     * @param    string        Project directory
     */
    public function run($watchedDir)
    {
        if (!is_dir($watchedDir)) {
            throw new VDE_CLI_Exception(sprintf('"%s" does not exist', $watchedDir));
        }
        
        $this->deleteFiles($watchedDir);
        $this->deleteEmptyFolders($watchDir);
    }
    
    /**
     * Deletes all files found in $sourceDir from DIR
     * @param    string        Project directory
     */
    protected function deleteFiles($sourceDir)
    {
        foreach ($this->getProjectFiles($sourceDir) as $file) {
            if (is_dir($file)) {
                continue;
            }
            
            $relativePath = str_replace($sourceDir, '', $file);
            if (md5($file) === md5(DIR . $relativePath)) {
                @unlink(DIR . $relativePath);
            }
        }
    }
    
    /**
     * Deletes all empty folders found in $sourceDir from DIR
     * @param    string        Project directory
     */
    protected function deleteEmptyFolders($sourceDir)
    {
        foreach ($this->getProjectFiles($sourceDir) as $file) {
            if (!is_dir($file)) {
                continue;
            }
        
            $relativePath = str_replace($sourceDir, '', $file);
        
            if ($this->isEmpty(DIR . $relativePath)) {
                @rmdir(DIR . $relativePath);
            }
        }
    }
    
    /**
     * Fetches a list of all project files
     * @param    string        Project directory
     * @return   array         List of all paths found
     */
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
     * Checks to see if a directory is empty
     * (only would contain '.' and '..')
     * @param    string        Directory to check
     * @return   boolean       TRUE if empty
     */
    protected function isEmpty($directory)
    {
        return count(scandir($directory) == 2);
    }    
}