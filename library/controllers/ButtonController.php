<?php
namespace tools\controllers;
use tools\controllers\BaseController;
use tools\AppException;

class ButtonController extends BaseController 
{
    
    private $chat_id;
    
    public function __construct(\tools\Basis $basis, \tools\access\DataAccess $DA) {
        parent::__construct($basis, $DA);
        $this->chat_id = $this->basis->getChatId();
    }
    
    public function index() {}
    
    public function commuter($call) {
        $method = $call->command;
        $args = $call->params;
        if ($args === []) {
            $this->$method();
        } else {
            $this->$method($args);
        } 
    }
    
    private function addCount($args) {
        if ($this->alreadyVoted() === true) {
            return;
        }
        $this->DA->addCount($args[0]);
        $this->DA->addMember();

        $text = "Благодарим за участие в опросе!";
        $params = ["chat_id" => $this->chat_id,
            "text" => $text];
        $this->basis->request("sendMessage", $params);

        // inline клавиатура больше не нужна
        $message_id = $this->basis->getMessageId();
        $params = ["chat_id" => $this->chat_id,
            "message_id" => $message_id,
            "reply_markup" => ''];
        $this->basis->request("editMessageReplyMarkup", $params);
      
    }
}
