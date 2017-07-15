<?php

class SaguaroPDO {

	public $connection;
	public $last;

	public function connect() {
        $sqlhost = SQLHOST; $database = SQLDB;
		$this->connection = new PDO("mysql:host=$sqlhost;dbname=$database", SQLUSER, SQLPASS);

		/*if (!$this->connection->ping()) {
			$this->handleError(S_SQLCONF,'', true);
		}*/
	}

	public function selectDatabase($database) {
		return $this->query("USE :database", [":database"=>$database]);
	}

	public function query($string, $bind = false) {
        $this->last = $this->connection->prepare($string);
        if ($bind) {
            foreach ($bind as $key => $value) {
                $this->last->bindValue($key, $value);
            }
		}
		return $this->last->execute();
	}

	public function result($string, $bind = false) {
		$this->query($string, $bind);
        return $this->last->fetchColumn();
	}
    
    public function num_rows($string, $bind = false) {
        $this->query($string, $bind);
        return $this->last->rowCount();
    }
    
    public function fetch_array($string, $bind = false) {
        die("UPDATE THIS STRING DUMMY " . $string);
    }
    
    public function fetch_assoc($string, $bind = false) {
        $this->query($string, $bind);
        return $this->last->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function escape_string($string) {
        return $this->connection->quote($string);
    }

	public function free_result($destroy) {
        $this->last->closeCursor();
		$this->last = null; return;
	}

	private function handleError($message, $query, $fatal = false) {
		echo $message;

		if ($fatal) {
			die();
		}
	}
}