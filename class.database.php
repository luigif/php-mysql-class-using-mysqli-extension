<?php
/**
 * MySQLi Database Class
 *
 * @category  Database Access
 * @package   Database
 * @author    Vivek V <vivekv@vivekv.com>
 * @copyright Copyright (c) 2012
 * @license   http://opensource.org/licenses/gpl-3.0.html GNU Public License
 * @version   1.0.11
 **/

class Database
{

	protected static $_instance;

	/**
	 * MySQLi instance
	 */
	protected $_mysqli;

	/**
	 * The SQL Query
	 */
	var $_query;

	/**
	 * Affected rows after a select/update/delete query
	 */
	var $affected_rows = 0;
	/**
	 * Limit and offset
	 */
	var $_limit;
	var $_offset;
	var $_result;
	var $error = '';
	var $debug = TRUE;

	/**
	 * The table name used as FROM
	 */
	var $_fromTable;

	/**
	 * Arrays
	 */
	var $array_where = array();
	var $array_select = array();
	var $array_like = array();

	public function __construct($host, $username, $password, $db, $port = NULL)
	{
		// Get the default port number if not given.
		if ($port == NULL)
			$port = ini_get('mysqli.default_port');
		$this -> _mysqli = @new mysqli($host, $username, $password, $db, $port);
		if (!$this -> _mysqli)
			die($this -> oops('There was a problem connecting to the database'));
		$this -> _mysqli -> set_charset('utf8');
		self::$_instance = $this;
	}

	/**
	 * Close connection
	 */
	public function __destruct()
	{
		@$this -> _mysqli -> close();
	}

	/**
	 * Get the instance of the class.
	 *
	 * @uses $db = Database:getInstance();
	 *
	 * @return object Returns the current instance
	 */

	public static function getInstance()
	{
		return self::$_instance;
	}

	/**
	 * Sets a limit and offset clause. Offset is optional
	 *
	 * @uses $db->limit(0,12); // Will list the first 12 rows
	 * @uses $db->limit(1); // Will list the first 1 row.
	 */

	public function limit($limit, $offset = null)
	{
		if ($limit > 0)
			$this -> _limit = (int)$limit;
		if ($offset > 0)
			$this -> _offset = (int)$offset;

		return $this;
	}

	/**
	 * Executes raw sql query.
	 *
	 * @uses $db->query("SELECT * FROM table");
	 * @return object Returns the object. Use $db->fetch() to get the results array
	 */
	public function query($query)
	{
		$this -> _query = filter_var($query, FILTER_SANITIZE_STRING);
		return $this;
	}

	/**
	 * Executes a raw query. This is same as query() function but it returns only the first row as result.
	 * @uses $db->query_first("SELECT * FROM table"); // Will product "SELECT * FROM table LIMIT 1"
	 * @return object Returns the object. Use $db->fetch() to get the results array
	 */

	public function query_first($query)
	{
		$this -> _query = filter_var($query, FILTER_SANITIZE_STRING);
		$this -> limit(1);
		return $this;
	}

	/**
	 * Sets the WHERE clause
	 * Multiple instances are joined by AND
	 * @param $key array Can either be string or array.
	 * @param $value string Optional. Need only if $key is a string..
	 *
	 */

	public function where($key, $value = null)
	{
		return $this -> _where($key, $value, 'AND ');
	}

	/**
	 * Sets the OR WHERE clause
	 * This function is identical to where() function except that multiple instances are joined by OR
	 * @param $key array Can either be string or array.
	 * @param $value string Optional. Need only if $key is a string..
	 *
	 */

	public function or_where($key, $value = null)
	{
		return $this -> _where($key, $value, 'OR ');
	}

	/**
	 * Save WHERE as array for building the query
	 */

