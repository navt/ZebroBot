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
        if ($this->alreadyVoted()) {
            return;
        }
        $this->DA->addCount($args[0]);
        $this->DA->addMember('callback_query');
    }
}
