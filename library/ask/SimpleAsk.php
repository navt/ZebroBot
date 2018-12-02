<?php
namespace tools\ask;
use tools\ask\AskInterface;
use tools\AppException;

class SimpleAsk implements AskInterface{
    
    private $params;
    private $url;


    public function getContents($url, $params=null) {
        $this->params = $params;
        $this->url = $url;
        
        $address = $this->address();
        $r = \file_get_contents($address);
        if ($r === false) {
            throw new AppException(__METHOD__." Неудачное обращение к адресу {$address}");
        }
        return $r;
    }
    private function address() {
        if ($this->params === null){
            $queryString = '';
        }
        if (\is_string($this->params)){
            $queryString = \sprintf('?%s', $this->params);
        }
        if (is_array($this->params)){
            $queryString = \sprintf('?%s', http_build_query($this->params));
        }
        $address = \sprintf('%s%s', $this->url, $queryString);
        
        return $address;
    }
}

