<?php

require_once "Database.php";
require_once "MessageSender.php";
require_once "Replies.php";
require_once "Script.php";

class Router
{
    private $ms;
    private $db;
	private $botname;
	private $format;
    private $replies;
    
    public function __construct($ms, $pdo, $botname)
    {
        $this->botname = $botname;
        $this->ms = $ms;
		$this->db = new Database($pdo);
		$this->format = false;
        $this->replies = new Replies();
    }

    private function checkCommand($cmd, $target)
    {
        if ($cmd === $target)
            return true;
        if ($cmd === $target.'@'.$this->botname)
            return true;
        return false;
    }

    private function canRoute($update)
    {
        if (isset($update['channel_post']['chat']))
        {
            $this->db->update($update['channel_post']['chat'], '1');
            return false;
        }
        if (isset($update['message']['chat']))   
            return true;
        return false;
    }

    private function getFirstWhitespace($message)
    {
        $sp_pos = mb_strpos($message, ' ');
        $sp_pos = $sp_pos ? $sp_pos : mb_strlen($message);
        $nl_pos = mb_strpos($message, PHP_EOL);
        $nl_pos = $nl_pos ? $nl_pos : mb_strlen($message);
        return min($sp_pos, $nl_pos);
    }

    private function getCommandName($message)
    {
        return mb_substr($message, 0, $this->getFirstWhitespace($message));
    }

    private function getCommandArg($message)
    {
        $arg = mb_substr($message, $this->getFirstWhitespace($message) + 1);
        return trim($arg);
    }

    private function splitCmdArgToArray($message)
    {
        $arguments_str = $this->getCommandArg($message);
        
        if ($arguments_str)
        {
            $args = explode(',', $arguments_str);
            foreach ($args as &$val)
                $val = trim($val);
            if ($args)
                $keywords = array_filter($args);
            return $args;
        }
        else
            return false;
    }

    public function route($update)
    {
        if (!$this->canRoute($update))
            return false;
        
        $chat_object = $update['message']['chat'];
        $message = $update['message']['text'];
        $chat_id = $chat_object['id'];
        
        if (!$this->db->update($chat_object))
            return false;
        if (!$message)
            return false;

        $command_name = $this->getCommandName($message);
        
        if ($this->checkCommand($command_name, "/help"))
            $reply = $this->routeHelp($chat_id, 1);
        else if ($this->checkCommand($command_name, "/help2"))
            $reply = $this->routeHelp($chat_id, 2);
        else if ($this->checkCommand($command_name, "/start"))
            $reply = $this->routeStart($chat_id);
        else if ($this->checkCommand($command_name, "/stop"))
            $reply = $this->routeStop($chat_id);
        else if ($this->checkCommand($command_name, "/filter"))
            $reply = $this->routeFilter($chat_id);
        else if ($this->checkCommand($command_name, "/add"))
            $reply = $this->routeAdd($chat_id, $message);
        else if ($this->checkCommand($command_name, "/del"))
            $reply = $this->routeDel($chat_id, $message);
        else if ($this->checkCommand($command_name, "/clear"))
			$reply = $this->routeClear($chat_id);
		else if ($this->checkCommand($command_name, "/creator"))
			$reply = $this->routeCreator($chat_id);
        else if ($this->checkCommand($command_name, "/convert"))
            $reply = $this->routeConvert($chat_id, $message);
        else
            $reply = $this->routeUnknown($chat_id);

        $this->ms->sendMessage($chat_id, $reply, $this->format ?
                               ['parse_mode' => $this->format] : []);
    }

    private function routeHelp($chat_id, $n)
	{
		$this->format = "HTML";

        if ($n === 1)
            return $this->replies->help();
        if ($n === 2)
            return $this->replies->help2();
    }

    private function routeStart($chat_id)
    {
        $entry = $this->db->getEntry($chat_id);
        if ($entry['active'] == '1')
            return $this->replies->enableFail();
        $this->db->updateActive($chat_id, true);
        return $this->replies->enableSuccess();
    }
    private function routeStop($chat_id)
    {
        $entry = $this->db->getEntry($chat_id);
        if ($entry['active'] == '0')
            return $this->replies->disableFail();
        $this->db->updateActive($chat_id, false);
        return $this->replies->disableSuccess();
    }
    private function routeFilter($chat_id)
    {
        $entry = $this->db->getEntry($chat_id);
        if (!empty($entry['keywords'])) {
            $keywords = explode(',', $entry['keywords']);
            foreach ($keywords as &$val)
                $val = '- ' . $val;
            $keywords = implode(PHP_EOL, $keywords);
        }
        else
            $keywords = '';
        $msg = "Текущий список фильтров:" . PHP_EOL . $keywords;
        if (empty($keywords))
            $msg .= "(пусто)";
        return $msg;
    }
    
    private function routeAdd($chat_id, $message)
    {
        $args = $this->splitCmdArgToArray($message);
        if (!$args)
            $msg = "Перечислите фильтры через запятую после команды.";
        else {
            $this->db->addFilter($chat_id, $args);
            $msg = "Фильтры были добавлены.";
            $msg .= PHP_EOL . PHP_EOL . $this->routeFilter($chat_id);
        }
        return $msg;
    }
    
    private function routeDel($chat_id, $message)
    {
        $args = $this->splitCmdArgToArray($message);
        if (!$args)
            $msg = "Введите список фильтров для удаления после команды.";
        else
        {
            $count = $this->db->deleteFilter($chat_id, $args);
            $msg = "Удалено фильров: $count";
            $msg .= PHP_EOL . PHP_EOL . $this->routeFilter($chat_id);
        }
        return $msg;
    }
    private function routeClear($chat_id)
    {
        $this->db->clearFilter($chat_id);
        return $this->replies->clear();
	}

	private function routeCreator($chat_id)
	{
		$msg = "@suisei_v";
		return $msg;
	}

    private function routeConvert($chat_id, $message)
    {
        $text = $this->getCommandArg($message);
        if (empty($text))
            return "Передайте текст сразу после команды.";
        $script = new Script($text);   
        return (string)$script;
    }

    private function routeUnknown($chat_id)
    {
        $msg = $this->replies->dontUnderstand();
        return $msg;
    }
}
