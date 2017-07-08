<?php

class SaguaroSQLi {

	public $connection;
	public $last;

	public function connect() {
		$this->connection = new mysqli(SQLHOST, SQLUSER, SQLPASS);
		$this->selectDatabase(SQLDB);

		if (!$this->connection->ping()) {
			$this->handleError(S_SQLCONF,'', true);
		}
	}

	public function selectDatabase($database) {
		return $this->connection->select_db($database);
	}

	public function query($string, $bind = false, $temp = []) {
		if ($bind) {
            $this->last = $this->connection->prepare($string);
            foreach($bind as $key => $value) $temp[$key] = &$bind[$key];
            call_user_func_array([$this->last, "bind_param"], $bind);
            $this->last->execute();
            $this->last = mysqli_stmt_fetch();
		} else {
			$this->last = $this->connection->query($string);
		}
		return $this->last;
	}

	public function result($string, $bind = false) {
		$this->query($string, $bind);
		return mysqli_fetch_array($this->last)[0];
	}

	public function free_result($destroy) {
        mysqli_free_result($destroy);
		$this->last = null; return;
	}

	private function handleError($message, $query, $fatal = false) {
		echo $message;

		if ($fatal) {
			die();
		}
	}
}