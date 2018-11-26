<?php
namespace tools;
use tools\ask\AskInterface;
use tools\Fix;
use tools\AppException;

class Basis
{
    protected $ask;   // интерфейс для обращения к url
    public    $come;  // обновления от бота

    public function __construct(AskInterface $ask) {
        $this->ask = $ask;
    }
    
    public function request($apiMethod=null,$params=null) {
        if (!$apiMethod) {
            throw new AppException(__METHOD__." Не определён API метод для этого запоса.");
        }
        $url = \sprintf('%s%s%s%s', Fix::API_URL, Fix::TOKEN, '/', $apiMethod);
        try {
            $reply = $this->ask->getContents($url, $params);
        } catch (AppException $e) {
            $this->toLog($e->getMessage());
        }
        $replyObj = json_decode($reply, false);
        if (isset($replyObj->ok)) {
            if (false === $replyObj->ok) {
                $s = \sprintf('%s%s%s', '--- FALSE ответ от API ---', "\r\n", $reply);
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
            return $this;
        } else {
            $this->come = null;
            $content = \sprintf('%s%s%s', ' --- Неожиданное обновление ---', "\r\n", $content);
            throw new AppException(__METHOD__.$content);
        }
    }
    
    public function toLog($str) {
        if (Fix::LOG_ON !== true) {
            return;
        }
        $path = \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT', FILTER_SANITIZE_SPECIAL_CHARS).Fix::LOG_FILE;
        $format = '%s %s%s';
        $s = sprintf($format, date("Y-m-d H:i:s"), $str, "\r\n");
        \file_put_contents($path, $s, \FILE_APPEND);
    }
    
    public function getChatId() {
        if (isset($this->come->callback_query)) {
            return $this->come->callback_query->message->chat->id;
        }
        if (isset($this->come->message)) {
            return $this->come->message->chat->id;
        }
        return null;
    }
    public function getUserId() {
        if (isset($this->come->callback_query)) {
            return $this->come->callback_query->from->id;
        }
        if (isset($this->come->message)) {
            return $this->come->message->from->id;
        }
        return null;
    }

}

