<?php

class MessageSender {
    private $ch;
    private $urlbase;
    
    public function __construct($token, $curlproxy = false) {
        $this->urlbase = "https://api.telegram.org/bot" . $token;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if ($curlproxy) {
            curl_setopt($ch, CURLOPT_PROXY, $curlproxy['host']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, $curlproxy['type']);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $curlproxy['auth']);
        }
        $this->ch = $ch;
    }
    
    public function __destruct() {
        curl_close($this->ch);
    }
    
    public function getUrlBase() {
        return $this->urlbase;
    }
    
    public function request($method, $params = []) {
        $url = $this->urlbase . "/" . $method;
        $params_str = http_build_query($params);
        if ($params_str)
            $url .= '?' . $params_str;
        curl_setopt($this->ch, CURLOPT_URL, $url);
        $res = curl_exec($this->ch);
        $error = curl_error($this->ch);
        if ($error) {
            error_log("curl error:  " . $error);
            return false;
        }
        $httpcode = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $decoded = json_decode($res, true);
        if (!$decoded['ok']) {
            error_log("$method error:  " .
                      var_export($decoded, true) .
                      " (code $httpcode)");
            return $httpcode;
        }
        return $httpcode;
    }
    
    public function sendMessage($chat_id, $text, $optional = []) {
        if (!$chat_id || !$text)
            return false;
        if (mb_strlen($text) > 4096)
            $text = "Message is too long."; //todo: split into 2,3... messages
        $query = array(
            'chat_id' => $chat_id,
            'text' => $text
        );
        $query = array_merge($query, $optional);
        return $this->request("sendMessage", $query);
    }
}
