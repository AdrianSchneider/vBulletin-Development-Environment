<?php

class VDE_Command_ProjectCreate extends VDE_Command
{
    static protected $name = 'project:create';
    static protected $description = 'Create a new VDE project in the filesystem.';
    
    static protected $arguments = array(
        array('path',  self::ARG_PROMPT, 'Path to generate project at?'),
        array('id',    self::ARG_PROMPT, 'Product ID?'),
        array('tile',  self::ARG_PROMPT, 'Product Title?')
    );
    
    public function run($path, $id, $title)
    {
        if (!is_dir($path)) {
            $this->createProjectDir($path);
        }
        
        $this->write("Creating project configuration... ");
        if (file_put_contents("$path/config.php", $this->getCodeTemplate($id, $title))) {
            $this->writeln("Done!");
            $this->writeln("Project created successfully.");
        } else {
            $this->writeln("Could not create config");
            exit(1);
        }
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
    
    protected function getCodeTemplate($id, $title)
    {
        return <<<TEMPLATE
<?php return array(
    'id'           => '$projectId',
    'title'        => '$projectTitle',
    'description'  => '',
    'url'          => '',
    'version'      => '0.0.1',
    'author'       => 'Your Name',
    'active'       => 1,
    'dependencies' => array(
        'php'       => array('5.2',   ''),
        'vbulletin' => array('3.8', '3.9.9')
    ),
    'files'        => array(
		// list files here
    )
);
TEMPLATE;
    }
}