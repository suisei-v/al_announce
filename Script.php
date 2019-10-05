<?php

require_once "Subtitle.php";

class Script
{
    private $script = [];
    
    public function __construct($text)
    {
        $strings = explode(PHP_EOL, $text);
        $i = 0;
        foreach ($strings as $str) {
            $this->script[$i] = new Subtitle($str);
            $i++;
        }
    }
    
    public function __toString()
    {
        $res = '';
        foreach ($this->script as $sub) {
            $res .= ($sub->isValid() ? $sub : $sub->getRaw()) . PHP_EOL;
        }
        return $res;
    }
}
