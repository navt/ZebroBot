<?php
namespace tools;
use tools\ask\AskInterface;
use tools\Fix;
use tools\AppException;

class Basis
{
    protected $ask;          // интерфейс для обращения к url
    public    $come;         // обновления от бота
    public    $updType = ''; // 'callback_query' или 'message'

    public function __construct(AskInterface $ask) {
        $this->ask = $ask;
    }
    
    public function request($apiMethod=null,$params=null) {
        if (!$apiMethod) {
            throw new AppException(__METHOD__." Не определён API метод для этого запоса.");
        }
        $url = \sprintf('%s%s/%s', Fix::API_URL, Fix::TOKEN, $apiMethod);
        try {
            $reply = $this->ask->getContents($url, $params);
        } catch (AppException $e) {
            $this->toLog($e->getMessage());
        }
        $replyObj = json_decode($reply, false);
        if (isset($replyObj->ok)) {
            if (false === $replyObj->ok) {
                $s = \sprintf("--- FALSE ответ от API ---\r\n%s", $reply);
                throw new AppException(__METHOD__.$s);
            }
        }
        return $reply;
    }
    
    public function webhookUpdate($assoc=false) {
        $content = file_get_contents("php://input");
        if (\is_string($content)) { 
            $this->come = \json_decode($content, $assoc);
            /*
            $this->toLog("--- Боту пришла строка JSON ---\n".$content);
             */
        } 
        if (is_object($this->come) || is_array($this->come)) {
            if (isset($this->come->message)) {
                $this->updType = Fix::MSG;
            }
            if (isset($this->come->callback_query)) {
                $this->updType =  Fix::CBQ;
            } 
            return $this;
        } else {
            $this->come = null;
            $content = \sprintf("--- Неожиданное обновление ---\r\n%s", $content);
            throw new AppException(__METHOD__.$content);
        }
    }
    
    public function toLog($str) {
        if (Fix::LOG_ON !== true) {
            return;
        }
        $path = \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_SPECIAL_CHARS).Fix::LOG_FILE;
        $format = "%s %s\r\n";
        $s = sprintf($format, date("Y-m-d H:i:s"), $str);
        \file_put_contents($path, $s, \FILE_APPEND);
    }
    // Геттеры
    public function getChatId() {
        if ($this->updType ===  Fix::MSG) {
            if (isset($this->come->message->chat->id)) {
                return $this->come->message->chat->id;
            }
        }
        if ($this->updType ===  Fix::CBQ) {
            if (isset($this->come->callback_query->message->chat->id)) {
                return $this->come->callback_query->message->chat->id;
            }
        }
        return null;
    }
    
    public function getChatType() {
        if ($this->updType ===  Fix::MSG) {
            if (isset($this->come->message->chat->type)) {
                return $this->come->message->chat->type;
            }
        }
        if ($this->updType ===  Fix::CBQ) {
            if (isset($this->come->callback_query->message->chat->type)) {
                return $this->come->callback_query->message->chat->type;
            }
             
        }
        return null;
    }
    
    public function getUserId() {
        if (isset($this->come->{$this->updType}->from->id)) {
            return $this->come->{$this->updType}->from->id;
        }
        return null;
    }
    public function getFirstName() {
       if (isset($this->come->{$this->updType}->from->first_name)) {
           return $this->come->{$this->updType}->from->first_name;
        }
        return null;
    }
    
    public function getUsername() {
        if (isset($this->come->{$this->updType}->from->username)) {
            return $this->come->{$this->updType}->from->username;
        }
        return null;
    }
    
    public function getText() {
        if ($this->updType ===  Fix::MSG) {
            if (isset($this->come->message->text)) {
                return $this->come->message->text;
            }
        }
        if ($this->updType ===  Fix::CBQ) {
            if (isset($this->come->callback_query->message->text)) {
                return $this->come->callback_query->message->text;
            }
        }
        return null;
    }

    public function isBotCommand() {
        if ($this->come->message->entities[0]->type === "bot_command") {
            return true;
        } else {
            return false;
        }
    }

    public function getCbqData() {
        return $this->come->callback_query->data;
    }

    public function getMessageId() {
        if ($this->updType ===  Fix::MSG) {
            if (isset($this->come->message->message_id)) {
                return $this->come->message->message_id;
            }
        }
        if ($this->updType ===  Fix::CBQ) {
            if (isset($this->come->callback_query->message->message_id)) {
                return $this->come->callback_query->message->message_id;
            }
        }
        return null;
    }    
}
