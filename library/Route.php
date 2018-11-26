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
        }
        /* --- отладка ---
        $str = 'сюда стоку json';
        $this->basis->come = json_decode($str, false);
        var_dump($this->basis->come);
         */
        if (isset($this->basis->come->message)) {
            // это текстовое сообщение или кнопка
            $this->parsingMessage();
        }
        if (isset($this->basis->come->callback_query)) {
            // это инлайн кнопка
            $this->parsingCallbackQuery();
        }
    }
    
    private function parsingMessage() {  
        $this->controller = new TextController($this->basis, $this->DA);
        $text = $this->basis->come->message->text;

        if ($text !== '') {
            if ($this->basis->come->message->entities[0]->type === "bot_command") { 
                $this->controller->commands($text); // это какая-то команда
            } else {
                $this->controller->index();         // это текст
            } 
        }
    }
    
    private function parsingCallbackQuery() {
        // по data из callback_query выбираем контроллер
        $data = $this->basis->come->callback_query->data;
        $call = \json_decode($data, false);
        if (is_object($call)) {
            $this->controller = new ButtonController($this->basis, $this->DA);
            $this->controller->commuter($call);
        }
    }
}
