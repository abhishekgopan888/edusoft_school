<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Module_lib
{

    private $CI;
    private $allModules = [];
    protected $modules;
    public $perm_category;

    function __construct()
    {
        $this->CI = &get_instance();
        $this->modules = array();
        $this->CI->load->model('module_model');
        self::loadModule(); //Initiate the userroles
    }

    function loadModule()
    {
        $this->CI->load->model('module_model');
        $this->allModules = $this->CI->module_model->get();

        if (!empty($this->allModules)) {
            foreach ($this->allModules as $mod_key => $mod_value) {

                if ($mod_value->is_active == 1) {
                    $this->modules[$mod_value->short_code] = true;
                } else {

                    $this->modules[$mod_value->short_code] = false;
                }
            }
        }
    }

    function hasActive($module = null)
    {
        if (empty($this->modules)) {
            $this->loadModule();
        }
        if (!empty($module) && isset($this->modules[$module]) && $this->modules[$module]) {
            return true;
        }

        return false;
    }

    function hasModule($module_shortcode)
    {
        $this->CI->load->model('module_model');
        $count = $this->CI->module_model->hasModule($module_shortcode);

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }
}
