<?php

class VDE_Command_GenerateTask extends VDE_Command
{
    static protected $name = 'generate:task';
    static protected $description = 'Generate a scheduled task confiugration file.';
    static protected $alias = 'task:generate';
    
    static protected $arguments = array(
        array('path', self::ARG_PROMPT, 'Where is your project located?')
    );
    
    public function run($projectPath)
    {
        if (!is_dir($dir = "$projectPath/tasks")) {
            echo "Creating $projectPath/tasks... ";
            if (mkdir($dir, null, true)) {
                echo "Done" . PHP_EOL;
            } else {
                die('Could not create tasks directory' . PHP_EOL);
            }
        }

        $taskVarname = $this->ask('Task varname?');
        $taskFilename = $this->ask('Task filename? (relative)');
        
        if (file_exists("$projectPath/tasks/$taskVarname.php")) {
            throw new VDE_CLI_Exception('That task already exists');
        }
        
        $title       = addslashes($this->ask('New task title?'));
        $description = addslashes($this->ask('New task description?'));
        
        
        $monthly = -1;
        $weekly  = $this->ask('Frequency? (blank = daily, m = monthly, 0-6 for specific weekdays');
        
        if ($weekly == 'm') {
            $weekly  = -1;
            $monthly = $this->ask('Enter day of month to run (between 1 and 31)');
        }  else if ($weekly === '') {
            $weekly = -1;
        }
        $weekly = intval($weekly);
        
        
        $hour = $this->ask('Which hour? (blank for all, or 0-23)');
        if ($hour === '') {
            $hour = -1;
        }
        $hour = intval($hour);
        
        
        $minutes = $this->ask('At which minutes? -1, or up to 6 comma-separated numbers (0-59)');
        if ($minutes === '') {
            $minutes = -1;
        }
        
        $taskTemplate = <<<TEMPLATE
<?php return array(
    'title'       => '$title',
    'description' => '$description',
    'filename'    => '$taskFilename',
    'weekday'     => $weekly,
    'day'         => $monthly,
    'hour'        => $hour,
    'minutes'     => '$minutes'
);
TEMPLATE;
        
        $this->write('Creating a new task file... ');
        
        if (file_put_contents("$projectPath/tasks/$taskVarname.php", $taskTemplate)) {
            $this->writeln('Done!');
        } else {
            $this->writeln($this->fgColor('red', 'Could not create task file!'));
            exit(1);
        }

        $this->writeln('Task created successfully');
    }
}






