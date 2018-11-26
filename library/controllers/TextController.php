<?php
namespace tools\controllers;
use tools\controllers\BaseController;
use tools\AppException;

class TextController extends BaseController {
    
    private $chat_id;
    
    public function __construct(\tools\Basis $basis, \tools\access\DataAccess $DA) {
        parent::__construct($basis, $DA);
        $this->chat_id = $this->basis->getChatId();
    }

    public function index() {
        if ($this->basis->come->message->chat->type === "private") {
            $text = '';
            $tp2 = "Этот бот не умеет отвечать на произвольный текст!";
            if (isset($this->basis->come->message->chat->first_name)) {
               $first_name = $this->basis->come->message->chat->first_name;
               $text = "{$first_name}!\n".$tp2 ;
            }
            if ($text === '') {
                $text = $tp2;
            }
            $params = ["chat_id" => $this->chat_id,
                "text" => $text];
            try {
                $this->basis->request("sendMessage", $params);
            } catch (AppException $e) {
                $this->toLog($e->getMessage());
            }
        }
    }
    
    public function commands($cmd) {
        switch ($cmd) {
            case '/start':
                $this->start();
                break;
            case '/vote':
                $this->vote();
                break;
            case '/result':
                $this->result();
                break;
            default:
                $params = ["chat_id" => $this->chat_id,
                    "text" => "Команда {$cmd} не поддерживается."];
                $this->basis->request("sendMessage", $params);
                break;
        }
    }
    
    private function start() {
        $text = "Доброго времени суток!\nВам доступны команды:\n/vote - голосовать\n/result - результаты";
        $params = ["chat_id" => $this->chat_id,
                "text" => $text];
        $this->basis->request("sendMessage", $params);
    }
    
    private function vote() {
        if ($this->alreadyVoted()) {
            return;
        }
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Да', 'callback_data' => '{"command":"addCount", "params":["vote_yes"]}'],
                    ['text' => 'Нет', 'callback_data' => '{"command":"addCount", "params":["vote_no"]}'],
                    ['text' => 'Не уверен', 'callback_data' => '{"command":"addCount", "params":["vote_random"]}'],
                ]
            ] 
        ];
        $s = json_encode($keyboard); 
        $params = ["chat_id" => $this->chat_id,
            "text" => "Защищают ли пешехода полоски перехода на асфальте?",
            "reply_markup" => $s];
        $this->basis->request("sendMessage", $params);

    }
    
    private function result() {
        $this->DA->initAccess();
        $counts = $this->DA->getCounts();
        if (is_array($counts)) {
            $text ="Ответили:\nДа - {$counts['vote_yes']}\nНет - {$counts['vote_no']}\nНе уверен - {$counts['vote_random']}\n";
            $params = ["chat_id" => $this->chat_id,
                "text" => $text];
            $this->basis->request("sendMessage", $params);
        }
    }

}

