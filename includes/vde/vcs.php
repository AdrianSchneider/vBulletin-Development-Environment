<?php

class VDE_VCSFilterIterator extends FilterIterator
{
    protected $callback;
    
    protected $skipFiles;
    
    protected $skipDirectories;
    
    public function __construct(Iterator $iterator, $skipFiles = array(), $skipDirectories = array())
    {
        $this->skipFiles = $skipFiles;
        $this->skipDirectories = $skipDirectories;
        parent::__construct($iterator);
    }
    
    public function accept()
    {
        $file = $this->getInnerIterator()->current();
        if ($this->isSkipped($file) or ($this->gitignore and $this->isIgnored($file))) {
            return false;
        }
        
        return true;
    }
    
    protected function isSkipped($file)
    {
        foreach (explode(DIRECTORY_SEPARATOR, $file) as $path) {
            if (in_array($path, $this->skipDirectories)) {
                return true;
            }
        }
        
        if (in_array(basename($file), $this->skipFiles)) {
            return true;
        }
        
        return false;
    }
}