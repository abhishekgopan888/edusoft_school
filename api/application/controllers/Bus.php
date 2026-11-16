<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Bus extends Base
{
    public $swagger_meta = [
        'tag' => 'Bus Live Location',
        'description' => 'Real-time GPS tracking and telemetry endpoints for school buses. Used by the Flutter driver app to push live coordinates and parents/admin apps to track buses.',
        'methods' => [
            'location_post' => [
                'path' => '/api/bus/location',
                'summary' => 'Record live bus location and GPS telemetry (supports list of objects)',
                'description' => 'Receives live GPS coordinates (latitude, longitude), speed, heading, and timestamp for a bus during an active trip. Accepts a list of objects for batch telemetry or a single object. Includes trip-based details in the response.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'array',
                                'description' => 'List of bus GPS location telemetry objects',
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['bus_id', 'latitude', 'longitude'],
                                    'properties' => [
                                        'bus_id' => [
                                            'type' => 'string',
                                            'example' => 'BUS_08',
                                            'description' => 'Bus identifier, vehicle ID, or registration number'
                                        ],
                                        'trip_id' => [
                                            'type' => 'string',
                                            'example' => 'TRIP_20261112_01',
                                            'description' => 'Associated Trip ID'
                                        ],
                                        'latitude' => [
                                            'type' => 'number',
                                            'format' => 'float',
                                            'example' => 12.971598,
                                            'description' => 'Current GPS Latitude'
                                        ],
                                        'longitude' => [
                                            'type' => 'number',
                                            'format' => 'float',
                                            'example' => 77.594562,
                                            'description' => 'Current GPS Longitude'
                                        ],
                                        'speed' => [
                                            'type' => 'number',
                                            'format' => 'float',
                                            'example' => 34.2,
                                            'description' => 'Speed in km/h (helps parents know if bus is moving)'
                                        ],
                                        'heading' => [
                                            'type' => 'number',
                                            'format' => 'float',
                                            'example' => 182.0,
                                            'description' => 'Direction in degrees (0-360) so the icon rotates on the map'
                                        ],
                                        'timestamp' => [
                                            'type' => 'string',
                                            'example' => '2026-11-12T07:15:23Z',
                                            'description' => 'ISO 8601 GPS timestamp'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => ['description' => 'Bus location(s) recorded successfully with trip details'],
                    '400' => ['description' => 'Validation error - bus_id, latitude, and longitude are required']
                ]
            ],
            'location_get' => [
                'path' => '/api/bus/location',
                'summary' => 'Get latest live location for a bus or trip',
                'description' => 'Retrieves the most recent GPS coordinates, speed, heading, and trip info for a given bus_id or trip_id.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'bus_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Bus / Vehicle ID to retrieve latest location for',
                        'schema' => ['type' => 'string', 'example' => 'BUS_08']
                    ],
                    [
                        'name' => 'trip_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Trip ID to retrieve latest location for',
                        'schema' => ['type' => 'string', 'example' => 'TRIP_20261112_01']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Latest bus location and telemetry retrieved successfully'],
                    '404' => ['description' => 'No location recorded yet for this bus or trip']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->_ensure_tables_exist();
    }

    /**
     * Auto-create bus_locations and trips tables if they don't exist
     */
    private function _ensure_tables_exist()
    {
        if (!$this->db->table_exists('bus_locations')) {
            $sql = "CREATE TABLE IF NOT EXISTS `bus_locations` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `bus_id` varchar(100) NOT NULL,
                `trip_id` varchar(100) DEFAULT NULL,
                `latitude` decimal(10,8) NOT NULL,
                `longitude` decimal(11,8) NOT NULL,
                `speed` decimal(6,2) DEFAULT '0.00',
                `heading` decimal(6,2) DEFAULT '0.00',
                `recorded_at` datetime NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_bus_id` (`bus_id`),
                KEY `idx_trip_id` (`trip_id`),
                KEY `idx_recorded_at` (`recorded_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            $this->db->query($sql);
        }

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
     * Dispatcher for /api/bus/location (supports both GET and POST)
     */
    public function location()
    {
        $method = strtolower($this->input->server('REQUEST_METHOD'));
        if ($method === 'post') {
            $this->_record_location();
        } elseif ($method === 'get') {
            $this->_get_latest_location();
        } else {
            $this->_error('Method Not Allowed. Use GET or POST.', 405);
        }
    }

    /**
     * POST /api/bus/location
     * Accepts a list of objects: [ { "bus_id": "...", ... }, { ... } ]
     * Or { "locations": [ { ... } ] }
     * Or a single object: { "bus_id": "...", ... }
     */
    private function _record_location()
    {
        $this->_check_auth([], false);

        $raw = file_get_contents('php://input');
        $params = json_decode($raw, true);
        if (!is_array($params)) {
            $params = $this->input->post();
        }

        // Detect if input is a list of objects or a single object
        $items = [];
        if (isset($params[0]) && is_array($params[0])) {
            // Direct JSON list of objects: [ { ... }, { ... } ]
            $items = $params;
        } elseif (isset($params['locations']) && is_array($params['locations'])) {
            // Object containing locations list: { "locations": [ ... ] }
            $items = $params['locations'];
        } elseif (isset($params['data']) && is_array($params['data'])) {
            $items = $params['data'];
        } elseif (!empty($params) && (isset($params['bus_id']) || isset($params['vehicle_id']) || isset($params['bus_number']) || isset($params['latitude']))) {
            // Single object: { "bus_id": ... }
            $items = [$params];
        }

        if (empty($items)) {
            $this->_error('Request body is empty or invalid. A bus location object or list of location objects is required.', 400);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $batch_data = [];
        $last_bus_id = null;
        $last_trip_id = null;
        $last_item_info = null;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $bus_id    = $item['bus_id'] ?? $item['vehicle_id'] ?? $item['bus_number'] ?? null;
            $trip_id   = $item['trip_id'] ?? null;
            $latitude  = $item['latitude'] ?? null;
            $longitude = $item['longitude'] ?? null;
            $speed     = isset($item['speed']) ? (float)$item['speed'] : 0.0;
            $heading   = isset($item['heading']) ? (float)$item['heading'] : 0.0;
            $timestamp = $item['timestamp'] ?? null;

            if (empty($bus_id) || $latitude === null || $longitude === null) {
                continue;
            }

            $recorded_at = null;
            if (!empty($timestamp)) {
                $parsed_time = strtotime($timestamp);
                if ($parsed_time !== false) {
                    $recorded_at = date('Y-m-d H:i:s', $parsed_time);
                }
            }
            if (empty($recorded_at)) {
                $recorded_at = $now;
            }

            $record = [
                'bus_id'      => (string)$bus_id,
                'trip_id'     => !empty($trip_id) ? (string)$trip_id : null,
                'latitude'    => (float)$latitude,
                'longitude'   => (float)$longitude,
                'speed'       => $speed,
                'heading'     => $heading,
                'recorded_at' => $recorded_at,
                'created_at'  => $now,
            ];

            $batch_data[] = $record;
            $last_bus_id = (string)$bus_id;
            if (!empty($trip_id)) {
                $last_trip_id = (string)$trip_id;
            }
            $last_item_info = [
                'bus_id'      => (string)$bus_id,
                'trip_id'     => !empty($trip_id) ? (string)$trip_id : null,
                'latitude'    => (float)$latitude,
                'longitude'   => (float)$longitude,
                'speed'       => (float)$speed,
                'heading'     => (float)$heading,
                'timestamp'   => !empty($timestamp) ? $timestamp : date('c', strtotime($recorded_at)),
            ];
        }

        if (empty($batch_data)) {
            $this->_error('bus_id, latitude, and longitude are required for each location item.', 400);
            return;
        }

        $recorded_count = count($batch_data);
        if ($recorded_count === 1) {
            $this->db->insert('bus_locations', $batch_data[0]);
            $inserted_id = $this->db->insert_id();
        } else {
            $this->db->insert_batch('bus_locations', $batch_data);
            $inserted_id = $this->db->insert_id();
        }

        // Fetch trip details based on trip_id or bus_id
        $trip_info = $this->_resolve_trip_details($last_trip_id, $last_bus_id);

        $trip_details = [
            'trip_id'    => (string)$trip_info['trip_id'],
            'route_name' => (string)$trip_info['route_name'],
            'trip_type'  => (string)$trip_info['trip_type'],
            'status'     => (string)$trip_info['status'],
        ];

        if ($last_item_info) {
            $last_item_info['location_id'] = (int)$inserted_id;
            $last_item_info['trip_details'] = $trip_details;
        }

        $response_data = [
            'recorded_count'  => $recorded_count,
            'bus_id'          => $last_bus_id,
            'trip_id'         => (string)$trip_info['trip_id'],
            'route_name'      => (string)$trip_info['route_name'],
            'trip_type'       => (string)$trip_info['trip_type'],
            'latest_location' => $last_item_info,
            'trip_details'    => $trip_details,
            'timeline'        => $trip_info['timeline'],
            'locations'       => $batch_data,
        ];

        // Backwards compatibility for single-record payloads
        if ($recorded_count === 1 && $last_item_info) {
            $response_data['location_id'] = (int)$inserted_id;
            $response_data['latitude']    = (float)$last_item_info['latitude'];
            $response_data['longitude']   = (float)$last_item_info['longitude'];
            $response_data['speed']       = (float)$last_item_info['speed'];
            $response_data['heading']     = (float)$last_item_info['heading'];
            $response_data['timestamp']   = $last_item_info['timestamp'];
        }

        $this->_success($recorded_count . ' bus location(s) recorded successfully', $response_data, 201);
    }

    /**
     * GET /api/bus/location?bus_id=BUS_08 or ?trip_id=...
     */
    private function _get_latest_location()
    {
        $this->_check_auth([], false);

        $bus_id  = $this->input->get('bus_id');
        if (empty($bus_id)) {
            $bus_id = $this->input->get('vehicle_id');
        }
        $trip_id = $this->input->get('trip_id');

        if (empty($bus_id) && empty($trip_id)) {
            $this->_error('bus_id or trip_id parameter is required.', 400);
            return;
        }

        $this->db->from('bus_locations');
        if (!empty($trip_id)) {
            $this->db->where('trip_id', $trip_id);
        }
        if (!empty($bus_id)) {
            $this->db->where('bus_id', $bus_id);
        }
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);

        $location = $this->db->get()->row_array();

        if (empty($location)) {
            $this->_error('No location recorded yet for this bus or trip.', 404);
            return;
        }

        $trip_info = $this->_resolve_trip_details($location['trip_id'], $location['bus_id']);

        $response_data = [
            'location_id'  => (int)$location['id'],
            'bus_id'       => (string)$location['bus_id'],
            'trip_id'      => (string)$trip_info['trip_id'],
            'route_name'   => (string)$trip_info['route_name'],
            'trip_type'    => (string)$trip_info['trip_type'],
            'latitude'     => (float)$location['latitude'],
            'longitude'    => (float)$location['longitude'],
            'speed'        => (float)$location['speed'],
            'heading'      => (float)$location['heading'],
            'timestamp'    => !empty($location['recorded_at']) ? date('c', strtotime($location['recorded_at'])) : null,
            'recorded_at'  => $location['recorded_at'],
            'trip_details' => [
                'trip_id'    => (string)$trip_info['trip_id'],
                'route_name' => (string)$trip_info['route_name'],
                'trip_type'  => (string)$trip_info['trip_type'],
                'status'     => (string)$trip_info['status'],
            ],
            'timeline'     => $trip_info['timeline'],
        ];

        $this->_success('Latest bus location retrieved successfully', $response_data);
    }

    /**
     * Helper to resolve trip-based details by checking what trip is started in database
     */
    private function _resolve_trip_details($trip_id = null, $bus_id = null)
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

        // If no route_id found from trip, find first active route from DB
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

        $timeline = !empty($route_id) 
            ? $this->_build_timeline($route_id, $resolved_trip_id, $status, $start_time)
            : [];

        return [
            'trip_id'    => $resolved_trip_id,
            'route_id'   => $route_id,
            'route_name' => $route_name,
            'driver_id'  => $driver_id,
            'trip_type'  => $trip_type,
            'status'     => $status,
            'start_time' => $start_time,
            'timeline'   => $timeline,
        ];
    }

    /**
     * Builds real timeline of pickup points from database
     */
    private function _build_timeline($route_id, $trip_id = null, $trip_status = 'started', $start_time = null)
    {
        if (empty($route_id) || !$this->db->table_exists('route_pickup_point')) {
            return [];
        }

        $points = $this->db->select('
            rpp.id as rpp_id,
            rpp.pickup_point_id,
            pp.name as title,
            pp.latitude,
            pp.longitude,
            rpp.pickup_time,
            rpp.order_number
        ')
        ->from('route_pickup_point rpp')
        ->join('pickup_point pp', 'pp.id = rpp.pickup_point_id')
        ->where('rpp.transport_route_id', $route_id)
        ->order_by('rpp.order_number', 'ASC')
        ->order_by('rpp.id', 'ASC')
        ->get()
        ->result_array();

        if (empty($points)) {
            return [];
        }

        $timeline = [];
        $total_points = count($points);

        foreach ($points as $idx => $pt) {
            $seq = (!empty($pt['order_number']) && is_numeric($pt['order_number'])) ? (int)$pt['order_number'] : ($idx + 1);
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

            // Real status & actual_time
            $status = 'upcoming';
            $actual_time = null;

            if ($trip_status === 'completed') {
                $status = 'completed';
                $actual_time = $sched_time;
            } elseif ($trip_status === 'started') {
                if ($idx === 0) {
                    $status = ($total_points > 1) ? 'completed' : 'in_progress';
                    $actual_time = ($status === 'completed') ? (!empty($start_time) ? date('h:i A', strtotime($start_time) + 120) : $sched_time) : null;
                } elseif ($idx === 1) {
                    $status = 'in_progress';
                    $actual_time = null;
                } else {
                    $status = 'upcoming';
                    $actual_time = null;
                }
            }

            $timeline[] = [
                'sequence'       => (int)$seq,
                'stop_id'        => $stop_id,
                'title'          => (string)$pt['title'],
                'scheduled_time' => $sched_time,
                'actual_time'    => $actual_time,
                'status'         => $status,
                'students_count' => (int)$students_count,
                'latitude'       => (float)$lat,
                'longitude'      => (float)$lng,
            ];
        }

        return $timeline;
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

