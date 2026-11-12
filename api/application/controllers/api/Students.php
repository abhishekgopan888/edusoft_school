<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Students extends Base
{
    public $swagger_meta = [
        'tag' => 'Students',
        'description' => 'Endpoints for student management.',
        'methods' => [
            'register' => [
                'path' => '/v1/register-student',
                'summary' => 'Register a new student',
                'description' => 'Registers a new student into the school management system.',
                'http_method' => 'post',
                'security' => [['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'student_class', 'division'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Sophia Carter'],
                                    'student_class' => ['type' => 'string', 'example' => '10'],
                                    'division' => ['type' => 'string', 'example' => 'A'],
                                    'address' => ['type' => 'string', 'example' => '123 Innovation Drive, Tech City'],
                                    'dob' => ['type' => 'string', 'format' => 'date', 'example' => '2008-04-12'],
                                    'phone_number' => ['type' => 'string', 'example' => '9876543210', 'description' => 'Student phone / mobile number'],
                                    'mobileno' => ['type' => 'string', 'example' => '9876543210', 'description' => 'Alias for student mobile number'],
                                    'guardian_name' => ['type' => 'string', 'example' => 'Michael Carter'],
                                    'guardian_phone' => ['type' => 'string', 'example' => '9876543210'],
                                    'link' => ['type' => 'string', 'format' => 'uri', 'example' => 'https://demo-academy.onedusoft.in/site/login']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Student registered successfully'],
                    '400' => ['description' => 'Required fields missing'],
                    '401' => ['description' => 'Unauthorized - Invalid X-API-Key'],
                    '404' => ['description' => 'Class or Division/Section not found'],
                    '500' => ['description' => 'Internal Server Error']
                ]
            ],
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('student_model', 'setting_model'));
    }

    private function _check_auth()
    {
        $api_key = $this->input->get_request_header('X-API-Key', true);
        if ($api_key !== 'miki-external-secret-key-2024') {
            $this->_error('Unauthorized', 401);
            exit;
        }
    }

    public function register()
    {
        $this->_check_auth();

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

        if (empty($name) || empty($student_class) || empty($division)) {
            $this->_error('Required fields missing (name, student_class, division)', 400);
            return;
        }

        // Find class_id by class name
        $this->db->select('id');
        $this->db->where('class', $student_class);
        $class_query = $this->db->get('classes')->row();
        if (!$class_query) {
            $this->_error('Class not found', 404);
            return;
        }
        $class_id = $class_query->id;

        // Find section_id by division name
        $this->db->select('id');
        $this->db->where('section', $division);
        $section_query = $this->db->get('sections')->row();
        if (!$section_query) {
            $this->_error('Division/Section not found', 404);
            return;
        }
        $section_id = $section_query->id;

        // Split name into firstname and lastname
        $parts = explode(' ', $name);
        $lastname = array_pop($parts);
        $firstname = implode(' ', $parts);
        if (empty($firstname)) {
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
            'note' => 'Link: ' . $link 
        );

        // Save student
        $insert_id = $this->student_model->add($student_data);

        if ($insert_id) {
            $session_data = array(
                'student_id' => $insert_id,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'session_id' => $session
            );
            $this->student_model->add_student_session($session_data);

            $this->_success('Student registered successfully', ['student_id' => $insert_id]);
        } else {
            $this->_error('Failed to register student', 500);
        }
    }

    public function analysis()
    {
        $this->_check_auth();

        $student_id = $this->input->get('student_id');
        if (empty($student_id)) {
            $params = json_decode(file_get_contents('php://input'), true);
            $student_id = isset($params['student_id']) ? $params['student_id'] : $this->input->post('student_id');
        }

        if (empty($student_id)) {
            $this->_error('Student ID is required', 400);
            return;
        }

        $student = $this->student_model->get($student_id);
        if (empty($student)) {
            $this->_error('Student not found', 404);
            return;
        }

        $this->load->model(array('setting_model'));
        $sch_setting = $this->setting_model->getSetting();

        $this->_success('Student analysis data retrieved successfully', [
            'student_id' => $student_id,
            'student' => $student,
            'sch_setting' => $sch_setting
        ]);
    }
}
