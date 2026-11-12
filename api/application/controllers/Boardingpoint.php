<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Boardingpoint API Controller
 * Secret Keys: attender-api-secret-key-2026, MgMgOQNwNg, system-admin-api-key-2026
 */
class Boardingpoint extends CI_Controller
{
    private $valid_keys = [
        'attender-api-secret-key-2026',
        'system-admin-api-key-2026',
        'MgMgOQNwNg',
        'OQMwOANgNg'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('boardingpoint_model');
    }

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
            if ($session && strtotime($session->expired_at) >= time()) {
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

        $filters = array_filter([
            'status'     => $this->input->get('status'),
            'busid'      => $this->input->get('busid'),
            'attenderid' => $this->input->get('attenderid'),
            'studentid'  => $this->input->get('studentid'),
            'date'       => $this->input->get('date'),
            'search'     => $this->input->get('search')
        ], function($v) { return $v !== null && $v !== ''; });

        $records = $this->boardingpoint_model->get(null, $filters);
        $this->_success('Boarding records retrieved successfully', $records);
    }

    public function details()
    {
        $this->_check_auth();
        $id = $this->input->get('id');
        if (empty($id)) {
            $this->_error('Boarding record ID is required', 400);
            return;
        }

        $record = $this->boardingpoint_model->get($id);
        if (!$record) {
            $this->_error('Boarding record not found', 404);
            return;
        }

        $this->_success('Boarding record details retrieved successfully', $record);
    }

    public function add()
    {
        $this->_check_auth();
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $studentid  = isset($input['studentid']) ? $input['studentid'] : null;
        $busid      = isset($input['busid']) ? $input['busid'] : null;
        $attenderid = isset($input['attenderid']) ? $input['attenderid'] : null;
        $status     = isset($input['status']) ? strtolower(trim($input['status'])) : 'pending';

        if (empty($studentid) || empty($attenderid)) {
            $this->_error('Required fields missing: studentid, attenderid', 400);
            return;
        }

        // If busid is empty, null, or 0, attempt to look up student assigned vehicle route
        if (empty($busid)) {
            $this->db->select('vehicle_routes.vehicle_id')
                ->from('student_session')
                ->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id', 'left')
                ->where('student_session.student_id', $studentid)
                ->order_by('student_session.id', 'DESC')
                ->limit(1);
            $veh_row = $this->db->get()->row_array();
            if ($veh_row && !empty($veh_row['vehicle_id'])) {
                $busid = (int)$veh_row['vehicle_id'];
            } else {
                $busid = 0;
            }
        } else {
            $busid = (int)$busid;
        }

        $allowed_statuses = ['pending', 'boarded', 'deboarded', 'dropped'];
        if (!in_array($status, $allowed_statuses)) {
            $this->_error('Invalid status value. Allowed values: pending, boarded, deboarded, dropped', 400);
            return;
        }

        $data = [
            'studentid'  => (int)$studentid,
            'busid'      => (int)$busid,
            'attenderid' => (int)$attenderid,
            'status'     => $status
        ];

        $insert_id = $this->boardingpoint_model->add($data);

        if ($insert_id) {
            // Also keep students.boarding_status in sync
            $this->db->where('id', $studentid)->update('students', ['boarding_status' => ucfirst($status)]);

            $this->_success('Boarding record added successfully', ['id' => $insert_id], 201);
        } else {
            $this->_error('Failed to add boarding record', 500);
        }
    }

    public function update_status()
    {
        $this->_check_auth();
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $id        = isset($input['id']) ? $input['id'] : null;
        $studentid = isset($input['studentid']) ? $input['studentid'] : null;
        $status    = isset($input['status']) ? strtolower(trim($input['status'])) : null;

        if (empty($status)) {
            $this->_error('Status is required', 400);
            return;
        }

        $allowed_statuses = ['pending', 'boarded', 'deboarded', 'dropped'];
        if (!in_array($status, $allowed_statuses)) {
            $this->_error('Invalid status value. Allowed values: pending, boarded, deboarded, dropped', 400);
            return;
        }

        if (!empty($id)) {
            $record = $this->boardingpoint_model->get($id);
        } elseif (!empty($studentid)) {
            $records = $this->boardingpoint_model->get(null, ['studentid' => $studentid]);
            $record  = !empty($records) ? $records[0] : null;
        } else {
            $this->_error('Either Boarding record ID or studentid is required', 400);
            return;
        }

        if (!$record) {
            $this->_error('Boarding record not found', 404);
            return;
        }

        $update_data = [
            'id'     => $record['id'],
            'status' => $status
        ];
        if (isset($input['busid'])) {
            $update_data['busid'] = !empty($input['busid']) ? (int)$input['busid'] : 0;
        }
        if (isset($input['attenderid'])) {
            $update_data['attenderid'] = !empty($input['attenderid']) ? (int)$input['attenderid'] : 0;
        }

        $this->boardingpoint_model->add($update_data);

        // Also keep students.boarding_status in sync
        $sid = !empty($record['studentid']) ? $record['studentid'] : $studentid;
        if (!empty($sid)) {
            $this->db->where('id', $sid)->update('students', ['boarding_status' => ucfirst($status)]);
        }

        $updated_record = $this->boardingpoint_model->get($record['id']);

        $this->_success('Boarding status updated successfully', $updated_record);
    }

    public function delete()
    {
        $this->_check_auth();
        $id = $this->input->get('id');
        if (empty($id)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? $input['id'] : null;
        }

        if (empty($id)) {
            $this->_error('Boarding record ID is required', 400);
            return;
        }

        $record = $this->boardingpoint_model->get($id);
        if (!$record) {
            $this->_error('Boarding record not found', 404);
            return;
        }

        $this->boardingpoint_model->remove($id);
        $this->_success('Boarding record deleted successfully', ['id' => $id]);
    }
}
