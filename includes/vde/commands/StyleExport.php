<?php

class VDE_Command_StyleExport extends VDE_Command
{
    static protected $name = 'style:export';
    static protected $description = 'Export a vBulletin style to a Style XML file.';
    
    static protected $arguments = array(
        array('style_id', self::ARG_PROMPT, 'Which style ID to export?'),
        array('out_path', self::ARG_PROMPT, 'Save XML as?')
    );
    
    /**
     * Generates a style XML from $styleId at $outPath
     * 
     * @param    integer        Style ID to export
     * @param    string         Path to save XML file
     */
    public function run($styleId, $outPath)
    {
        if (!$style = $this->fetchStyle($styleId)) {
            throw new VDE_CLI_Exception("Style ID $styleId not found");
        }
        
        global $only;
        require_once(DIR . '/includes/adminfunctions.php');
        require_once(DIR . '/includes/adminfunctions_template.php');
        
        $templates = $this->fetchTemplates($styleId, $only);
        ksort($templates);
        
        if (!$templates) {
            throw new VDE_CLI_Exception("No templates found in $style[title]");
        }
        
        file_put_contents(
            $outPath,
            $this->generateStyleXml($templates, $only, fetch_product_list(true))
        );
        
        $this->writeln(sprintf(
            'Wrote Style "%s" to "%s"',
            $this->fgColor('green', $style['title']),
            $this->fgColor('green', $outPath)
        ));
    }
    
    /**
     * Fetches a style from the database
     * @param    integer        Style ID
     * @return   mixed          Style Array|false on failure
     */
    protected function fetchStyle($id)
    {
        return $this->registry->db->query_first("
            SELECT *
              FROM " . TABLE_PREFIX . "style
             WHERE styleid = " . $this->registry->db->sql_prepare($id) . "
        ");
    }
    
    /**
     * Fetches all templates for a given style
     * @param    integer        Style ID@
     * @return   array          Template info
     */
    protected function fetchTemplates($styleId, array $only)
    {
        $result = $this->registry->db->query_read("
            SELECT title
                 , templatetype
                 , username
                 , dateline
                 , version
                 , IF(templatetype = 'template', template_un, template) AS template
              FROM " . TABLE_PREFIX . "template
             WHERE styleid = " . $this->registry->db->sql_prepare($styleId) . "
               AND product IN ('', 'vbulletin')
            ORDER
                BY title
        ");
        
        $only['zzz'] = 'Ungrouped Templates';
        
        $templates = array();
        while ($template = $this->registry->db->fetch_array($result)) {
            switch ($template['templatetype']) {
                case 'template':
                    $isGrouped = false;
        
                    foreach (array_keys($only) as $group) {
                        if (strpos(strtolower($template['title']), $group) === 0) {
                            $templates[$group][] = $template;
                            $isGrouped = true;
                        }
                    }
        
                    if (!$isGrouped) {
                        $templates['zzz'][] = $template;
                    }
                    break;
        
                case 'stylevar':
                    $templates['StyleVar Special Templates'][] = $template;
                    break;
        
                case 'css':
                    $templates['CSS Special Templates'][] = $template;
                    break;
        
                case 'replacement':
                    $templates['Replacement Var Special Templates'][] = $template;
                    break;
            }
        }
        
        $this->registry->db->free_result($result);
        return $templates;
    }
    
    /**
     * Generates the full XML style 
     * 
     * @param    array        Templates from Style
     * @param    array        Template groupings
     * @param    array        Product information
     * @return   string       Generated Style XML
     */
    protected function generateStyleXml(array $templates, array $only, array $productInfo)
    {
        require_once(DIR . '/includes/class_xml.php');
        $xml = new vB_XML_Builder($this->registry);
        $xml->add_group('style', array('name' => $style['title'], 'vbversion' => $productInfo[$vbulletin->GPC['product']]['version'], 'product' => 'vbulletin', 'type' => 'custom'));
        
        foreach ($templates as $group => $grouptemplates) {
            $xml->add_group('templategroup', array('name' => isset($only["$group"]) ?  $only["$group"] :  $group));
            foreach($grouptemplates as $template) {
                $xml->add_tag('template', $template['template'], array('name' => htmlspecialchars($template['title']), 'templatetype' => $template['templatetype'], 'date' => $template['dateline'], 'username' => $template['username'], 'version' => htmlspecialchars_uni($template['version'])), true);
            }
            $xml->close_group();
        }
        
        $xml->close_group();
        
        $doc = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\r\n\r\n";
        $doc .= $xml->output();
        
        return $doc;
    }
}