<?php

class VDE_Command_ProjectSync extends VDE_Command
{
    static protected $name = 'project:sync';
    static protected $description = "Sync's your external project (ex: local git repo) path with vBulletin.";
    
    static protected $arguments = array(
        array('dir', self::ARG_PROMPT, 'Directory to sync?')
    );
    
    /**
     * Uses watchmedo to watch the $watchDir filesystem for changes
     * Upon changes, it triggers a call to vde project:sync:callback which copies the
     * file to vBulletin.
     * 
     * @param    string        Watch directory (local git repository outside of vBulletin)
     */
    public function run($watchDir)
    {
        if (!is_dir($watchDir)) {
            throw new VDE_CLI_Exception(sprintf('"%s" does not exist', $watchDir));
        }
        
        $output = '';
        $return = '';
        exec('watchmedo --version 2>&1', $output, $return);
        
        if (strpos($output[0], 'watchmedo') === false) {
            throw new VDE_CLI_Exception('You must install python + watchmedo for project syncing');
        }
        
        $this->writeln($this->fgColor('green', sprintf('Watching "%s" for changes...', $watchDir)));
        
        $command = sprintf(
            'watchmedo shell-command --recursive --command=\'%s\' %s',
            sprintf(
                'php %s/vde.php project:sync:callback %s %s',
                DIR,
                '"${watch_src_path}"',
                $watchDir
            ),
            $watchDir
        );
        
        echo shell_exec($command);
    }
}