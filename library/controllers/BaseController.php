<?php
namespace tools\controllers;
use tools\Basis;
use tools\access\DataAccess;

abstract class BaseController
{
    protected $basis;
    protected $DA;
    
    public function __construct(Basis $basis, DataAccess $DA) {
        $this->basis = $basis;
        $this->DA = $DA;
    }
    
    abstract function index();
    
    protected function alreadyVoted() {
        if ($this->DA->isVoted()) {
            // человек уже голосовал
            $params = ["chat_id" => $this->basis->getChatId(),
                "text" => "Голосовать несколько раз - против правил."];
            $this->basis->request("sendMessage", $params);
            return true;
        } else {
            return false;
        } 
    }
}

