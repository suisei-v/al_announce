<?php

class Database
{
    private $pdo;
    
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function getEntry($chat_id)
    {
        $stmt = $this->pdo->prepare(
            'SELECT chat_id, keywords FROM chats WHERE chat_id = :id');
        $stmt->execute(['id' => $chat_id]);
        $res = $stmt->fetch();
        //error_log(var_export($res, true));
        return $res;
    }

    public function getAllEntries()
    {
        $stmt = $this->pdo->prepare(
            'SELECT chat_id, active, keywords FROM chats');
        $stmt->execute();
        $res = $stmt->fetchAll();
        return $res;
    }
   
    public function update($chat_object, $active = '0')
    {
        if (!$chat_object['id'])
            return false;
        if ($chat_object['type'] == 'private')
            $name = ($chat_object['first_name'] ?? '') . ' ' .
                  ($chat_object['last_name'] ?? '');
        else
            $name = $chat_object['title'] ?? '';
        
        if ($this->getEntry($chat_object['id'])) {
            $stmt = $this->pdo->prepare(
                   'UPDATE chats SET ' .
                   'username = :username,' .
                   'name = :name ' .
                   'WHERE chat_id = :chat_id');
        } else {
            $stmt = $this->pdo->prepare(
                   'INSERT INTO chats ' .
                   '(chat_id, type, active, username, name, keywords) ' .
                   'VALUES ' .
                   '(:chat_id, :type, :active, :username, :name, :keywords)');
            $stmt->bindValue(':type', $chat_object['type']);
            $stmt->bindValue(':active', $active);
            $stmt->bindValue(':keywords', '');
        }
        
        $stmt->bindValue(':chat_id', $chat_object['id']);
        $stmt->bindValue(':username', $chat_object['username'] ?? '');
        $stmt->bindValue(':name', $name);
        
        $stmt->execute();
        return true;
    }

    public function updateActive($chat_id, $active)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE chats SET ' .
            'active = :active ' .
            'WHERE chat_id = :chat_id');
        $stmt->bindValue(':active', $active ? "1" : "0");
        $stmt->bindValue(':chat_id', $chat_id);
        
        $stmt->execute();
    }

    public function addFilter($chat_id, $keywords)
    {
        if (!$keywords)
            return false;
        $entry = $this->getEntry($chat_id);
        $old_keywords = $entry ? $entry['keywords'] : '';
        $old_keywords_array = explode(',', $old_keywords);

        $keywords = array_merge($old_keywords_array, $keywords);
        $keywords = array_unique($keywords);
        $keywords = array_filter($keywords);
        $keywords = implode(',', $keywords);
        
        $stmt = $this->pdo->prepare(
            'UPDATE chats SET ' .
            'keywords = :keywords ' .
            'WHERE chat_id = :chat_id');
        $stmt->bindValue(':keywords', $keywords);
        $stmt->bindValue(':chat_id', $chat_id);

        $stmt->execute();
    }

    public function deleteFilter($chat_id, $keywords)
    {
        if (!$keywords)
            return false;
        $entry = $this->getEntry($chat_id);
        $old_keywords = $entry ? $entry['keywords'] : '';
        $old_keywords_array = explode(',', $old_keywords);
        $count = 0;
        foreach ($old_keywords_array as &$old_val)
            foreach ($keywords as $del_val)
                if (mb_strpos($old_val, $del_val) !== false)
                {
                    $old_val = '';
                    $count++;
                    break ;
                }
        $keywords = array_filter($old_keywords_array);
        $keywords = implode(',', $keywords);
        
        $stmt = $this->pdo->prepare(
            'UPDATE chats SET ' .
            'keywords = :keywords ' .
            'WHERE chat_id = :chat_id');
        $stmt->bindValue(':keywords', $keywords);
        $stmt->bindValue(':chat_id', $chat_id);

        $stmt->execute();
        return $count;
    }

    public function clearFilter($chat_id)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE chats SET ' .
            'keywords = :keywords ' .
            'WHERE chat_id = :chat_id');
        $stmt->bindValue(':keywords', '');
        $stmt->bindValue(':chat_id', $chat_id);

        $stmt->execute();
    }
}
