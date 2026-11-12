<?php

defined('BASEPATH') or exit('No direct script access allowed');
class StudentModule_lib
{

    private $CI;
    private $allModules = array();
    protected $modules;
    public $perm_category;
    protected $student;

    public function __construct()
    {
        $this->CI      = &get_instance();
        $this->modules = array();
        $this->CI->load->library('session');
        $this->CI->load->model('module_model');
        self::loadModule(); //Initiate the userroles
    }

    public function loadModule()
    {
        if ($this->CI->session->has_userdata('student')) {
            $this->student = $this->CI->session->userdata('student');
            $this->CI->load->model('module_model');
            $role_name = isset($this->student['role']) ? $this->student['role'] : '';
            if (!empty($role_name)) {
                $this->allModules = $this->CI->module_model->get_userpermission($role_name);
                if (!empty($this->allModules)) {
                    foreach ($this->allModules as $mod_key => $mod_value) {

                        if (isset($mod_value->{$role_name})) {

                            if ($mod_value->{$role_name} == 1) {
                                $this->modules[$mod_value->short_code] = true;
                            } else {
                                $this->modules[$mod_value->short_code] = false;
                            }

                        } else {
                            $this->modules[$mod_value->short_code] = false;
                        }
                    }
                }
            }
        }
    }

    public function hasActive($module = null)
    {
        if (empty($this->modules)) {
            $this->loadModule();
        }
        if (!empty($module) && isset($this->modules[$module]) && $this->modules[$module]) {
            return true;
        }

        return false;
    }

}
