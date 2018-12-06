<?php
namespace tools\access;
use tools\access\DataAccess;
use tools\Fix;
use tools\Basis;
use tools\AppException;

class SQLiteAccess implements DataAccess{

    private $DB;        // экземпляр класса SQLite 3
    private $basis;     // объект класса Basis
    private $votes = ['vote_yes','vote_no','vote_random'];
    
    public function __construct(Basis $basis) {
        $this->basis = $basis;
        $f = \getenv('DOCUMENT_ROOT').Fix::SQLITE_FILE;
        try {
            if (file_exists($f)) {
                $this->DB = new \SQLite3($f);
            } else {
                throw new AppException(__METHOD__." Файл базы данных {$f} не существует.");
            }
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
        $q = '';
        $key = \array_search($name, $this->votes);
        if ($key !== false) {
            $q = "UPDATE counts SET value = value + 1 WHERE key = '{$this->votes[$key]}'";
        }
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
        $user_id = (integer)$this->basis->getUserId();
        $chat_id = (integer)$this->basis->getChatId();
        $username = $this->DB->escapeString($this->basis->getUsername());
        $first_name = $this->DB->escapeString($this->basis->getFirstName());
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
        $user_id = (integer)$this->basis->getUserId();
        $chat_id = (integer)$this->basis->getChatId();

        $q = "SELECT user_id, chat_id FROM users WHERE (user_id = $user_id AND chat_id = $chat_id)";
        $r = $this->DB->query($q);
        if (is_array($r->fetchArray(SQLITE3_ASSOC))) {
            return true;
        } else {
            return false;
        }
    }
    
    public function addVote($vote_value = '') {
        $user_id = (integer)$this->basis->getUserId();
        $vote_date = date('Y-m-d H:i:s');
        $q = '';
        $key = \array_search($vote_value, $this->votes);
        if ($key !== false) {
            $q = "INSERT INTO votes (user_id,vote_value,vote_date) VALUES ($user_id, '{$this->votes[$key]}', '{$vote_date}')";
        }
        try {
            $r = $this->DB->query($q);
            if ($r === false) {
                throw new AppException(__METHOD__." Не добавлена запись о голосовании юзера {$user_id} в таблицу votes.");
            }
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
            exit;
        }
    }
    
    public function isVoted() {
        $user_id = (integer)$this->basis->getUserId();
        $q = "SELECT user_id,vote_value,vote_date FROM votes WHERE user_id = {$user_id} ";
        $r = $this->DB->query($q);
        if (is_array($r->fetchArray(SQLITE3_ASSOC))) {
            return true;
        } else {
            return false;
        }
    }
    
    public function joinComment($comment='') {
        if (\is_string($comment) && $comment !== '') {
            $user_id = (integer)$this->basis->getUserId();
            $stmt = $this->DB->prepare("UPDATE votes SET comment = :com WHERE user_id = :uid ");
            $stmt->bindValue(':com', $comment, SQLITE3_TEXT);
            $stmt->bindValue(':uid', $user_id, SQLITE3_INTEGER);
        }
        try {
            $r = $stmt->execute();
            if ($r === false) {
                throw new AppException(__METHOD__." Не добавлен комментарий #{$comment}# в запись с user_id {$user_id} в таблице votes.");
            }
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
        }
    }
    public function inStack($command='') {
        $update_id = (integer)$this->basis->getUpdateId();
        $user_id = (integer)$this->basis->getUserId();
        $chat_id = (integer)$this->basis->getChatId();
        if ($command !== '') {
            $com = $this->DB->escapeString($command);
            $q = "INSERT INTO stack (update_id, user_id, chat_id, command) VALUES ($update_id, $user_id, $chat_id, '{$com}' )";
        }
        try {
            $r = $this->DB->query($q);
            if ($r === false) {
                throw new AppException(__METHOD__." Не добавлена запись команды #{$command}# в таблицу stack.");
            } 
        } catch (AppException $e) {
            $this->basis->toLog($e->getMessage());
            exit;
        }
    }
    
    public function awaitingAction() {
        $r0 = $this->DB->query("SELECT COUNT(*) FROM stack");
        $row0 = $r0->fetchArray(SQLITE3_ASSOC);
        if ($row0["COUNT(*)"] === 0) {
            return false;   // нет записей
        }
        
        $user_id = (integer)$this->basis->getUserId();
        $chat_id = (integer)$this->basis->getChatId();
        $q = "SELECT update_id,user_id, chat_id, command FROM stack WHERE (user_id = $user_id AND chat_id = $chat_id)";
        $r = $this->DB->query($q);
        $row = $r->fetchArray(SQLITE3_ASSOC);
        if (is_array($row)) {
            $update_id = $row["update_id"];
            // сразу убираем эту запись из таблицы
            $this->DB->query("DELETE FROM stack WHERE update_id = $update_id");
            return $row["command"];
        } else {
            return false;
        } 
        
    }
}
