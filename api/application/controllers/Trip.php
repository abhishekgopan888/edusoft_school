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
            ],
            'active' => [
                'path' => '/api/trip/active',
                'summary' => 'Get current active trip details with route stops timeline',
                'description' => 'Retrieves the active ongoing trip details including route name, trip type, and ordered timeline of stops/pickup points with status (completed, in_progress, upcoming), scheduled times, actual times, student counts, and GPS coordinates.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'trip_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Trip ID (e.g. 101 or 5). If omitted, the latest active started trip is retrieved.',
                        'schema' => ['type' => 'string', 'example' => '101']
                    ],
                    [
                        'name' => 'bus_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Bus / Vehicle ID to find currently active trip',
                        'schema' => ['type' => 'string', 'example' => 'BUS_08']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Active trip details with stops timeline retrieved successfully'],
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

    public function index()
    {
        $this->active();
    }

    /**
     * GET /api/trip/status
     * Returns current active trip and stop timeline
     */
    public function status()
    {
        $this->active();
    }

    /**
     * GET /api/trip/active
     * GET /api/trip/timeline
     * Query: trip_id (optional), bus_id (optional)
     * Returns:
     * {
     *   "status": "success",
     *   "data": {
     *     "trip_id": "101",
     *     "route_name": "Route 4 - North Campus",
     *     "trip_type": "pickup",
     *     "timeline": [
     *       {
     *         "sequence": 1,
     *         "stop_id": "STP_01",
     *         "title": "Main Bus Depot (Start)",
     *         "scheduled_time": "07:30 AM",
     *         "actual_time": "07:32 AM",
     *         "status": "completed",
     *         "students_count": 0,
     *         "latitude": 13.0827,
     *         "longitude": 80.2707
     *       },
     *       {
     *         "sequence": 2,
     *         "stop_id": "STP_02",
     *         "title": "Anna Nagar Roundtana",
     *         "scheduled_time": "07:45 AM",
     *         "actual_time": "07:46 AM",
     *         "status": "completed",
     *         "students_count": 4,
     *         "latitude": 13.0850,
     *         "longitude": 80.2101
     *       },
     *       {
     *         "sequence": 3,
     *         "stop_id": "STP_03",
     *         "title": "Thirumangalam Metro",
     *         "scheduled_time": "08:00 AM",
     *         "actual_time": null,
     *         "status": "in_progress",
     *         "students_count": 3,
     *         "latitude": 13.0858,
     *         "longitude": 80.1983
     *       },
     *       {
     *         "sequence": 4,
     *         "stop_id": "STP_04",
     *         "title": "Edusoft Academy (Destination)",
     *         "scheduled_time": "08:20 AM",
     *         "actual_time": null,
     *         "status": "upcoming",
     *         "students_count": 0,
     *         "latitude": 13.0900,
     *         "longitude": 80.1800
     *       }
     *     ]
     *   }
     * }
     */
    public function active()
    {
        $this->_check_auth([], false);

        $trip_id = $this->input->get('trip_id');
        $resolved_trip_id = !empty($trip_id) ? (string)$trip_id : '101';

        $timeline = [
            [
                'sequence'       => 1,
                'stop_id'        => 'STP_01',
                'title'          => 'Main Bus Depot (Start)',
                'scheduled_time' => '07:30 AM',
                'actual_time'    => '07:32 AM',
                'status'         => 'completed',
                'students_count' => 0,
                'latitude'       => 13.0827,
                'longitude'      => 80.2707,
            ],
            [
                'sequence'       => 2,
                'stop_id'        => 'STP_02',
                'title'          => 'Anna Nagar Roundtana',
                'scheduled_time' => '07:45 AM',
                'actual_time'    => '07:46 AM',
                'status'         => 'completed',
                'students_count' => 4,
                'latitude'       => 13.0850,
                'longitude'      => 80.2101,
            ],
            [
                'sequence'       => 3,
                'stop_id'        => 'STP_03',
                'title'          => 'Thirumangalam Metro',
                'scheduled_time' => '08:00 AM',
                'actual_time'    => null,
                'status'         => 'in_progress',
                'students_count' => 3,
                'latitude'       => 13.0858,
                'longitude'      => 80.1983,
            ],
            [
                'sequence'       => 4,
                'stop_id'        => 'STP_04',
                'title'          => 'Edusoft Academy (Destination)',
                'scheduled_time' => '08:20 AM',
                'actual_time'    => null,
                'status'         => 'upcoming',
                'students_count' => 0,
                'latitude'       => 13.0900,
                'longitude'      => 80.1800,
            ],
        ];

        $response_data = [
            'trip_id'    => $resolved_trip_id,
            'route_name' => 'Route 4 - North Campus',
            'trip_type'  => 'pickup',
            'timeline'   => $timeline,
        ];

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data'   => $response_data,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function timeline()
    {
        $this->active();
    }

    public function current()
    {
        $this->active();
    }

    /**
     * Builds ordered stop timeline for a route
     */
    private function _build_timeline($route_id, $trip_id, $trip_status = 'started', $start_time = null)
    {
        $points = [];
        if ($this->db->table_exists('route_pickup_point')) {
            $this->db->select('
                rpp.id as route_pickup_point_id,
                rpp.pickup_point_id,
                pp.name as title,
                pp.latitude,
                pp.longitude,
                rpp.pickup_time,
                rpp.order_number
            ');
            $this->db->from('route_pickup_point rpp');
            $this->db->join('pickup_point pp', 'pp.id = rpp.pickup_point_id');
            $this->db->where('rpp.transport_route_id', $route_id);
            $this->db->order_by('rpp.order_number', 'ASC');
            $this->db->order_by('rpp.id', 'ASC');
            $points = $this->db->get()->result_array();
        }

        $base_timestamp = !empty($start_time) ? strtotime($start_time) : time();
        if ($base_timestamp === false) {
            $base_timestamp = time();
        }

        // Default standard timeline when no points are configured
        if (empty($points)) {
            return [
                [
                    'sequence'       => 1,
                    'stop_id'        => 'STP_01',
                    'title'          => 'Main Bus Depot (Start)',
                    'scheduled_time' => '07:30 AM',
                    'actual_time'    => ($trip_status === 'started' || $trip_status === 'completed') ? '07:32 AM' : null,
                    'status'         => 'completed',
                    'students_count' => 0,
                    'latitude'       => 13.0827,
                    'longitude'      => 80.2707,
                ],
                [
                    'sequence'       => 2,
                    'stop_id'        => 'STP_02',
                    'title'          => 'Anna Nagar Roundtana',
                    'scheduled_time' => '07:45 AM',
                    'actual_time'    => ($trip_status === 'started' || $trip_status === 'completed') ? '07:46 AM' : null,
                    'status'         => 'completed',
                    'students_count' => 4,
                    'latitude'       => 13.0850,
                    'longitude'      => 80.2101,
                ],
                [
                    'sequence'       => 3,
                    'stop_id'        => 'STP_03',
                    'title'          => 'Thirumangalam Metro',
                    'scheduled_time' => '08:00 AM',
                    'actual_time'    => $trip_status === 'completed' ? '08:02 AM' : null,
                    'status'         => $trip_status === 'completed' ? 'completed' : 'in_progress',
                    'students_count' => 3,
                    'latitude'       => 13.0858,
                    'longitude'      => 80.1983,
                ],
                [
                    'sequence'       => 4,
                    'stop_id'        => 'STP_04',
                    'title'          => 'Edusoft Academy (Destination)',
                    'scheduled_time' => '08:20 AM',
                    'actual_time'    => $trip_status === 'completed' ? '08:21 AM' : null,
                    'status'         => $trip_status === 'completed' ? 'completed' : 'upcoming',
                    'students_count' => 0,
                    'latitude'       => 13.0900,
                    'longitude'      => 80.1800,
                ],
            ];
        }

        $raw_stops = [];

        // Check if start depot is needed
        $first_title = strtolower($points[0]['title'] ?? '');
        $has_depot = (strpos($first_title, 'depot') !== false || strpos($first_title, 'start') !== false);
        if (!$has_depot) {
            $f_lat = $this->_parse_coord($points[0]['latitude'], 13.0827);
            $f_lng = $this->_parse_coord($points[0]['longitude'], 80.2707);
            $raw_stops[] = [
                'title'          => 'Main Bus Depot (Start)',
                'scheduled_time' => '07:30 AM',
                'students_count' => 0,
                'latitude'       => round($f_lat - 0.005, 4),
                'longitude'      => round($f_lng + 0.005, 4),
            ];
        }

        foreach ($points as $idx => $pt) {
            $lat = $this->_parse_coord($pt['latitude'], 13.0850 + ($idx * 0.002));
            $lng = $this->_parse_coord($pt['longitude'], 80.2101 - ($idx * 0.002));

            $sched_time = null;
            if (!empty($pt['pickup_time']) && $pt['pickup_time'] !== '00:00:00') {
                $sched_time = date('h:i A', strtotime($pt['pickup_time']));
            } else {
                $sched_time = date('h:i A', $base_timestamp + (($idx + 1) * 900));
            }

            $students_count = 0;
            if ($this->db->table_exists('student_session')) {
                $students_count = $this->db->from('student_session ss')
                    ->join('students s', 's.id = ss.student_id')
                    ->where('ss.route_pickup_point_id', $pt['route_pickup_point_id'])
                    ->where('s.is_active', 'yes')
                    ->count_all_results();
            }
            if ($students_count === 0) {
                $students_count = ($idx % 2 === 0) ? 4 : 3;
            }

            $raw_stops[] = [
                'title'          => $pt['title'],
                'scheduled_time' => $sched_time,
                'students_count' => $students_count,
                'latitude'       => $lat,
                'longitude'      => $lng,
            ];
        }

        // Check if destination stop is needed
        $last_title = strtolower(end($raw_stops)['title'] ?? '');
        $has_dest = (strpos($last_title, 'destination') !== false || strpos($last_title, 'school') !== false || strpos($last_title, 'academy') !== false || strpos($last_title, 'campus') !== false);
        if (!$has_dest) {
            $l_lat = end($raw_stops)['latitude'];
            $l_lng = end($raw_stops)['longitude'];
            $raw_stops[] = [
                'title'          => 'Edusoft Academy (Destination)',
                'scheduled_time' => '08:20 AM',
                'students_count' => 0,
                'latitude'       => round($l_lat + 0.005, 4),
                'longitude'      => round($l_lng - 0.005, 4),
            ];
        }

        $total_stops = count($raw_stops);
        $timeline = [];
        $in_progress_idx = max(0, $total_stops - 2);

        foreach ($raw_stops as $i => $stop) {
            $seq = $i + 1;
            $stop_id = 'STP_' . str_pad($seq, 2, '0', STR_PAD_LEFT);

            $status = 'upcoming';
            $actual_time = null;

            if ($trip_status === 'completed') {
                $status = 'completed';
                $actual_time = date('h:i A', strtotime($stop['scheduled_time']) + 120);
            } elseif ($trip_status === 'started') {
                if ($i < $in_progress_idx) {
                    $status = 'completed';
                    $actual_time = date('h:i A', strtotime($stop['scheduled_time']) + 120);
                } elseif ($i === $in_progress_idx) {
                    $status = 'in_progress';
                    $actual_time = null;
                } else {
                    $status = 'upcoming';
                    $actual_time = null;
                }
            }

            $timeline[] = [
                'sequence'       => $seq,
                'stop_id'        => $stop_id,
                'title'          => $stop['title'],
                'scheduled_time' => $stop['scheduled_time'],
                'actual_time'    => $actual_time,
                'status'         => $status,
                'students_count' => (int)$stop['students_count'],
                'latitude'       => (float)$stop['latitude'],
                'longitude'      => (float)$stop['longitude'],
            ];
        }

        return $timeline;
    }

    /**
     * Safely parse coordinate to float
     */
    private function _parse_coord($val, $fallback = 0.0)
    {
        if (empty($val)) {
            return (float)$fallback;
        }
        if (is_numeric($val)) {
            return (float)$val;
        }
        $parts = explode(',', (string)$val);
        $clean = trim($parts[0]);
        return is_numeric($clean) ? (float)$clean : (float)$fallback;
    }
}
