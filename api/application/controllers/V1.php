<?php
defined('BASEPATH') or exit('No direct script access allowed');

class V1 extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('student_model', 'setting_model'));
        $this->load->library('form_validation');
    }

    private function _check_auth()
    {
        $api_key = $this->input->get_request_header('X-API-Key', true);
        if ($api_key !== 'miki-external-secret-key-2024') {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
    }

    public function register_student()
    {
        $this->_check_auth();

        $method = $this->input->server('REQUEST_METHOD');
        if ($method != 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            return;
        }

        $params = json_decode(file_get_contents('php://input'), true);

        // Required fields
        $name = isset($params['name']) ? $params['name'] : '';
        $student_class = isset($params['student_class']) ? $params['student_class'] : '';
        $division = isset($params['division']) ? $params['division'] : '';
        $address = isset($params['address']) ? $params['address'] : '';
        $dob = isset($params['dob']) ? $params['dob'] : '';
        $phone_number = isset($params['student_phone']) ? $params['student_phone'] : (isset($params['phone_number']) ? $params['phone_number'] : (isset($params['mobileno']) ? $params['mobileno'] : (isset($params['mobile_number']) ? $params['mobile_number'] : (isset($params['phone']) ? $params['phone'] : ''))));
        $guardian_name = isset($params['guardian_name']) ? $params['guardian_name'] : '';
        $guardian_phone = isset($params['guardian_phone']) ? $params['guardian_phone'] : '';
        $link = isset($params['link']) ? $params['link'] : '';

        if(empty($name) || empty($student_class) || empty($division)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Required fields missing']);
            return;
        }

        // Find class_id by class name
        $this->db->select('id');
        $this->db->where('class', $student_class);
        $class_query = $this->db->get('classes')->row();
        if(!$class_query) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Class not found']);
            return;
        }
        $class_id = $class_query->id;

        // Find section_id by division name
        $this->db->select('id');
        $this->db->where('section', $division);
        $section_query = $this->db->get('sections')->row();
        if(!$section_query) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Division/Section not found']);
            return;
        }
        $section_id = $section_query->id;

        // Split name into firstname and lastname
        $parts = explode(' ', $name);
        $lastname = array_pop($parts);
        $firstname = implode(' ', $parts);
        if(empty($firstname)) {
            $firstname = $lastname;
            $lastname = '';
        }

        $session = $this->setting_model->getCurrentSession();

        $student_data = array(
            'firstname' => $firstname,
            'lastname' => $lastname,
            'dob' => $dob,
            'mobileno' => $phone_number,
            'current_address' => $address,
            'guardian_name' => $guardian_name,
            'guardian_phone' => $guardian_phone,
            'is_active' => 'yes',
            // link can be stored in note or a custom field if required, putting in note for now
            'note' => 'Link: ' . $link 
        );
        
        $insert_id = $this->student_model->add($student_data);

        if($insert_id) {
            $session_data = array(
                'student_id' => $insert_id,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'session_id' => $session
            );
            $this->student_model->add_student_session($session_data);

            echo json_encode(['status' => 'success', 'message' => 'Student registered successfully', 'student_id' => $insert_id]);
        } else {
            $db_error = $this->db->error();
            http_response_code(500);
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to register student',
                'db_error' => $db_error
            ]);
        }
    }

    public function login() {
        
    }
}
