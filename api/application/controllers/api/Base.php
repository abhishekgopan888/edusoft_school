<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Base extends CI_Controller
{
    protected $valid_api_keys = [
        'driver-api-secret-key-2026',
        'attender-api-secret-key-2026',
        'student-details-api-secret-key-2026',
        'dashboard-api-secret-key-2026',
        'miki-external-secret-key-2024',
        'system-admin-api-key-2026',
        'MgMgOQNwNg',
        'OQMwOANgNg'
    ];

    protected function _check_auth($allowed_keys = [])
    {
        $api_key = $this->input->get_request_header('X-API-Key', true);
        if (empty($api_key)) {
            $api_key = $this->input->get('api_key');
        }
        if (empty($api_key)) {
            $api_key = $this->input->get('X-API-Key');
        }
        if (empty($api_key)) {
            $auth_header = $this->input->get_request_header('Authorization', true);
            if ($auth_header && preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
                $api_key = trim($matches[1]);
            }
        }
        if (empty($api_key) && isset($_SERVER['HTTP_X_API_KEY'])) {
            $api_key = $_SERVER['HTTP_X_API_KEY'];
        }

        $check_keys = !empty($allowed_keys) ? (array)$allowed_keys : $this->valid_api_keys;
        $check_keys[] = 'system-admin-api-key-2026';

        if (!empty($api_key) && in_array($api_key, $check_keys)) {
            return true;
        }

        if (!empty($api_key)) {
            $session = $this->db->select('expired_at')->from('users_authentication')->where('token', $api_key)->get()->row();
            if ($session && $session->expired_at >= date('Y-m-d H:i:s')) {
                return true;
            }
        }

        $this->_error('Unauthorized - Invalid or missing authentication token / API key', 401);
        $this->output->_display();
        exit;
    }

    protected function _success($message, $data = [], $code = 200)
    {
        $payload = [
            'status' => 'success',
            'message' => $message,
        ];

        if (!empty($data) || is_array($data)) {
            $payload['data'] = $data;
        }

        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function _error($message, $code = 400, $data = [])
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
        ];

        if (!empty($data)) {
            $payload['data'] = $data;
        }

        $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
