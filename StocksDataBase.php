<?php
/**
 * interacts with the particular database containing revelent stock data
 *
 * @author Thanh Vo <vothany@hotmail.com>
 */

define('ROOT', dirname(dirname(_FILE_)));

class StocksDataBase {
	/**
	 * connection to the MySQL server
	 * @access protected
	 * @var resource
	 */
	private $connection;
	static $_instance;
	private $config = './config.xml';
	/**
	 * Constructor for the MysqlHandler object
	 * @param string host, string user name, string pswd, string dbname
	 * @return void
	 * @access public
	 */
	private function __construct() {
		$opts = $this->getConfig();
		$this->connection = mysql_connect((string)$opts->host, (string)$opts->user, (string)$opts->password);
		$this->throwOnMysqlError();
		mysql_select_db((string)$opts->db, $this->connection);
	}
	
	private function __clone() {}

	public static function getInstance() {
		if( ! (self::$_instance instanceof self) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	/**
	 * free memory
	 * @return void
	 * @access public
	 */
	public function __destruct() {
		if (isset($this->connection)) {
			@mysql_query('rollback', $this->connection);
			@mysql_close($this->connection);
		}
	}
	/**
	 * executes a SQL query to MySQL server.
	 * @param string SQL statement query
	 * @return mixed
	 * @access public
	 */
	public function execute($sql) {
		@mysql_query($sql, $this->connection);
		$this->throwOnMysqlError();
	}
	/**
	 * selects a MySQL database
	 * @param string SQL statement query
	 * @return mixed
	 * @access public
	 */
	public function select($sql) {
		$result = @mysql_query($sql, $this->connection);
		return new MysqlResult($result);
	}

	public function setConnection($connection) {
		$this->db = $connection;
	}

	protected function throwOnMysqlError() {
		if ($error = mysql_error($this->connection)) {
			throw new Exception($error);
		}
	}

	private function getConfig() {
		if ( ! file_exists($this->config) ) {
			throw new Exception();
		}
		$options = @SimpleXml_load_file( $this->config );
		if ( ! $options instanceof SimpleXMLElement ) {
			throw new Exception();
		}
		return $options;
	}

	public function findReturnsBySymbol($symbol) {
		$query = "SELECT return_value FROM Stock, StockData " .
               "WHERE Stock.stockID = StockData.stockID " .
               "AND Stock.symbol = '$symbol'";
		$result = @mysql_query($query, $this->connection);
		if(!$result) {
			throw new Exception(mysql_error());
		}
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$returns[] = $row['return_value'];
		}
		return $returns;
	}
}
?>