	protected function _where($key, $value, $type = 'AND ')
	{
		/**
		 * If user provided custom where() clauses then we do not need to process it
		 */

		if (!is_array($key) AND is_null($value))
		{
			$this -> array_where[0] = $key;
			return $this;
		}
		/**
		 * If the WHERE key is an array then we process the array
		 */

		if (is_array($key) AND is_null($value))
		{
			foreach ($key as $wkey => $wval)
			{
				$this -> _where($wkey, $wval, $type);
			}
		}
		else
		{
			$prefix = (count($this -> array_where) == 0) ? '' : $type;
			$value = $this -> escape($value);
			$this -> array_where[] = "$prefix$key = '$value'";
		}
		return $this;

	}

	/**
	 * The SELECT portion of the query.
	 *
	 * @param $select Can either be a string or an array containing the columns to be selected. If none provided, * will be assigned by default
	 * @uses $db->select("id, email, password") ;
	 * @uses $db->select(array('id', 'email', 'password')) ;
	 */

	public function select($select = '*')
	{
		if (is_string($select))
		{
			$select = explode(',', $select);
		}
		foreach ($select as $val)
		{
			$val = trim($val);

			if ($val != '')
			{
				$this -> array_select[] = $val;

			}
		}
		return $this;
	}

	/**
	 * Sets the FROM portion of the query.
	 *
	 * @param $table string Name of the table.
	 */
	public function from($table)
	{
		$this -> _fromTable = $table;
		return $this;
	}

	/**
	 * Build the query string
	 */

	private function prepare()
	{

		/**
		 * We need to process $this->_query only if the user has not given a _query string.
		 */

		if (!isset($this -> _query))
		{
			// Write the "SELECT" portion of the query
			if (count($this -> array_select > 0))
			{
				$this -> _query = "SELECT ";
				if ($this -> array_select == '*' OR count($this -> array_select) == 0)
				{
					$this -> _query .= '*';
				}
				else
				{
					$this -> _query .= implode(",", $this -> array_select);
				}
			}

			// Write the "FROM" portion of the query
			if (isset($this -> _fromTable))
				$this -> _query .= " FROM $this->_fromTable ";

			// Write the "WHERE" portion of the query
			if (count($this -> array_where) > 0)
			{
				$this -> _query .= "\nWHERE ";
				$this -> _query .= implode("\n", $this -> array_where);
			}

		}
		// Write the "LIMIT" portion of the query
		if ($this -> _limit > 0)
		{
			$this -> _query .= ' LIMIT ' . $this -> _limit;
		}

		// Write the "OFFSET" portion of the query
		if (isset($this -> _offset))
		{
			$this -> _query .= ' ' . $this -> _offset;
		}

		return $this;

	}

	/**
	 * Execute the query. This function returns the object. For getting the result of the execution use fetch();
	 */

	public function execute()
	{
		$this -> prepare();
		$this -> _result = $this -> _mysqli -> query($this -> _query);
		if (!$this -> _result)
			$this -> oops();

		$this -> affected_rows = $this -> _mysqli -> affected_rows;
		return $this;
	}

	/**
	 * Fetches the result of an execution. Must be called only after calling execute()
	 *
	 * @return array Returns an Associate Array of results.
	 */
	public function fetch()
	{
		if (is_object($this -> _result))
		{
			if ($this -> _limit == 1)
				return $this -> _result -> fetch_array(MYSQLI_ASSOC);
			else
				return $this -> _result -> fetch_all(MYSQLI_ASSOC);
		}
		else
		{
			$this -> oops('Unable to perform fetch()');
		}

	}

	/**
	 * This function returns the last build query. Useful for troubleshooting the code.
	 *
	 * @return string Last query, exmaple : "SELECT * FROM table"
	 */
	public function last_query()
	{
		return $this -> _query;
	}

	/**
	 * Remove dangerous input
	 *
	 * @param string $string The string needs to be sanitized
	 * @return string Returns the sanitized string
	 */
	public function escape($string)
	{
		if (get_magic_quotes_runtime())
			$string = stripslashes($string);
		return @$this -> _mysqli -> real_escape_string($string);
	}

