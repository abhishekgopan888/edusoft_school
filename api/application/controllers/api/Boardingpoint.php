<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Boardingpoint extends Base
{
    public $swagger_meta = [
        'tag' => 'Boarding Point Tracking',
        'description' => 'Endpoints for managing student bus boarding records with student details, grade, address, search, and filtering.',
        'methods' => [
            'index' => [
                'path' => '/api/boardingpoint',
                'summary' => 'Get all boarding records with filters',
                'description' => 'Retrieves list of student bus boarding records with student name, grade, address, vehicle, and attender details. Supports filtering by status, busid, attenderid, studentid, date, and keyword search.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by status (pending, boarded, deboarded, dropped)',
                        'schema' => ['type' => 'string', 'enum' => ['pending', 'boarded', 'deboarded', 'dropped'], 'example' => 'boarded']
                    ],
                    [
                        'name' => 'busid',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Bus/Vehicle ID',
                        'schema' => ['type' => 'integer', 'example' => 5]
                    ],
                    [
                        'name' => 'attenderid',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Attender ID',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'studentid',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Student ID',
                        'schema' => ['type' => 'integer', 'example' => 101]
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by record date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-08-06']
                    ],
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Search by student name, admission no, roll no, grade, vehicle no, attender name, or address',
                        'schema' => ['type' => 'string', 'example' => 'John']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Boarding records retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'details' => [
                'path' => '/api/boardingpoint/details',
                'summary' => 'Get single boarding record details',
                'description' => 'Retrieves details of a specific boarding record by ID including student info, grade, and address.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Boarding record ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Record details retrieved successfully'],
                    '404' => ['description' => 'Record not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'add' => [
                'path' => '/api/boardingpoint/add',
                'summary' => 'Add new boarding entry',
                'description' => 'Creates a new boarding entry for a student with status (pending, boarded, deboarded, dropped). Bus ID is optional (defaults to student\'s assigned bus or 0 if unassigned).',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['studentid', 'attenderid'],
                                'properties' => [
                                    'studentid' => ['type' => 'integer', 'example' => 816, 'description' => 'Student ID'],
                                    'busid' => ['type' => 'integer', 'nullable' => true, 'example' => 0, 'description' => 'Bus ID (optional/nullable, defaults to student\'s assigned bus or 0 if unassigned)'],
                                    'attenderid' => ['type' => 'integer', 'example' => 5, 'description' => 'Attender ID'],
                                    'status' => ['type' => 'string', 'enum' => ['pending', 'boarded', 'deboarded', 'dropped'], 'example' => 'boarded', 'description' => 'Boarding status']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => ['description' => 'Boarding record created successfully'],
                    '400' => ['description' => 'Required fields missing: studentid, attenderid or invalid status'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'update_status' => [
                'path' => '/api/boardingpoint/update_status',
                'summary' => 'Update student boarding status',
                'description' => 'Updates the status (pending, boarded, deboarded, dropped) of a boarding record by ID or studentid.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['status'],
                                'properties' => [
                                    'id' => ['type' => 'integer', 'example' => 1, 'description' => 'Boarding record ID'],
                                    'studentid' => ['type' => 'integer', 'example' => 101, 'description' => 'Student ID (if ID is not provided)'],
                                    'busid' => ['type' => 'integer', 'nullable' => true, 'example' => 5, 'description' => 'Bus ID (optional/nullable)'],
                                    'attenderid' => ['type' => 'integer', 'example' => 2, 'description' => 'Attender ID (optional)'],
                                    'status' => ['type' => 'string', 'enum' => ['pending', 'boarded', 'deboarded', 'dropped'], 'example' => 'boarded', 'description' => 'New status value (pending, boarded, deboarded, dropped)']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Boarding status updated successfully'],
                    '400' => ['description' => 'Missing required fields or parameters'],
                    '404' => ['description' => 'Boarding record not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'delete' => [
                'path' => '/api/boardingpoint/delete',
                'summary' => 'Delete boarding record',
                'description' => 'Removes a boarding record by ID.',
                'http_method' => 'delete',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Boarding record ID to delete',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Boarding record deleted successfully'],
                    '404' => ['description' => 'Record not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('boardingpoint_model');
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
            $this->_error('ID is required', 400);
            return;
        }

        $record = $this->boardingpoint_model->get($id);
        if (!$record) {
            $this->_error('Record not found', 404);
            return;
        }

        $this->_success('Record details retrieved successfully', $record);
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
            $this->_error('Record ID is required', 400);
            return;
        }

        $record = $this->boardingpoint_model->get($id);
        if (!$record) {
            $this->_error('Record not found', 404);
            return;
        }

        $this->boardingpoint_model->remove($id);
        $this->_success('Boarding record deleted successfully', ['id' => $id]);
    }
}
