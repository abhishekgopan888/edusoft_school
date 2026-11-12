<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Drivers extends Base
{
    public $swagger_meta = [
        'tag' => 'Driver Management',
        'description' => 'Endpoints for managing transport drivers. Requires X-API-Key: driver-api-secret-key-2026',
        'methods' => [
            'index' => [
                'path' => '/api/drivers',
                'summary' => 'Get all drivers list',
                'description' => 'Retrieves list of all transport drivers.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'responses' => [
                    '200' => ['description' => 'List of drivers retrieved successfully'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing API key']
                ]
            ],
            'details' => [
                'path' => '/api/drivers/details',
                'summary' => 'Get single driver details',
                'description' => 'Retrieves details of a specific driver by ID.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Driver ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Driver details retrieved successfully'],
                    '404' => ['description' => 'Driver not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'add' => [
                'path' => '/api/drivers/add',
                'summary' => 'Add new driver',
                'description' => 'Creates a new driver record.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['driver_name', 'licence', 'contact'],
                                'properties' => [
                                    'driver_name' => ['type' => 'string', 'example' => 'John Doe'],
                                    'age' => ['type' => 'integer', 'example' => 38],
                                    'licence' => ['type' => 'string', 'example' => 'DL-987654321'],
                                    'contact' => ['type' => 'string', 'example' => '9876543210'],
                                    'address' => ['type' => 'string', 'example' => '123 Main St, Springfield'],
                                    'status' => ['type' => 'string', 'example' => 'Active', 'enum' => ['Active', 'Inactive', 'leave']],
                                    'note' => ['type' => 'string', 'example' => 'Experienced driver']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Driver created successfully'],
                    '400' => ['description' => 'Validation error'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'edit' => [
                'path' => '/api/drivers/edit',
                'summary' => 'Edit driver details',
                'description' => 'Updates an existing driver record.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['id', 'driver_name'],
                                'properties' => [
                                    'id' => ['type' => 'integer', 'example' => 1],
                                    'driver_name' => ['type' => 'string', 'example' => 'John Doe'],
                                    'age' => ['type' => 'integer', 'example' => 39],
                                    'licence' => ['type' => 'string', 'example' => 'DL-987654321'],
                                    'contact' => ['type' => 'string', 'example' => '9876543210'],
                                    'address' => ['type' => 'string', 'example' => '456 Elm St, Springfield'],
                                    'status' => ['type' => 'string', 'example' => 'Active'],
                                    'note' => ['type' => 'string', 'example' => 'Updated note']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Driver updated successfully'],
                    '404' => ['description' => 'Driver not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'delete' => [
                'path' => '/api/drivers/delete',
                'summary' => 'Delete driver',
                'description' => 'Removes a driver record by ID.',
                'http_method' => 'delete',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Driver ID to delete',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Driver deleted successfully'],
                    '404' => ['description' => 'Driver not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ]
        ]
    ];

    public function index()
    {
        $this->_check_auth(['driver-api-secret-key-2026']);
        $drivers = $this->db->order_by('id', 'desc')->get('drivers')->result_array();
        $this->_success('Drivers list retrieved successfully', $drivers);
    }

    public function details()
    {
        $this->_check_auth(['driver-api-secret-key-2026']);
        $id = $this->input->get('id');
        if (empty($id)) {
            $this->_error('Driver ID is required', 400);
            return;
        }

        $driver = $this->db->get_where('drivers', ['id' => $id])->row_array();
        if (!$driver) {
            $this->_error('Driver not found', 404);
            return;
        }

        $this->_success('Driver details retrieved successfully', $driver);
    }

    public function add()
    {
        $this->_check_auth(['driver-api-secret-key-2026']);
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $driver_name = isset($input['driver_name']) ? trim($input['driver_name']) : '';
        $licence     = isset($input['licence']) ? trim($input['licence']) : '';
        $contact     = isset($input['contact']) ? trim($input['contact']) : '';

        if (empty($driver_name) || empty($licence) || empty($contact)) {
            $this->_error('Required fields missing: driver_name, licence, contact', 400);
            return;
        }

        $data = [
            'driver_name' => $driver_name,
            'age'         => isset($input['age']) ? $input['age'] : null,
            'licence'     => $licence,
            'contact'     => $contact,
            'address'     => isset($input['address']) ? $input['address'] : '',
            'status'      => isset($input['status']) ? $input['status'] : 'Active',
            'note'        => isset($input['note']) ? $input['note'] : ''
        ];

        $this->db->insert('drivers', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->_success('Driver created successfully', ['id' => $insert_id], 201);
        } else {
            $this->_error('Failed to create driver', 500);
        }
    }

    public function edit()
    {
        $this->_check_auth(['driver-api-secret-key-2026']);
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $id = isset($input['id']) ? $input['id'] : null;
        if (empty($id)) {
            $this->_error('Driver ID is required', 400);
            return;
        }

        $driver = $this->db->get_where('drivers', ['id' => $id])->row_array();
        if (!$driver) {
            $this->_error('Driver not found', 404);
            return;
        }

        $data = [];
        foreach (['driver_name', 'age', 'licence', 'contact', 'address', 'status', 'note'] as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (!empty($data)) {
            $this->db->where('id', $id)->update('drivers', $data);
        }

        $updated_driver = $this->db->get_where('drivers', ['id' => $id])->row_array();
        $this->_success('Driver updated successfully', $updated_driver);
    }

    public function delete()
    {
        $this->_check_auth(['driver-api-secret-key-2026']);
        $id = $this->input->get('id');
        if (empty($id)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? $input['id'] : null;
        }

        if (empty($id)) {
            $this->_error('Driver ID is required', 400);
            return;
        }

        $driver = $this->db->get_where('drivers', ['id' => $id])->row_array();
        if (!$driver) {
            $this->_error('Driver not found', 404);
            return;
        }

        $this->db->where('id', $id)->delete('drivers');
        $this->_success('Driver deleted successfully', ['id' => $id]);
    }
}
