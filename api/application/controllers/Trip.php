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
        $bus_id  = $this->input->get('bus_id');

        $bus_lat = null;
        $bus_lng = null;
        if ($this->db->table_exists('bus_locations')) {
            $loc_query = $this->db->from('bus_locations');
            if (!empty($bus_id)) {
                $loc_query->where('bus_id', $bus_id);
            } elseif (!empty($trip_id)) {
                $loc_query->where('trip_id', $trip_id);
            }
            $loc = $loc_query->order_by('id', 'DESC')->limit(1)->get()->row_array();
            if (!empty($loc)) {
                $bus_lat = (float)$loc['latitude'];
                $bus_lng = (float)$loc['longitude'];
            }
        }

        $trip_info = $this->_resolve_trip_details($trip_id, $bus_id, $bus_lat, $bus_lng);

        $response_data = [
            'trip_id'       => (string)$trip_info['trip_id'],
            'route_name'    => (string)$trip_info['route_name'],
            'trip_type'     => (string)$trip_info['trip_type'],
            'status'        => (string)$trip_info['status'],
            'pickup_point'  => $trip_info['pickup_point'],
            'drop_point'    => $trip_info['drop_point'],
            'between_stops' => $trip_info['between_stops'],
            'timeline'      => $trip_info['timeline'],
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
     * Helper to resolve trip-based details by checking what trip is started in database
     */
    private function _resolve_trip_details($trip_id = null, $bus_id = null, $bus_lat = null, $bus_lng = null)
    {
        $trip = null;

        // 1. Check if specific trip_id exists in trips table
        if (!empty($trip_id) && $this->db->table_exists('trips')) {
            $trip = $this->db->where('id', $trip_id)
                ->get('trips')
                ->row_array();
        }

        // 2. Check if a trip is currently started for this bus_id
        if (empty($trip) && !empty($bus_id) && $this->db->table_exists('trips')) {
            $trip = $this->db->where('bus_id', $bus_id)
                ->where('status', 'started')
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        }

        // 3. Check vehicles table for matched vehicle ID or number
        if (empty($trip) && !empty($bus_id) && $this->db->table_exists('vehicles') && $this->db->table_exists('trips')) {
            $veh = $this->db->where('vehicle_no', $bus_id)
                ->or_where('id', $bus_id)
                ->get('vehicles')
                ->row_array();
            if (!empty($veh)) {
                $trip = $this->db->where_in('bus_id', [(string)$veh['id'], (string)$veh['vehicle_no']])
                    ->where('status', 'started')
                    ->order_by('id', 'DESC')
                    ->get('trips')
                    ->row_array();
                if (empty($trip)) {
                    $trip = $this->db->where_in('bus_id', [(string)$veh['id'], (string)$veh['vehicle_no']])
                        ->order_by('id', 'DESC')
                        ->get('trips')
                        ->row_array();
                }
            }
        }

        // 4. Fallback: check latest trip for this bus_id
        if (empty($trip) && !empty($bus_id) && $this->db->table_exists('trips')) {
            $trip = $this->db->where('bus_id', $bus_id)
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        }

        // 5. Fallback: find any active started trip in the system
        if (empty($trip) && $this->db->table_exists('trips')) {
            $trip = $this->db->where('status', 'started')
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        }

        // 6. Fallback: find latest trip in the system
        if (empty($trip) && $this->db->table_exists('trips')) {
            $trip = $this->db->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        }

        $resolved_trip_id = null;
        $route_id = null;
        $route_name = null;
        $trip_type = 'pickup';
        $status = 'started';
        $driver_id = null;
        $start_time = null;

        if (!empty($trip)) {
            $resolved_trip_id = (string)$trip['id'];
            $route_id = !empty($trip['route_id']) ? (int)$trip['route_id'] : null;
            $trip_type = !empty($trip['trip_type']) ? (string)$trip['trip_type'] : 'pickup';
            $status = !empty($trip['status']) ? (string)$trip['status'] : 'started';
            $driver_id = !empty($trip['driver_id']) ? (string)$trip['driver_id'] : null;
            $start_time = $trip['start_time'] ?? null;
        } elseif (!empty($trip_id)) {
            $resolved_trip_id = (string)$trip_id;
        }

        // If no route_id found from trip, find route from vehicle_routes or route_pickup_point
        if (empty($route_id) && !empty($bus_id) && $this->db->table_exists('vehicle_routes')) {
            $vr = $this->db->where('vehicle_id', $bus_id)->get('vehicle_routes')->row_array();
            if (!empty($vr['route_id'])) {
                $route_id = (int)$vr['route_id'];
            }
        }

        if (empty($route_id) && $this->db->table_exists('route_pickup_point')) {
            $rpp = $this->db->select('transport_route_id')->order_by('id', 'ASC')->limit(1)->get('route_pickup_point')->row_array();
            if (!empty($rpp['transport_route_id'])) {
                $route_id = (int)$rpp['transport_route_id'];
            }
        }

        if (!empty($route_id) && $this->db->table_exists('transport_route')) {
            $route = $this->db->select('route_title')
                ->where('id', $route_id)
                ->get('transport_route')
                ->row();
            if ($route && !empty($route->route_title)) {
                $route_name = $route->route_title;
            }
        }

        $timeline_info = !empty($route_id) 
            ? $this->_build_timeline($route_id, $resolved_trip_id, $status, $start_time, $bus_lat, $bus_lng)
            : ['timeline' => [], 'pickup_point' => null, 'drop_point' => null, 'between_stops' => []];

        $timeline = $timeline_info['timeline'];

        $next_stop = null;
        foreach ($timeline as $st) {
            if ($st['status'] === 'in_progress') {
                $next_stop = $st;
                break;
            }
        }
        if ($next_stop === null) {
            foreach ($timeline as $st) {
                if ($st['status'] === 'upcoming') {
                    $next_stop = $st;
                    break;
                }
            }
        }
        $next_stop_name = !empty($next_stop['title']) ? $next_stop['title'] : null;

        return [
            'trip_id'        => $resolved_trip_id,
            'route_id'       => $route_id,
            'route_name'     => $route_name,
            'driver_id'      => $driver_id,
            'trip_type'      => $trip_type,
            'status'         => $status,
            'start_time'     => $start_time,
            'next_stop'      => $next_stop,
            'next_stop_name' => $next_stop_name,
            'pickup_point'   => $timeline_info['pickup_point'],
            'drop_point'     => $timeline_info['drop_point'],
            'between_stops'  => $timeline_info['between_stops'],
            'timeline'       => $timeline,
        ];
    }

    /**
     * Builds real timeline of pickup points from database
     */
    private function _build_timeline($route_id, $trip_id = null, $trip_status = 'started', $start_time = null, $bus_lat = null, $bus_lng = null)
    {
        $empty_result = [
            'timeline'      => [],
            'pickup_point'  => null,
            'drop_point'    => null,
            'between_stops' => [],
        ];

        if (empty($route_id) || !$this->db->table_exists('route_pickup_point')) {
            return $empty_result;
        }

        $points = $this->db->select('
            rpp.id as rpp_id,
            rpp.pickup_point_id,
            pp.name as title,
            pp.latitude,
            pp.longitude,
            rpp.pickup_time,
            rpp.order_number,
            rpp.point_type
        ')
        ->from('route_pickup_point rpp')
        ->join('pickup_point pp', 'pp.id = rpp.pickup_point_id')
        ->where('rpp.transport_route_id', $route_id)
        ->order_by('rpp.order_number', 'ASC')
        ->order_by('rpp.id', 'ASC')
        ->get()
        ->result_array();

        if (empty($points)) {
            return $empty_result;
        }

        $total_points = count($points);
        $parsed_points = [];

        foreach ($points as $idx => $pt) {
            // Strictly sequential sequence numbering (1, 2, 3...)
            $seq = $idx + 1;
            $stop_id = 'STP_' . str_pad($pt['pickup_point_id'], 2, '0', STR_PAD_LEFT);

            // Parse real coordinates from database
            list($lat, $lng) = $this->_parse_lat_lng($pt['latitude'], $pt['longitude']);

            // Parse real scheduled time
            $sched_time = null;
            if (!empty($pt['pickup_time']) && $pt['pickup_time'] !== '00:00:00') {
                $sched_time = date('h:i A', strtotime($pt['pickup_time']));
            } elseif (!empty($start_time)) {
                $sched_time = date('h:i A', strtotime($start_time) + ($idx * 900));
            }

            // Real student count from student_session
            $students_count = 0;
            if ($this->db->table_exists('student_session')) {
                $students_count = $this->db->from('student_session ss')
                    ->join('students s', 's.id = ss.student_id')
                    ->where('ss.route_pickup_point_id', $pt['rpp_id'])
                    ->where('s.is_active', 'yes')
                    ->count_all_results();
            }

            $point_type = !empty($pt['point_type']) 
                ? $pt['point_type'] 
                : (($idx === 0) ? 'pickup' : (($idx === $total_points - 1 && $total_points > 1) ? 'drop' : 'stop'));

            $parsed_points[] = [
                'seq'            => (int)$seq,
                'stop_id'        => $stop_id,
                'point_type'     => $point_type,
                'title'          => (string)$pt['title'],
                'latitude'       => (float)$lat,
                'longitude'      => (float)$lng,
                'sched_time'     => $sched_time,
                'students_count' => (int)$students_count,
            ];
        }

        // Determine live progression index
        $in_progress_idx = 0;
        if ($trip_status === 'started' && !empty($bus_lat) && !empty($bus_lng) && $total_points > 1) {
            $closest_idx = 0;
            $min_dist = PHP_FLOAT_MAX;

            foreach ($parsed_points as $i => $p) {
                if ($p['latitude'] != 0.0 && $p['longitude'] != 0.0) {
                    $dist = $this->_calculate_distance_km($bus_lat, $bus_lng, $p['latitude'], $p['longitude']);
                    if ($dist < $min_dist) {
                        $min_dist = $dist;
                        $closest_idx = $i;
                    }
                }
            }

            // If bus is closest to start stop (idx 0) but has already started and moved away (> 0.3 km), advance to stop 1
            if ($closest_idx === 0 && $min_dist > 0.3) {
                $in_progress_idx = 1;
            } else {
                $in_progress_idx = $closest_idx;
            }
        } elseif ($trip_status === 'started' && $total_points > 1) {
            $in_progress_idx = 1;
        }

        $timeline = [];
        foreach ($parsed_points as $i => $p) {
            $status = 'upcoming';
            $actual_time = null;

            if ($trip_status === 'completed') {
                $status = 'completed';
                $actual_time = $p['sched_time'];
            } elseif ($trip_status === 'started') {
                if ($i < $in_progress_idx) {
                    $status = 'completed';
                    $actual_time = !empty($p['sched_time']) 
                        ? date('h:i A', strtotime($p['sched_time']) + 120) 
                        : (!empty($start_time) ? date('h:i A', strtotime($start_time) + ($i * 900) + 120) : null);
                } elseif ($i === $in_progress_idx) {
                    $status = 'in_progress';
                    $actual_time = null;
                } else {
                    $status = 'upcoming';
                    $actual_time = null;
                }
            }

            $timeline[] = [
                'sequence'       => (int)$p['seq'],
                'stop_id'        => $p['stop_id'],
                'point_type'     => $p['point_type'],
                'title'          => $p['title'],
                'latitude'       => (float)$p['latitude'],
                'longitude'      => (float)$p['longitude'],
                'status'         => $status,
                'scheduled_time' => $p['sched_time'],
                'actual_time'    => $actual_time,
                'students_count' => (int)$p['students_count'],
            ];
        }

        $pickup_point = !empty($timeline[0]) ? $timeline[0] : null;
        $drop_point   = (count($timeline) > 1) ? $timeline[count($timeline) - 1] : null;
        $between_stops = [];
        if (count($timeline) > 2) {
            for ($bi = 1; $bi < count($timeline) - 1; $bi++) {
                $between_stops[] = $timeline[$bi];
            }
        }

        return [
            'timeline'      => $timeline,
            'pickup_point'  => $pickup_point,
            'drop_point'    => $drop_point,
            'between_stops' => $between_stops,
        ];
    }

    /**
     * Distance between two GPS points in kilometers (Haversine formula)
     */
    private function _calculate_distance_km($lat1, $lon1, $lat2, $lon2)
    {
        $earth_radius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earth_radius * $c;
    }

    /**
     * Helper to safely parse coordinate floats from database strings
     */
    private function _parse_lat_lng($lat_val, $lng_val)
    {
        $lat = 0.0;
        $lng = 0.0;

        if (!empty($lat_val)) {
            $lat_parts = array_map('trim', explode(',', (string)$lat_val));
            $lat = isset($lat_parts[0]) && is_numeric($lat_parts[0]) ? (float)$lat_parts[0] : 0.0;
            if (isset($lat_parts[1]) && is_numeric($lat_parts[1])) {
                $lng = (float)$lat_parts[1];
            }
        }

        if (!empty($lng_val)) {
            $lng_parts = array_map('trim', explode(',', (string)$lng_val));
            if (count($lng_parts) > 1 && is_numeric($lng_parts[1])) {
                $lng = (float)$lng_parts[1];
            } elseif (isset($lng_parts[0]) && is_numeric($lng_parts[0]) && $lng == 0.0) {
                $lng = (float)$lng_parts[0];
            }
        }

        return [$lat, $lng];
    }
}
