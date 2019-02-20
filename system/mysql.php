<?php

/*
* MySQL database magagement classes
*/

// MySQL connection

function good_microtime()
{ 
    list($usec, $sec) = explode(" ",microtime()); 
    return ((float)$usec + (float)$sec); 
} 

function mysql_query_with_log($query, $con)
{
	$fp = @fopen('cache/sql_log', 'a');
	if (!$fp)
		return mysql_query_with_log($query, $con);

	$start = good_microtime();
	$result = mysql_query($query, $con);
	$end = good_microtime();
	@fwrite($fp, sprintf("[%.8f] %s\n", $end-$start, str_replace("\n", '', $query)));
	@fclose($fp);

	return $result;
}

class mysql_con {

	var $error;
	var $con;
	var $host, $database, $login, $password;

	// Class constructor. Used just to create new class instance and
	// set various parameters. No database connection at the moment.

	function mysql_con ($database, $login = '', $password = '', $host = '') {
		$this->con      = false;
		$this->host     = $host;
		$this->database = $database;
		$this->login    = $login;
		$this->password = $password;
	}

	// Connect to database. This method in most cases invokes automatically
	// on first query but you can invoke it manually.

	function connect() {
		$this->con = mysqli_connect($this->host, $this->login, $this->password);
		if (!$this->con) {
			return false;
		}
		if (!@$this->con->select_db($this->database)) {
			return false;
		}
		$this->con->set_charset('utf8');
		return true;
	}

	// Disconnect from a database. Not very userfull. Just for symmetry.

	function disconnect() {
		if ($this->con) {
			return $this->con->close();
		}
		return false;
	}

	function query ($sql) {
		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

	  return $this->con->query($sql);
	}

	function qquery ($sql) {
		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

		$res = $this->con->query($sql);
		if ($res === false) return false;
		$result = $res->fetch_assoc();
		$res->free();

		return $result;
	}

	function aquery ($sql) {
		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

		$res = mysqli_query($sql, $this->con);
		if ($res === false) return false;
		$result = mysqli_fetch_assoc($res);
		mysqli_free_result($res);

		return $result;
	}

	function query_list($sql, $assoc = true) {
		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

    $res = $this->con->query($sql);
    print $this->con->error;
		if ($res === false) return false;
		$result = array();
		switch ($res->field_count)
		{
		case 1:
			while ($r = $res->fetch_array())
				$result[] = $r[0];
			break;
		case 2:
			if ($assoc)
			{
				while ($r = $res->fetch_array())
					$result[$r[0]] = $r[1];
				break;
			}
		// если нужен неассоциативный массив, идем дальше
		default:
			if ($assoc)
				while ($r = $res->fetch_array())
				{
					$key = array_shift($r);
					$result[$key] = $r;
				}
			else
				while ($r = $res->fetch_array())
					$result[] = $r;
		}

		$res->free();
		return $result;
	}

	function property ($sql) {
		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

		$res = $this->con->query($sql);
		if ($res === false) return false;
		$result = $res->fetch_row()[0];
		$res->free();

		return $result;
	}

	function safe_str ($str) {
		if ($this->con === false)
			$this->connect();
		return mysqli_real_escape_string($this->con, $str);
	}

	// Calls on INSERT query. Return last inserted id if any.

	function insert ($query) {

		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

		$res = $this->con->query($query);
		return ($res === false ? false : mysqli_insert_id($this->con));
	}

	// Calls on UPDATE query. Return number of rows affected on query.

	function update ($query) {

		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

		$res = $this->con->query($query);
		return ($res === false ? false : mysqli_affected_rows($this->con));
	}

	// Calls on DELETE query. Return number of rows affected on query.

	function delete ($query) {

		if (!$this->con) {
			$this->connect();
			if (!$this->con) return false;
		}

		$res = $this->con->query($query);
		return ($res === false ? false : mysqli_affected_rows($this->con));
	}

	function last_insert_id () {
		return mysqli_insert_id($this->con);
	}

	function affected_rows () {
		return mysqli_affected_rows($this->con);
	}

}


// MySQL query class

class query{
	// types (1 - INSERT, 2 - UPDATE)
	var $type;

	// Query fields;
	var $fields;

	// Name of table
	var $tablename;

	// condition ("without WHERE");
	var $cond;

	function query($tname, $type = 1, $cond = ""){
		$this->type = $type;
		settype($fields, "array");
		$this->tablename = $tname;
		$this->cond = $cond;
	}

	function push($key, $val){
		if(!$val) return;
		$this->fields[$key]["val"] = $val;
		$this->fields[$key]["dl"] = "'";
	}

	function push_str($key, $val){
		$this->fields[$key]["val"] = $val;
		$this->fields[$key]["dl"] = "'";
	}

	function push_dig($key, $val){
		$this->fields[$key]["val"] = $val;
		$this->fields[$key]["dl"] = "";
	}

	function generate(){
		$sql = (($this->type == 1)?"INSERT INTO ":"UPDATE ").$this->tablename." ".(($this->type == 1)?"(":"SET ");
		$sql2 = "";
		$tag = "";
		while(list($key, $val) = each($this->fields)){
			switch($this->type){
			case 1: // INSERT;
				$sql .= $tag.$key;
				$sql2 .= $tag.$val["dl"].$val["val"].$val["dl"];
			break;
			case 2: // UPDATE;
				$sql .= $tag.$key." = ".$val["dl"].$val["val"].$val["dl"];
			break;
			}
			$tag = ", ";
		}
		switch($this->type){
		case 1: // INSERT;
			return $sql.") VALUES ($sql2)";
		case 2: // UPDATE;
			return $sql.$sql2.((strlen($this->cond))?(" WHERE ".$this->cond):"");
		}
	}

};

?>
