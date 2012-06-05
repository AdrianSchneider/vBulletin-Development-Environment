<?php

class VDE_Command_ProjectDisconnect extends VDE_Command
{
    static protected $name = 'project:sync:remove';
    static protected $description = "Deletes any synchronized files from the local install";
    
    static protected $arguments = array(
        array('dir', self::ARG_PROMPT, 'Directory to disconnect?'),
        array('ignoreVCS', self::ARG_OPTIONAL, 'Ignore VCS (Git) files?')
    );
    
    /**
     * Deletes all files found in A from the vBulletin directory
     * @param    string        Project directory
     * @param    bool          Ignore VCS files
     */
    public function run($watchedDir, $ignoreVcs = true)
    {
        if (!is_dir($watchedDir)) {
            throw new VDE_CLI_Exception(sprintf('"%s" does not exist', $watchedDir));
        }
        
        if ($ignoreVcs) {
            require_once(DIR . '/includes/vde/vcs.php');
        }
        
        $this->deleteFiles($watchedDir, $ignoreVcs);
        $this->deleteEmptyFolders($watchedDir, $ignoreVcs);
    }
    
    /**
     * Deletes all files found in $sourceDir from DIR
     * @param    string        Project directory
     * @param    boolean       Ignore Vcs and other files
     */
    protected function deleteFiles($sourceDir, $ignoreVcs)
    {
        foreach ($this->getProjectFiles($sourceDir, $ignoreVcs) as $file) {
            if (is_dir($file)) {
                continue;
            }
            
            $relativePath = str_replace($sourceDir, '', $file);
            if (file_exists(DIR . $relativePath) and md5_file($file) === md5_file(DIR . $relativePath)) {
                @unlink(DIR . $relativePath);
            }
        }
    }
    
    /**
     * Deletes all empty folders found in $sourceDir from DIR
     * @param    string        Project directory
     * @param    boolean       Ignore Vcs and other files
     */
    protected function deleteEmptyFolders($sourceDir, $ignoreVcs)
    {
        $dirs = array();
        $processed = array();
        
        foreach ($this->getProjectFiles($sourceDir, $ignoreVcs) as $file) {
            $dir = dirname($file);
            $relativePath = str_replace($sourceDir, '', $dir);
            if (!$relativePath) {
                continue;
            }
            
            $dirs[] = $relativePath;
        }

        foreach ($this->expandDirectories($dirs) as $dir) {
            if (is_dir(DIR . $dir) and $this->isEmpty(DIR . $dir)) {
                @rmdir(DIR . $dir);
            }
        }
    }
    
    /**
     * Grabs a list of all project files
     * @param    string        Project directory
     * @param    boolean       Ignore Vcs and other files
     * @return   array         List of files found in project dir
     */
    protected function getProjectFiles($dir, $ignoreVcs)
    {
        $paths = array();
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        
        if ($ignoreVcs) {
            $iterator = new VDE_VCSFilterIterator(
                $iterator, 
                array('.gitignore', '.project', '.buildpath', '.', '..'),
                array('.git', '.settings')
            );
        }
        
        foreach ($iterator as $path => $info) {
            $paths[] = $path;
        }
        
        return $paths;
    }
    
    /**
     * Expands directories to include all subdirectories
     * @param    array        Directories
     * @return   array        Expanded directories
     */
    protected function expandDirectories(array $dirs)
    {
        $dirs = array_unique($dirs);
        
        $sortByLengthAndAlpha = function($a, $b) {
            $al = strlen($a);
            $bl = strlen($b);
            if ($a === $b) {
                return strcmp($a, $b);
            }
            return $al < $bl ? 1 : -1;
        };

        usort($dirs, $sortByLengthAndAlpha);
        
        $out = array();
        
        foreach ($dirs as $dir) {
            $out[] = $dir;
            while ($parent = dirname($dir) and $parent != DIRECTORY_SEPARATOR) {
                $out[] = $parent;
                $dir = $parent;
            }
        }
        
        $dirs = array_unique($out);
        usort($dirs, $sortByLengthAndAlpha);

        return $dirs;
    }
    
    /**
     * Checks to see if a directory is empty
     * (only would contain '.' and '..')
     * 
     * @param    string        Directory to check
     * @return   boolean       TRUE if empty
     */
    protected function isEmpty($directory)
    {
        return count(scandir($directory) == 2);
    }    
}