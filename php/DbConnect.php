<?php
/**
 * DbConnect.
 * Class to handle low-level database connection
 *
 * @author Ignacio Nieto Carvajal
 * @link http://digitalleaves.com
 */
 
namespace creamy;

class DbConnect {

    private $conn;

    function __construct() {        
    }

    /**
     * Constructor establishes the database connection
     * @return database connection handler
     */
    function connect() {
        include_once dirname(__FILE__) . '/Config.php';

        // Connecting to mysql database
        $this->conn = new \mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
		$this->conn->set_charset('utf8');
		
        // Check for database connection error
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
        }

        // returing connection resource
        return $this->conn;
    }

}

?>
