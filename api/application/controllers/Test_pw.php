<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Test_pw extends CI_Controller
{
    public function index()
    {
        $this->load->model('student_model');
        
        $student_data = array(
            'firstname' => 'TestFirst',
            'lastname' => 'TestLast',
            'dob' => '2008-04-12',
            'current_address' => 'Test Address',
            'guardian_name' => 'Test Guardian',
            'guardian_phone' => '9876543210',
            'is_active' => 'yes'
        );
        
        $res = $this->db->insert('students', $student_data);
        echo "Insert result: " . ($res ? "TRUE" : "FALSE") . "<br>";
        echo "Insert ID: " . $this->db->insert_id() . "<br>";
        print_r($this->db->error());
        echo "<br>Last Query: " . $this->db->last_query() . "<br>";
    }
}
