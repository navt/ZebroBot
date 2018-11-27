<?php
namespace tools\access;
use tools\access\DataAccess;
use tools\Fix;
use tools\Basis;
use tools\AppException;

class FileAccess implements DataAccess
{
    // http://php.net/manual/ru/simplexmlelement.addchild.php
    public   $dataObject;
    private  $basis;     // объект класса Basis 
    private  $file;      // xml-файл

    public function __construct(Basis $basis) {
        $this->basis = $basis;
        $f = \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT').Fix::DATA_FILE;
        if (file_exists($f)) {
            $this->file = $f;
        } else {
            throw new AppException(__METHOD__." Файл данных {$f} не существует.");
        }
    }

    public function initAccess() {
        echo __METHOD__."\n";
        if (isset($this->file)) {
            $this->dataObject = simplexml_load_file($this->file);
        } 
        return $this;
    }
    public function getCounts() {
        if (is_object($this->dataObject)) {
            $counts['vote_yes'] = $this->dataObject->vote_yes;
            $counts['vote_no'] = $this->dataObject->vote_no;
            $counts['vote_random'] = $this->dataObject->vote_random;
        } else {
            $counts = null;
        }
        return $counts;
    }
    
    public function addCount($name) {
        $votes = ['vote_yes','vote_no','vote_random'];
        if (\in_array($name, $votes) && \is_object($this->dataObject)) {
            $this->dataObject->$name =  (integer)$this->dataObject->$name + 1; 
        }
        try {
            $this->saveChange();
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
        }
    }
    private function saveChange() {
        if (is_object($this->dataObject)) {
            $r = $this->dataObject->asXML($this->file);
        }
        if ($r === false){
            throw new AppException(__METHOD__." Запись в файл {$this->file} не удалась.");
        }
        return $this;
    }
    
    public function findMember($user_id) {
        echo __METHOD__."\n";
        if (is_object($this->dataObject)) {
            foreach ($this->dataObject->member as $member) {
                if ((integer)$member->user_id === (integer)$user_id) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function addMember($type='message') {
        if (is_object($this->dataObject)) {
            $member = $this->dataObject->addChild('member');
            $member->addChild('user_id', $this->basis->getUserId());
            $member->addChild('username', $this->basis->getUsername()); 
            $member->addChild('vote_date', date('Y-m-d H:i:s'));
            $this->saveChange();
        }
        return $this;
    }
}

