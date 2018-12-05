<?php
namespace tools;
use tools\Basis;
use tools\access\DataAccess;
use tools\AppException;
use tools\controllers\TextController;
use tools\controllers\ButtonController;

class Route
{
    private  $basis;     // объект класса Basis 
    private  $DA;
    private  $controller; 

    public function __construct(Basis $basis, DataAccess $DA) {
        $this->basis = $basis;
        $this->DA = $DA;
    }
    
    public function checkUpdateType() {

        try {
           $this->basis->webhookUpdate(false); 
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
            exit;
        }

        // был ли уже здесь данный юзер?
        if ($this->DA->findMember() === false) {
            $this->DA->addMember();      // добавляем
        }
        if ($this->basis->updType ===  Fix::MSG) {
            // это текстовое сообщение или кнопка
            $this->parsingMessage();
        }
        if ($this->basis->updType ===  Fix::CBQ) {
            // это инлайн кнопка
            $this->parsingCallbackQuery();
        }
    }
    
    private function parsingMessage() {  
        $this->controller = new TextController($this->basis, $this->DA);
        $text = $this->basis->getText();

        if ($text !== '') {
            if ($this->basis->isBotCommand()) { 
                $this->controller->commands($text);    // это какая-то команда
                exit;
            }
            $command = $this->DA->awaitingAction();
            if ($command !== false) {
                $this->controller->commands($command); // эта команда из стека
            } else {
                $this->controller->index();            // это текст
            }
        }
    }
    
    private function parsingCallbackQuery() {
        // по data из callback_query выбираем контроллер
        $data = $this->basis->getCbqData();
        $call = \json_decode($data, false);
        if (is_object($call)) {
            $this->controller = new ButtonController($this->basis, $this->DA);
            $this->controller->commuter($call);
        }
    }

}
