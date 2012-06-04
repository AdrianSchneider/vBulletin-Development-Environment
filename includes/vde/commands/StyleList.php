<?php

class VDE_Command_StyleList extends VDE_Command
{
    static protected $name = 'style:list';
    static protected $description = 'Lists all styles.';
    
    public function run()
    {
        $result = $this->registry->db->query_read("
            SELECT styleid 
                 , title
              FROM " . TABLE_PREFIX . "style
            ORDER
                BY displayorder
        ");
        
        while ($style = $this->registry->db->fetch_array($result)) {
            $this->writeln(sprintf(
                '%s [%s]',
                $style['title'],
                $this->fgColor('brown', $style['styleid'])
            ));
        }
    }
}