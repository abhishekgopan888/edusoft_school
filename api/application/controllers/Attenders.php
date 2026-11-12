<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Attenders API Controller
 * API Key: attender-api-secret-key-2026
 */
class Attenders extends CI_Controller
{
    private $valid_keys = [
        'attender-api-secret-key-2026',
        'system-admin-api-key-2026'
    ];

    private function _check_auth()
    {
        $api_key = $this->input->get_request_header('X-API-Key', true);
        if (empty($api_key)) {
            $api_key = $this->input->get('api_key');
        }
        if (empty($api_key)) {
            $api_key = $this->input->get('X-API-Key');
        }
        if (empty($api_key)) {
            $auth = $this->input->get_request_header('Authorization', true);
            if ($auth && preg_match('/Bearer\s+(.*)$/i', $auth, $m)) {
                $api_key = trim($m[1]);
            }
        }
        if (empty($api_key) && isset($_SERVER['HTTP_X_API_KEY'])) {
            $api_key = $_SERVER['HTTP_X_API_KEY'];
        }

        if (!empty($api_key) && in_array($api_key, $this->valid_keys)) {
            return true;
        }
        if (!empty($api_key)) {
            $session = $this->db->select('expired_at')->from('users_authentication')->where('token', $api_key)->get()->row();
            if ($session && $session->expired_at >= date('Y-m-d H:i:s')) {
                return true;
            }
        }
        $this->output->set_status_header(401)->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized - Invalid or missing X-API-Key']));
        exit;
    }

    private function _success($message, $data = [], $code = 200)
    {
        $payload = ['status' => 'success', 'message' => $message];
        if (!empty($data) || is_array($data)) {
            $payload['data'] = $data;
        }
        $this->output->set_status_header($code)->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function _error($message, $code = 400)
    {
        $this->output->set_status_header($code)->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'error', 'message' => $message]));
    }

    public function index()
    {
        $this->_check_auth();
        $attenders = $this->db->order_by('id', 'desc')->get('attenders')->result_array();
        $this->_success('Attenders list retrieved successfully', $attenders);
    }

    public function details()
    {
        $this->_check_auth();
        $id = $this->input->get('id');
        if (empty($id)) { $this->_error('Attender ID is required', 400); return; }
        $attender = $this->db->get_where('attenders', ['id' => $id])->row_array();
        if (!$attender) { $this->_error('Attender not found', 404); return; }
        $this->_success('Attender details retrieved successfully', $attender);
    }

    public function add()
    {
        $this->_check_auth();
        $input = json_decode(file_get_contents('php://input'), true) ?: $this->input->post();
        $attender_name = isset($input['attender_name']) ? trim($input['attender_name']) : '';
        if (empty($attender_name)) {
            $this->_error('Required field missing: attender_name', 400); return;
        }
        $data = [
            'attender_name' => $attender_name,
            'age' => isset($input['age']) ? $input['age'] : null,
            'address' => isset($input['address']) ? $input['address'] : '',
            'status' => isset($input['status']) ? $input['status'] : 'Active',
            'note' => isset($input['note']) ? $input['note'] : ''
        ];
        $this->db->insert('attenders', $data);
        $id = $this->db->insert_id();
        if ($id) { $this->_success('Attender created successfully', ['id' => $id], 201); }
        else { $this->_error('Failed to create attender', 500); }
    }

    public function edit()
    {
        $this->_check_auth();
        $input = json_decode(file_get_contents('php://input'), true) ?: $this->input->post();
        $id = isset($input['id']) ? $input['id'] : null;
        if (empty($id)) { $this->_error('Attender ID is required', 400); return; }
        if (!$this->db->get_where('attenders', ['id' => $id])->row_array()) {
            $this->_error('Attender not found', 404); return;
        }
        $data = [];
        foreach (['attender_name', 'age', 'address', 'status', 'note'] as $f) {
            if (isset($input[$f])) $data[$f] = $input[$f];
        }
        if (!empty($data)) $this->db->where('id', $id)->update('attenders', $data);
        $this->_success('Attender updated successfully', $this->db->get_where('attenders', ['id' => $id])->row_array());
    }

    public function delete()
    {
        $this->_check_auth();
        $id = $this->input->get('id');
        if (empty($id)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? $input['id'] : null;
        }
        if (empty($id)) { $this->_error('Attender ID is required', 400); return; }
        if (!$this->db->get_where('attenders', ['id' => $id])->row_array()) {
            $this->_error('Attender not found', 404); return;
        }
        $this->db->where('id', $id)->delete('attenders');
        $this->_success('Attender deleted successfully', ['id' => $id]);
    }
}
