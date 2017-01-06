<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Loader extends CI_Loader {

    function __construct() {
        parent::__construct();
    }

    /**
     * Database Loader
     *
     * @param	mixed	$params		Database configuration options
     * @param	bool	$return 	Whether to return the database object
     * @param	bool	$query_builder	Whether to enable Query Builder
     *					(overrides the configuration setting)
     *
     * @return	object|bool	Database object if $return is set to TRUE,
     *					FALSE on failure, CI_Loader instance in any other case
     */
    public function database($params = '', $return = FALSE, $query_builder = NULL)
    {
        // Grab the super object
        $CI =& get_instance();

        // Do we even need to load the database class?
        if ($return === FALSE && $query_builder === NULL && isset($CI->db) && is_object($CI->db) && ! empty($CI->db->conn_id))
        {
            return FALSE;
        }

        require_once(BASEPATH.'database/DB.php');

        $db =& DB($params, $query_builder);

        $my_driver = config_item('subclass_prefix').'DB_'.$db->dbdriver.'_driver';
        $my_driver_file = APPPATH.'core/'.$my_driver.'.php';

        if (file_exists($my_driver_file))
        {
            require_once($my_driver_file);
            $db_obj = new $my_driver(get_object_vars($db));
            $db=& $db_obj;
        }

        if ($return === TRUE)
        {
            return $db;
        }

        // Initialize the db variable. Needed to prevent
        // reference errors with some configurations
        $CI->db = '';

        // Load the DB class
        $CI->db =$db;
        return $this;
    }
}