	/**
	 * Inserts data into table.
	 *
	 * @param string $table Name of the table
	 * @param array $data The array which contains the coulumn name and values to be inserted.
	 *
	 * @return integer Returns the inserted id. ( mysqli->insert_id)
	 */

	public function insert($table, $data)
	{
		foreach ($data as $key => $value)
		{
			$keys[] = $key;
			if (strpos($value, '()') == true)
				$values[] = "$value";
			else
				$values[] = "'$value'";
		}
		$this -> _query = "INSERT INTO " . $table . " (" . implode(', ', $keys) . ") VALUES (" . implode(', ', $values) . ");";
		$this -> execute();
		return $this -> _mysqli -> insert_id;
	}

	/**
	 * Update query. Use where() if needed.
	 *
	 * @param $table string Name of the table
	 * @param $data string Array containing the data to be updated
	 *
	 */

	public function update($table, $data)
	{
		foreach ($data as $key => $val)
		{
			if (strpos($val, '()') == true)
				$valstr[] = $key . " = $val";
			else
				$valstr[] = $key . " = '$val'";
		}

		$this -> _query = "UPDATE " . $table . " SET " . implode(', ', $valstr);
		if (count($this -> array_where) > 0)
		{
			$this -> _query .= " WHERE ";
			$this -> _query .= implode(" ", $this -> array_where);
		}
		$this -> execute();
		return $this;
	}

	/**
	 * Permits to write the LIKE portion of the query using the connector AND
	 *
	 * @param $title string or array Can either be a string or array. This is the title portion of LIKE
	 * @param $match string Required only if $title is a string. This is the matching portion
	 * @param $place string This enables you to control where the wildcard (%) is placed. Options are "both", "before", and "after". Default is "both"
	 */

	public function like($title, $match = null, $place = 'both')
	{
		$this -> _like($title, $match, $place, 'AND ');
		return $this;

	}

	/**
	 * Permits to write the LIKE portion of the query using the connector OR
	 *
	 * @param $title string or array Can either be a string or array. This is the title portion of LIKE
	 * @param $match string Required only if $title is a string. This is the matching portion
	 * @param $place string This enables you to control where the wildcard (%) is placed. Options are "both", "before", and "after". Default is "both"
	 */

	public function or_like($title, $match = null, $place = 'both')
	{
		$this -> _like($title, $match, $place, 'OR ');
		return $this;
	}

	/**
	 * Builds $array_like
	 */

	protected function _like($title, $match, $place = 'both', $type)
	{
		// If $title is an array, we need to process it

		if (is_array($title))
		{
			foreach ($title as $key => $value)
			{
				$this -> _like($key, $value, $place, $type);
			}
		}
		else
		{
			$prefix = (count($this -> array_like) == 0) ? '' : $type;
			$match = $this -> escape($match);

			if ($place == 'both')
				$this -> array_like[] = "$prefix$title LIKE '%$match%'";
			if ($place == 'before')
				$this -> array_like[] = "$prefix$title LIKE '%$match'";
			if ($place == 'after')
				$this -> array_like[] = "$prefix$title LIKE '$match%'";
			if ($place == 'none')
				$this -> array_like[] = "$prefix$title LIKE '$match'";

			return $this;

		}

	}

	private function oops($msg = null)
	{
		// If debug is not enabled, do not proceed
		if (!$this -> debug)
			return;

		if (!$msg)
		{
			$msg = 'MySQL Error has occured';
		}
		$this -> error = mysqli_error($this -> _mysqli);

		echo '<table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">
		<tr><th colspan=2>Database Error</th></tr>
		<tr><td align="right" valign="top">Message:</td><td> ' . $msg . '</td></tr> ';

		if (!empty($this -> error))
			echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>' . $this -> error . '</td></tr>';
		echo '<tr><td align="right">Date:</td><td>' . date("l, F j, Y \a\\t g:i:s A") . '</td></tr>';
		echo '<tr><td align="right">Query:</td><td>' . $this -> _query . '</td></tr>';
		echo '</table>';

		unset($this -> error);

		//die();
	}

}