<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
    }

    public function login()
    {
        $method = $this->input->server('REQUEST_METHOD');

        if ($method != 'POST') {
            $this->output->set_content_type('application/json')
                ->set_status_header(405)
                ->set_output(json_encode(['status' => 0, 'message' => 'Method Not Allowed.']));
            return;
        }

        $params   = json_decode(file_get_contents('php://input'), true);
        $username = isset($params['username']) ? trim($params['username']) : '';
        $password = isset($params['password']) ? $params['password'] : '';
        $app_key  = isset($params['deviceToken']) ? $params['deviceToken'] : '';

        if (empty($username) || empty($password)) {
            $this->output->set_content_type('application/json')
                ->set_status_header(400)
                ->set_output(json_encode(['status' => 0, 'message' => 'Username and password are required.']));
            return;
        }

        // Call login — it checks staff first (no Client-Service header needed),
        // then falls back to student login.
        $response = $this->auth_model->login($username, $password, $app_key);

        $this->output->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    public function keys()
    {
        $keys = [
            'header_name' => 'X-API-Key',
            'api_keys' => [
                'driver_api_key' => 'driver-api-secret-key-2026',
                'attender_api_key' => 'attender-api-secret-key-2026',
                'studentdetails_api_key' => 'student-details-api-secret-key-2026',
                'master_system_api_key' => 'system-admin-api-key-2026'
            ],
            'usage' => 'Pass the key in the X-API-Key header for all Driver, Attender and Studentdetails endpoints.'
        ];
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'data' => $keys], JSON_PRETTY_PRINT));
    }

}
