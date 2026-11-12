<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class ApiAuth extends Base
{
    public $swagger_meta = [
        'tag' => 'Authentication & API Keys',
        'description' => 'Endpoints for user authentication and API key management.',
        'methods' => [
            'login' => [
                'path' => '/api/auth/login',
                'summary' => 'User Login',
                'description' => 'Authenticates a staff/admin, student, or parent user and returns the login token and user session.',
                'http_method' => 'post',
                'security' => [
                    ['BearerAuth' => []],
                    ['ApiKeyAuth' => []]
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['username', 'password'],
                                'properties' => [
                                    'username' => ['type' => 'string', 'example' => 'admin@gmail.com'],
                                    'password' => ['type' => 'string', 'example' => 'K3lpm@lpm'],
                                    'deviceToken' => ['type' => 'string', 'example' => '', 'description' => 'Optional device token']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Login response with token'],
                    '400' => ['description' => 'Username and password are required']
                ]
            ],
            'keys' => [
                'path' => '/api/auth/keys',
                'summary' => 'Get Authentication API Keys',
                'description' => 'Retrieves API keys used by Driver, Attender, Studentdetails, Dashboard and system admin requests.',
                'http_method' => 'get',
                'security' => [
                    ['BearerAuth' => []],
                    ['ApiKeyAuth' => []]
                ],
                'responses' => [
                    '200' => ['description' => 'API keys retrieved successfully']
                ]
            ]
        ]
    ];
    public function login()
    {
        $this->load->model('auth_model');
        $post = json_decode(file_get_contents('php://input'), true);
        if (empty($post)) {
            $post = $this->input->post();
        }

        $username = isset($post['username']) ? trim($post['username']) : '';
        $password = isset($post['password']) ? $post['password'] : '';
        $deviceToken = isset($post['deviceToken']) ? $post['deviceToken'] : '';

        if (empty($username) || empty($password)) {
            $this->_error('Username and password are required', 400);
            return;
        }

        $response = $this->auth_model->login($username, $password, $deviceToken);

        if (isset($response['status']) && $response['status'] == 1) {
            $this->_success('Login Successful', $response);
        } else {
            $this->_error(isset($response['message']) ? $response['message'] : 'Invalid Username or Password', 401);
        }
    }

    public function keys()
    {
        $keys = [
            'header_name' => 'X-API-Key',
            'api_keys' => [
                'dashboard_api_key' => 'dashboard-api-secret-key-2026',
                'driver_api_key' => 'driver-api-secret-key-2026',
                'attender_api_key' => 'attender-api-secret-key-2026',
                'studentdetails_api_key' => 'student-details-api-secret-key-2026',
                'master_system_api_key' => 'system-admin-api-key-2026'
            ],
            'usage_instructions' => 'Pass the respective API key in the X-API-Key header or Authorization: Bearer <key> header.'
        ];

        $this->_success('API Keys retrieved successfully', $keys);
    }
}
