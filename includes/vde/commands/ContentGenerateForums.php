<?php

class VDE_Command_ContentGenerateForums extends VDE_Command
{
    static protected $name = 'content:generate:forums';
    static protected $description = "Generates new forums.";
    
    static protected $arguments = array(
        array('filename', self::ARG_OPTIONAL, 'The filename to import, or null to enter your own content.'),
        array('root', self::ARG_OPTIONAL, 'The parent forum ID')
    );
    
    protected $defaults = array(
        'title' => '',
        'description' => '',
        'link' => '',
        'displayorder' => '1',
        'parentid' => 'root',
        'daysprune' => '-1',
        'defaultsortfield' => 'lastpost',
        'defaultsortorder' => 'desc',
        'showprivate' => '0',
        'newpostemail' => '',
        'newthreademail' => '',
        'options' =>
        array (
            'moderatenewpost' => '0',
            'moderatenewthread' => '0',
            'moderateattach' => '0',
            'styleoverride' => '0',
            'canhavepassword' => '1',
            'cancontainthreads' => '1',
            'active' => '1',
            'allowposting' => '1',
            'indexposts' => '1',
            'allowhtml' => '0',
            'allowbbcode' => '1',
            'allowimages' => '1',
            'allowsmilies' => '1',
            'allowicons' => '1',
            'allowratings' => '1',
            'countposts' => '1',
            'showonforumjump' => '1',
            'prefixrequired' => '0',
        ),
        'styleid' => '-1',
        'imageprefix' => '',
        'password' => '',
    );
    
    public function run($filename = null, $root = -1)
    {
        $this->defaults['parentid'] = $root;
        
        if ($filename) {
            if (!file_exists($filename)) {
                throw new VDE_CLI_Exception("$filename does not exist");
            }
            $content = file_get_contents($filename);
        } else {
            $content = $this->getUserInput();
        }
        
        foreach (array_map('rtrim', explode("\n", $content)) as $index => $line) {
            if (!trim($line)) {
                continue;
            }
            
            $forum = $this->defaults;
            
            // No Parent
            if (preg_match("/^([a-z0-9]+)/i", $line)) {
                $forum['options']['cancontainthreads'] = 0;
            } else {
                $forum['parentid'] = $last['forumid'];
            }
            
            $forum['title'] = trim($line);
            
            $forumdata    = datamanager_init('Forum', $this->registry, ERRTYPE_ARRAY);
            $forum_exists = false;
            
            foreach ($forum as $varname => $value) {
                if ($varname == 'options') {
                    foreach ($value AS $key => $val) {
                        $forumdata->set_bitfield('options', $key, $val);
                    }
                } else {
                    $forumdata->set($varname, $value);
                }
            }
            
            $forumdata->pre_save();
            if ($forumdata->errors) {
                throw new Exception('An error occured.  Please refer to the documentation for help.  Error: ' . implode(', ', $forumdata->errors));
            }
            
            $forum['forumid'] = $forumdata->save();
            
            $this->writeln("Imported forum " . $this->fgColor('green', $forum['title']));
            
            if ($forum['parentid'] == $root) {
                $last = $forum;
            } 
        }
        
        $this->writeln("Forums added successfully.  You should rebuild your forum cache now.");
    }
    
    /**
     * Prompts the user for input
     * @return    string        User input
     */
    protected function getUserInput()
    {
        $this->writeln("Enter the forums to add, in the following format:");
        $this->writeln($this->fgColor('brown', implode(PHP_EOL, array(
            'Forum A',
            'Forum B',
            '    Forum C',
            '    Forum D',
            'Forum E'
        ))));
        
        $this->writeln("When you are done, leave two empty lines.");
        
        while (!feof(STDIN)) {
            $content .= fgets(STDIN);
        
            if (strpos($content, PHP_EOL . PHP_EOL) !== false) {
                break;
            }
        }
        
        return trim($content);
    }
}