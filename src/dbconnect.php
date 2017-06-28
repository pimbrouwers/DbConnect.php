<?php 
if(file_exists(LIB . 'data/dbconnect.config.php')) {
	include_once (LIB . 'data/dbconnect.config.php');
}
else {
	trigger_error("No database configuration (dbconnect.config.php) was found.");
}

class DbConnectConfig {

	public $db;
	public $u;
	public $p;

	function __construct($db, $u, $p){
		$this->db = $db;
		$this->u = $u; 
		$this->p = $p; 
	}
}

class DbConnect {
	
	private $config;
	private $db;
	private	$params = [];

	function __construct(DbConnectConfig $config = null, array $options = null)
	{		
		$this->setConfig($config);
		
		$this->db = new PDO($this->config->db, 
	    					$this->config->u, 
							$this->config->p);

	    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	public function Execute($query)
	{
		$this->prepare($query);
	}

	public function ExecuteScalar($query)
	{
		$stmt = $this->prepare($query);

		return $stmt->fetchColumn();
	}

	public function FillList($query, $className = null) 
	{
		$rows = [];

		$stmt = $this->prepareFetch($query, $className);

		do {
			while($row = $stmt->fetch())
			{
				array_push($rows, $row);
			}
		} while ($stmt->nextRowset() && $stmt->columnCount());		
		
		return $rows;
	}

	public function FillObject($query, $className = null)
	{
		$stmt = $this->prepareFetch($query, $className);

		do {
			return $stmt->fetch();
		} while ($stmt->nextRowset() && $stmt->columnCount());		
	}

	public function AddParameter ($key, $value){
		if(!substr($key, 0, 1) != ':')
			$key = sprintf(':%s', $key);

		$this->params[$key] = $value;
	}

	public function ClearParameters(){
		$this->params = [];
	}

	private function prepare($query)
	{
		$stmt = $this->db->prepare($query);
		$stmt->execute($this->params);

		return $stmt;
	}

	private function prepareFetch($query, $className = null)
	{
		$stmt = $this->prepare($query);


		if(is_null($className) || !class_exists($className)){
			$stmt->setFetchMode(PDO::FETCH_ASSOC);
		}
		else {
			$stmt->setFetchMode(PDO::FETCH_CLASS, $className);
		}

		return $stmt;
	}

	private function setConfig(array $config = null)
	{
		if(!is_null($config) && is_array($config)){

			$this->config = $config;			
		}
		elseif(defined('DBCONNECT_DB') && defined('DBCONNECT_U') && defined('DBCONNECT_P')) {			

			$this->config = new DbConnectConfig(DBCONNECT_DB, DBCONNECT_U, DBCONNECT_P);

		}		
	}
}
