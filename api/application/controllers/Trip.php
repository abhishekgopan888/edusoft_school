<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Trip extends Base
{
    public $swagger_meta = [
        'tag' => 'Trip Management',
        'description' => 'Endpoints for starting, tracking, and completing bus transit trips by drivers/attenders in the Flutter app.',
        'methods' => [
            'start' => [
                'path' => '/api/trip/start',
                'summary' => 'Start a new trip',
                'description' => 'Creates and starts a new trip for a bus and route. Returns the created trip_id.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['bus_id', 'route_id'],
                                'properties' => [
                                    'bus_id' => [
                                        'type' => 'string',
                                        'example' => '12',
                                        'description' => 'Bus / Vehicle ID or Bus Number (e.g. 12, "KL 38 M 0990")'
                                    ],
                                    'route_id' => [
                                        'type' => 'string',
                                        'example' => '2',
                                        'description' => 'Transport Route ID'
                                    ],
                                    'driver_id' => [
                                        'type' => 'string',
                                        'example' => '1',
                                        'description' => 'Optional Driver ID (auto-detected if omitted)'
                                    ],
                                    'trip_type' => [
                                        'type' => 'string',
                                        'example' => 'pickup',
                                        'description' => 'Trip type: pickup or drop (default: pickup)'
                                    ],
                                    'note' => [
                                        'type' => 'string',
                                        'example' => 'Morning pickup trip',
                                        'description' => 'Optional note'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => ['description' => 'Trip started successfully, returns trip_id and details'],
                    '400' => ['description' => 'Validation error - bus_id and route_id are required']
                ]
            ],
            'end' => [
                'path' => '/api/trip/end',
                'summary' => 'End an ongoing trip',
                'description' => 'Marks a trip as completed with end_time.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['trip_id'],
                                'properties' => [
                                    'trip_id' => [
                                        'type' => 'integer',
                                        'example' => 1,
                                        'description' => 'Trip ID returned from /api/trip/start'
                                    ],
                                    'note' => [
                                        'type' => 'string',
                                        'example' => 'Completed trip smoothly'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Trip ended successfully'],
                    '400' => ['description' => 'trip_id is required'],
                    '404' => ['description' => 'Trip not found']
                ]
            ],
            'status' => [
                'path' => '/api/trip/status',
                'summary' => 'Get trip status or active trip',
                'description' => 'Gets trip status by trip_id, or finds the active trip by bus_id.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'trip_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Trip ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'bus_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Bus / Vehicle ID to find currently active trip',
                        'schema' => ['type' => 'string', 'example' => '12']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Trip status retrieved successfully'],
                    '404' => ['description' => 'No active trip found']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->_ensure_table_exists();
    }

    /**
     * Auto-create trips table if it doesn't exist
     */
    private function _ensure_table_exists()
    {
        if (!$this->db->table_exists('trips')) {
            $sql = "CREATE TABLE IF NOT EXISTS `trips` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `bus_id` varchar(100) NOT NULL,
                `route_id` int(11) NOT NULL,
                `driver_id` varchar(100) NOT NULL DEFAULT '0',
                `trip_type` varchar(50) NOT NULL DEFAULT 'pickup',
                `status` varchar(50) NOT NULL DEFAULT 'started',
                `start_time` datetime DEFAULT NULL,
                `end_time` datetime DEFAULT NULL,
                `note` text DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_bus_id` (`bus_id`),
                KEY `idx_route_id` (`route_id`),
                KEY `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->db->query($sql);
        }
    }

    /**
     * POST /api/trip/start
     * Body: { "bus_id": "12", "route_id": "2", "driver_id": "1", "trip_type": "pickup" }
     */
    public function start()
    {
        $this->_check_auth([], false);

        if (strtolower($this->input->server('REQUEST_METHOD')) !== 'post') {
            $this->_error('Method Not Allowed. Use POST.', 405);
            return;
        }

        $raw = file_get_contents('php://input');
        $params = json_decode($raw, true);
        if (!is_array($params)) {
            $params = $this->input->post();
        }

        $bus_id = $params['bus_id'] ?? $params['vehicle_id'] ?? $params['bus_number'] ?? $params['busid'] ?? $this->input->get('bus_id');
        $route_id = $params['route_id'] ?? $params['routeid'] ?? $this->input->get('route_id');
        $driver_id = $params['driver_id'] ?? $this->input->get('driver_id');
        $trip_type = $params['trip_type'] ?? 'pickup';
        $note = $params['note'] ?? null;

        if (empty($bus_id) || empty($route_id)) {
            $this->_error('bus_id and route_id are required to start a trip', 400);
            return;
        }

        // If driver_id is not passed, attempt to find assigned driver from vehicle_routes or vehicles
        if (empty($driver_id)) {
            $vr = $this->db->select('driver_id')
                ->from('vehicle_routes')
                ->where('route_id', $route_id)
                ->where('vehicle_id', $bus_id)
                ->get()
                ->row();
            if ($vr && !empty($vr->driver_id)) {
                $driver_id = $vr->driver_id;
            } else {
                $v = $this->db->select('driver_id')
                    ->from('vehicles')
                    ->where('id', $bus_id)
                    ->or_where('vehicle_no', $bus_id)
                    ->get()
                    ->row();
                if ($v && !empty($v->driver_id)) {
                    $driver_id = $v->driver_id;
                }
            }
        }

        $now = date('Y-m-d H:i:s');
        $insert_data = [
            'bus_id'     => (string)$bus_id,
            'route_id'   => (int)$route_id,
            'driver_id'  => !empty($driver_id) ? (string)$driver_id : '0',
            'trip_type'  => (string)$trip_type,
            'status'     => 'started',
            'start_time' => $now,
            'note'       => $note,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->db->insert('trips', $insert_data);
        $trip_id = $this->db->insert_id();

        $response_data = [
            'trip_id'    => (int)$trip_id,
            'bus_id'     => (string)$bus_id,
            'route_id'   => (int)$route_id,
            'driver_id'  => !empty($driver_id) ? (string)$driver_id : '0',
            'trip_type'  => (string)$trip_type,
            'status'     => 'started',
            'start_time' => $now,
            'note'       => $note,
        ];

        $this->_success('Trip started successfully', $response_data, 201);
    }

    /**
     * POST /api/trip/end
     * Body: { "trip_id": 1, "note": "completed" }
     */
    public function end()
    {
        $this->_check_auth([], false);

        if (strtolower($this->input->server('REQUEST_METHOD')) !== 'post') {
            $this->_error('Method Not Allowed. Use POST.', 405);
            return;
        }

        $raw = file_get_contents('php://input');
        $params = json_decode($raw, true);
        if (!is_array($params)) {
            $params = $this->input->post();
        }

        $trip_id = $params['trip_id'] ?? $params['id'] ?? $this->input->get('trip_id');
        $note = $params['note'] ?? null;

        if (empty($trip_id)) {
            $this->_error('trip_id is required', 400);
            return;
        }

        $trip = $this->db->where('id', $trip_id)->get('trips')->row_array();
        if (empty($trip)) {
            $this->_error('Trip not found', 404);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $update_data = [
            'status'     => 'completed',
            'end_time'   => $now,
            'updated_at' => $now,
        ];
        if (!empty($note)) {
            $update_data['note'] = $note;
        }

        $this->db->where('id', $trip_id)->update('trips', $update_data);

        $trip['status'] = 'completed';
        $trip['end_time'] = $now;
        if (!empty($note)) {
            $trip['note'] = $note;
        }
        $trip['trip_id'] = (int)$trip['id'];

        $this->_success('Trip completed successfully', $trip, 200);
    }

    /**
     * GET /api/trip/status
     * Query: trip_id or bus_id
     */
    public function status()
    {
        $this->_check_auth([], false);

        $trip_id = $this->input->get('trip_id');
        $bus_id = $this->input->get('bus_id');

        if (!empty($trip_id)) {
            $trip = $this->db->where('id', $trip_id)->get('trips')->row_array();
        } elseif (!empty($bus_id)) {
            $trip = $this->db->where('bus_id', $bus_id)
                ->where('status', 'started')
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        } else {
            $this->_error('trip_id or bus_id is required', 400);
            return;
        }

        if (empty($trip)) {
            $this->_error('No matching trip found', 404);
            return;
        }

        $trip['trip_id'] = (int)$trip['id'];
        $this->_success('Trip status retrieved successfully', $trip);
    }
}
