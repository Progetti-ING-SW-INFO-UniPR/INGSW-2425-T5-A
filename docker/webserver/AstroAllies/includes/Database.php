<?php
class Database
{
    private $host;
    private $username;
    private $password;
    private $dbname;
    private $connection;
	public readonly string $pepe;

    public function __construct()
    {
        $this->host = getenv('MYSQL_HOST');
        $this->username = getenv('MYSQL_USER');
        $this->password = getenv('MYSQL_PASSWORD');
        $this->dbname = getenv('MYSQL_DATABASE');
		// $this->pepe = getenv('PSWD_PEPE');
		$this->pepe = "2ry89^";


        $this->connect();
    }

    private function connect()
    {
        // echo "Tentativo di connessione a {$this->host}, {$this->username}, {$this->dbname}...<br>";
        $this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbname);

        if ($this->connection->connect_error) {
            die("Connessione fallita: " . $this->connection->connect_error);
        }
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->connection->prepare($sql);

        if ($params) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

		if($result)
        	return new DBResult($result);
		return $result;
    }

    public function close()
    {
        $this->connection->close();
    }
}

class DBResult{
	private $fields;
	private $mysqli;
	public readonly int $num_rows;
	
	public function __construct(mysqli_result $mysqli_res) {
		$this->mysqli = $mysqli_res;
		$this->num_rows = $this->mysqli->num_rows;
		$this->fields = $this->mysqli->fetch_fields();
		for ($i = 0; $i < count($this->fields); $i++) {
			$this->fields[$i] = $this->fields[$i]->name;
		}
	}

	/**
     * @return array|null|false
     */
	public function fetch_row() : array {
		$row = $this->mysqli->fetch_row();
		if($row == null or $row == false) return $row;
		return array_combine($this->fields, $row);
	}

	public function get_mysqli_result() {
		return $this->mysqli;
	}
}
?>