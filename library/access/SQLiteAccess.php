<?php
namespace tools\access;
use tools\access\DataAccess;
use tools\Fix;
use tools\Basis;
use tools\AppException;

class SQLiteAccess implements DataAccess{
    
    private $file;      // файл базы данных
    private $DB;        // экземпляр класса SQLite 3
    private $basis;     // объект класса Basis
    
    public function __construct(Basis $basis) {
        $this->basis = $basis;
        $f = \filter_input(\INPUT_SERVER, 'DOCUMENT_ROOT').Fix::SQLITE_FILE;
        if (file_exists($f)) {
            $this->file = $f;
        } else {
            throw new AppException(__METHOD__." Файл базы данных {$f} не существует.");
        }
    }
    public function initAccess() {
        try {
            $this->DB = new \SQLite3($this->file);
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
            exit;
        }
        return $this;
    }
    
    public function getCounts() {
        $q = "SELECT key,value FROM counts";
        $r = $this->DB->query($q);
        while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
            $counts[$row["key"]] = $row["value"];
        }
        return $counts;
    }
    
    public function addCount($name) {
        $q = "UPDATE counts SET value = value + 1 WHERE key = '{$name}'";
        try {
            $r = $this->DB->query($q);
            if ($r === false) {
                throw new AppException(__METHOD__." Не обновлёна запись с ключом {$name} в таблице counts.");
            }
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
            exit;
        }

        
    }

    public function addMember() {
        $user_id = $this->basis->getUserId();
        $chat_id = $this->basis->getChatId();
        $username = $this->basis->getUsername();
        $first_name =$this->basis->getFirstName();
        $q = "INSERT INTO users (user_id,chat_id,username,first_name) VALUES ($user_id, $chat_id, '{$username}', '{$first_name}')";
        try {
            $r = $this->DB->query($q);
            if ($r === false) {
                throw new AppException(__METHOD__." Не добавлена запись юзера {$user_id} в таблицу users.");
            }
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
            exit;
        }

        
    }

    public function findMember() {
        $user_id = $this->basis->getUserId();
        echo $user_id.PHP_EOL;
        $chat_id = $this->basis->getChatId();
        echo $chat_id.PHP_EOL;
        $q = "SELECT user_id, chat_id FROM users WHERE (user_id = $user_id AND chat_id = $chat_id)";
        $r = $this->DB->query($q);
        if (is_array($r->fetchArray(SQLITE3_ASSOC))) {
            return true;
        } else {
            return false;
        }
    }
    
    public function addVote() {
        $user_id = $this->basis->getUserId();
        $vote_value = '';                    // ?
        $vote_date = date('Y-m-d H:i:s');
        $q = "INSERT INTO votes (user_id,vote_value,vote_date) VALUES ($user_id, '{$vote_value}', '{$vote_date}')";
        try {
            $r = $this->DB->query($q);
            if ($r === false) {
                throw new AppException(__METHOD__." Не добавлена запись голосования юзера {$user_id} в таблицу votes.");
            }
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
            exit;
        }
    }
    
    public function addComment() {
        $comment = '';
        $user_id = $this->basis->getUserId();
        $q = "UPDATE votes SET comment = {$comment} WHERE user_id = '{$user_id}'";
        try {
            $r = $this->DB->query($q);
            if ($r === false) {
                throw new AppException(__METHOD__." Не добавлен комментарий в запись с user_id {$user_id} в таблице votes.");
            }
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
        }
    }

}
