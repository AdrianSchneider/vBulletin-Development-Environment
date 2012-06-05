<?php

class VDE_Command_ContentGenerateThreads extends VDE_Command
{
    static protected $name = 'content:generate:thread';
    static protected $description = "Generates new threads/posts.";
    
    static protected $arguments = array(
        array('threads',     self::ARG_PROMPT,  'Threads to create?'),
        array('replies_min', self::ARG_OPTIONAL, 'Replies per thread (min)'),
        array('replies_max', self::ARG_OPTIONAL, 'Replies per thread (max)'),
        array('source',      self::ARG_OPTIONAL, 'Path to content file.')
    );
    
    /**
     * @var    string        Post content
     */
    protected $content;
    
    /**
     * @var    string        Allowed forum IDs
     */
    protected $forums;
    
    /**
     * Generates threads
     * @param    integer        Number of threads to create
     * @param    integer        Number of replies to create [lower boundary]
     * @param    integer        Number of replies to create [higher boundary]
     * @param    string|null    Location of post data
     */
    public function run($threads, $repliesMin = 0, $repliesMax = 50, $content = null)
    {
        $this->content = file_get_contents($content ?: DIR . '/includes/vde/data/posts.txt');
        $this->forums  = $this->getActiveForums();
        
        for ($i = 0; $i < $threads; $i++) {
            $thread = datamanager_init('Thread_FirstPost', $this->registry, ERRTYPE_ARRAY, 'threadpost');
            
            $thread->set('title', $this->getRandomTitle());
            $thread->set('pagetext', $this->getRandomBody());
            $thread->set('userid', $this->getRandomUser());
            $thread->set('ipaddress', '127.0.0.1');
            $thread->set('allowsmilie', false);
            $thread->set('visible', 1);
            $thread->set('dateline', $time = TIMENOW - rand(86400, (86400*45)));
            $thread->set('forumid', $this->getRandomForum());
            
            $thread->pre_save();
            if (!empty($thread->errors)) {
                throw new Exception('Error creating thread: ' . implode(', ', $thread->errors));
            }
            
            $threadId = $thread->save();
            $replies = rand($repliesMin, $repliesMax);
            
            for ($i = 0; $i < $replies; $i++) {
                $reply = datamanager_init('Post', $this->registry, ERRTYPE_ARRAY, 'threadpost');
                
                $reply->set('threadid', $threadId);
                $reply->set('title', '');
                $reply->set('pagetext', $this->getRandomBody());
                $reply->set('userid', $this->getRandomUser());
                $reply->set('ipaddress', '127.0.0.1');
                $reply->set('allowsmilie', false);
                $reply->set('visible', 1);
                $reply->set('dateline', $time += (rand(3, 10000)));
                
                $reply->pre_save();
                if (!empty($reply->errors)) {
                    throw new Exception('Error creating thread: ' . implode(', ', $reply->errors));
                }
                
                $reply->save();
            }
        }
    }
    
    /**
     * Returns a list of forums that users can post in
     * @return    array        Forum IDs that allow posting
     */
    protected function getActiveForums()
    {
        $forums = $this->registry->forumcache;
        shuffle($forums);
        
        $forumIds = array();
        foreach ($forums as $forum) {
            if (!($forum['options'] & $this->registry->bf_misc_forumoptions['active'])) {
                continue;
            }
        
            if (!($forum['options'] & $this->registry->bf_misc_forumoptions['allowposting'])) {
                continue;
            }
            if (!($forum['options'] & $this->registry->bf_misc_forumoptions['cancontainthreads'])) {
                continue;
            }
        
            $forumIds[] = $forum['forumid'];
        }
        
        return $forumIds;
    }
    
    /**
     * Generates a random post title
     * @return    string        Post title
     */
    protected function getRandomTitle()
    {
        $lines = array_filter(explode("\n", $this->content), 'trim');
        $words = explode(' ', $lines[array_rand($lines)]);
        
        return preg_replace('/([^ a-zA-Z])/', '', implode(' ', array_slice($words, 0, rand(4, 7))));
    }
    
    /**
     * Generates random post content 
     * @return    string        Post content
     */
    protected function getRandomBody()
    {
        $lines = array_filter(explode("\n", $this->content), 'trim');
        shuffle($lines);
        
        $out = '';
        $paragraphs = rand(1, 6);
        
        for ($i = 0; $i < $paragraphs; $i++) {
            $out .= $lines[$i] . "\n\n";
        }
        
        return trim($out);
    }
    
    /**
     * Fetches a random user from the database
     * @return    integer        Random User ID
     */
    protected function getRandomUser()
    {
        $id = $this->registry->db->query_first("
            SELECT userid
              FROM " . TABLE_PREFIX . "user
            ORDER
                BY rand()
             LIMIT 1
        ");
        
        return intval($id['userid']);
    }
    
    /**
     * Returns a random forum ID that users can post in
     * @return    integer        Forum ID
     */
    protected function getRandomForum()
    {
        return $this->forums[array_rand($this->forums)];
    }
}