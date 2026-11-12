<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Attenders extends Base
{
    public $swagger_meta = [
        'tag' => 'Attender Management',
        'description' => 'Endpoints for managing transport attenders. Requires X-API-Key: attender-api-secret-key-2026',
        'methods' => [
            'index' => [
                'path' => '/api/attenders',
                'summary' => 'Get all attenders list',
                'description' => 'Retrieves list of all transport attenders.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'responses' => [
                    '200' => ['description' => 'List of attenders retrieved successfully'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing API key']
                ]
            ],
            'details' => [
                'path' => '/api/attenders/details',
                'summary' => 'Get single attender details',
                'description' => 'Retrieves details of a specific attender by ID.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Attender ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Attender details retrieved successfully'],
                    '404' => ['description' => 'Attender not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'add' => [
                'path' => '/api/attenders/add',
                'summary' => 'Add new attender',
                'description' => 'Creates a new attender record.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['attender_name'],
                                'properties' => [
                                    'attender_name' => ['type' => 'string', 'example' => 'Akhila'],
                                    'age' => ['type' => 'integer', 'example' => 43],
                                    'address' => ['type' => 'string', 'example' => 'Kollam'],
                                    'status' => ['type' => 'string', 'example' => 'Active', 'enum' => ['Active', 'Inactive', 'leave']],
                                    'note' => ['type' => 'string', 'example' => 'School transport attender']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Attender created successfully'],
                    '400' => ['description' => 'Validation error'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'edit' => [
                'path' => '/api/attenders/edit',
                'summary' => 'Edit attender details',
                'description' => 'Updates an existing attender record.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['id', 'attender_name'],
                                'properties' => [
                                    'id' => ['type' => 'integer', 'example' => 1],
                                    'attender_name' => ['type' => 'string', 'example' => 'Akhila'],
                                    'age' => ['type' => 'integer', 'example' => 44],
                                    'address' => ['type' => 'string', 'example' => 'Kollam'],
                                    'status' => ['type' => 'string', 'example' => 'Active'],
                                    'note' => ['type' => 'string', 'example' => 'Updated note']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Attender updated successfully'],
                    '404' => ['description' => 'Attender not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'delete' => [
                'path' => '/api/attenders/delete',
                'summary' => 'Delete attender',
                'description' => 'Removes an attender record by ID.',
                'http_method' => 'delete',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Attender ID to delete',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Attender deleted successfully'],
                    '404' => ['description' => 'Attender not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ]
        ]
    ];

    public function index()
    {
        $this->_check_auth(['attender-api-secret-key-2026']);
        $attenders = $this->db->order_by('id', 'desc')->get('attenders')->result_array();
        $this->_success('Attenders list retrieved successfully', $attenders);
    }

    public function details()
    {
        $this->_check_auth(['attender-api-secret-key-2026']);
        $id = $this->input->get('id');
        if (empty($id)) {
            $this->_error('Attender ID is required', 400);
            return;
        }

        $attender = $this->db->get_where('attenders', ['id' => $id])->row_array();
        if (!$attender) {
            $this->_error('Attender not found', 404);
            return;
        }

        $this->_success('Attender details retrieved successfully', $attender);
    }

    public function add()
    {
        $this->_check_auth(['attender-api-secret-key-2026']);
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $attender_name = isset($input['attender_name']) ? trim($input['attender_name']) : '';
        if (empty($attender_name)) {
            $this->_error('Required field missing: attender_name', 400);
            return;
        }

        $data = [
            'attender_name' => $attender_name,
            'age'           => isset($input['age']) ? $input['age'] : null,
            'address'       => isset($input['address']) ? $input['address'] : '',
            'status'        => isset($input['status']) ? $input['status'] : 'Active',
            'note'          => isset($input['note']) ? $input['note'] : ''
        ];

        $this->db->insert('attenders', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->_success('Attender created successfully', ['id' => $insert_id], 201);
        } else {
            $this->_error('Failed to create attender', 500);
        }
    }

    public function edit()
    {
        $this->_check_auth(['attender-api-secret-key-2026']);
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $id = isset($input['id']) ? $input['id'] : null;
        if (empty($id)) {
            $this->_error('Attender ID is required', 400);
            return;
        }

        $attender = $this->db->get_where('attenders', ['id' => $id])->row_array();
        if (!$attender) {
            $this->_error('Attender not found', 404);
            return;
        }

        $data = [];
        foreach (['attender_name', 'age', 'address', 'status', 'note'] as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (!empty($data)) {
            $this->db->where('id', $id)->update('attenders', $data);
        }

        $updated_attender = $this->db->get_where('attenders', ['id' => $id])->row_array();
        $this->_success('Attender updated successfully', $updated_attender);
    }

    public function delete()
    {
        $this->_check_auth(['attender-api-secret-key-2026']);
        $id = $this->input->get('id');
        if (empty($id)) {
            $input = json_decode(file_get_contents('php://input'), true);
            $id = isset($input['id']) ? $input['id'] : null;
        }

        if (empty($id)) {
            $this->_error('Attender ID is required', 400);
            return;
        }

        $attender = $this->db->get_where('attenders', ['id' => $id])->row_array();
        if (!$attender) {
            $this->_error('Attender not found', 404);
            return;
        }

        $this->db->where('id', $id)->delete('attenders');
        $this->_success('Attender deleted successfully', ['id' => $id]);
    }
}
