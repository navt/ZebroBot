<?php
namespace tools;
use tools\Basis;

class WebHook extends Basis
{
    private $dir = "/botdir";
    private $handler = "/index.php";

    public function init() {
        $params = 'url=https://'. \filter_input(\INPUT_SERVER, 'SERVER_NAME').$this->dir.$this->handler;
        $response = $this->request('setWebhook', $params); 
        return $response;
    }
    public function getInfo() {
        $response = $this->request('getWebhookInfo'); 
        return $response;
    }

}
