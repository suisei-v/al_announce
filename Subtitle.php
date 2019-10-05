<?php

class Subtitle
{
    private $raw;
    private $sub;
    private $start_hour, $start_min, $start_sec;
    private $end_hour, $end_min, $end_sec;
    private $actor, $text;
    private $valid = false;
    
    public function __construct($str)
    {
        $this->sub = $str;
        if ($this->checkValid()) {
            $this->valid = true;
            $this->setValues();
        }
    }
    
    public function isValid()
    {
        return $this->valid;
    }
    
    public function getRaw()
    {
        return $this->sub;
    }
    
    public function getText()
    {
        return $this->text;
    }
    
    private function checkValid()
    {
        if (!(substr($this->sub, 0, 10) === "Dialogue: ") &&
            !(substr($this->sub, 0, 9) === "Comment: "))
            return false;
        if (strlen($this->sub) < 43)
            return false;
        $i = 0;
        for ($j = 0; $j < 9; $j++, $i++) {
            $i = strpos($this->sub, ',', $i);
            if ($i === false)
                return false;
        }
        return true;
    }
    
    private function setValues()
    {
        $i = strpos($this->sub, ',');
        $i++;
        $this->start_hour = substr($this->sub, $i, 1);
        $this->start_min = substr($this->sub, $i += 2, 2);
        $this->start_sec = substr($this->sub, $i += 3, 2);
        
        $this->end_hour = substr($this->sub, $i += 6, 1);
        $this->end_min = substr($this->sub, $i += 2, 2);
        $this->end_sec = substr($this->sub, $i += 3, 2);
        $i = strpos($this->sub, ',', $i);
        $i++;
        for ($j = 0; $j < 6; $j++, $i++) {
            $i = strpos($this->sub, ',', $i);
        }
        $this->text = substr($this->sub, $i);
    }
    
    private function clearText()
    {
        $ret = str_replace('\\N', ' ', $this->text);
        $ret = preg_replace('/{.*?}/', ' ', $ret);
        $ret = preg_replace('!\s+!', ' ', $ret);
        $ret = trim($ret);
        return $ret;
    }
    
    public function __toString()
    {
        $res = ($this->start_hour == '0' ? '' : $this->start_hour . ':') .
             $this->start_min . ':' . $this->start_sec . ' - ' .
             ($this->end_hour == '0' ? '' : $this->end_hour . ':') .
             $this->end_min . ':' . $this->end_sec . '  ' . $this->clearText();
        return $res;
    }
}
