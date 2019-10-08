<?php

class Announcer
{
    private $ms;
    private $db;

    public function __construct($ms, $pdo)
    {
        $this->ms = $ms;
        $this->db = new Database($pdo);
    }

    private function isNeedToSend($entry, $text)
    {
        if (empty($entry['keywords']))
            return true;
        $keywords = explode(',', $entry['keywords']);
        foreach ($keywords as $word)
        {
            if (mb_strpos(mb_strtolower($text), mb_strtolower($word)) !== false)
                return true;
        }
        return false;
    }

    public function announce($title, $text, $url)
    {
        $send = false;
        $msg = $title . PHP_EOL . $text . PHP_EOL . $url;
        $entries = $this->db->getAllEntries();
        foreach ($entries as $val)
        {
            $chat_id = $val['chat_id'];
            if ($val['active'] == '1' && $this->isNeedToSend($val, $text))
            {
                $res = $this->ms->sendMessage($chat_id, $msg);
                if ($res == 403)
                    $this->db->delete($chat_id);
            }
        }
    }
}
