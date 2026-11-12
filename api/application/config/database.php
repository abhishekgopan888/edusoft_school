<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$parent_db = FCPATH . '../application/config/database.php';
if (file_exists($parent_db)) {
    require_once($parent_db);
} else {
    $active_group = 'default';
    $query_builder = TRUE;

    $db['default'] = array(
        'dsn' => '',
        'hostname' => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'school_edusoft',
        'dbdriver' => 'mysqli',
        'dbprefix' => '',
        'pconnect' => FALSE,
        'db_debug' => FALSE,
        'cache_on' => FALSE,
        'cachedir' => '',
        'char_set' => 'utf8',
        'dbcollat' => 'utf8_general_ci',
        'swap_pre' => '',
        'encrypt' => FALSE,
        'compress' => FALSE,
        'stricton' => FALSE,
        'failover' => array(),
        'save_queries' => TRUE
    );
}