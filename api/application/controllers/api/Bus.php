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
                'description' => 'Retrieves the most recent GPS coordinates, speed, heading, routing progress, next stop ETA, and trip info for a given bus_id or trip_id.',
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
                    '200' => ['description' => 'Latest bus location and telemetry retrieved successfully with routing progress and ETA'],
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
        $trip_info = $this->_resolve_trip_details(
            $last_trip_id,
            $last_bus_id,
            $last_item_info ? (float)$last_item_info['latitude'] : null,
            $last_item_info ? (float)$last_item_info['longitude'] : null,
            $last_item_info ? (float)$last_item_info['speed'] : null,
            $last_item_info ? $last_item_info['timestamp'] : null
        );

        $trip_details = [
            'trip_id'                  => (string)$trip_info['trip_id'],
            'route_name'               => (string)$trip_info['route_name'],
            'trip_type'                => (string)$trip_info['trip_type'],
            'status'                   => (string)$trip_info['status'],
            'routing_progress'         => $trip_info['routing_progress'],
            'next_stop'                => $trip_info['next_stop'],
            'next_stop_name'           => $trip_info['next_stop_name'],
            'time_to_reach_next_stop'  => $trip_info['time_to_reach_next_stop'],
            'estimated_arrival_time'   => $trip_info['estimated_arrival_time'],
            'distance_to_next_stop_km' => $trip_info['distance_to_next_stop_km'],
            'between_stops'            => $trip_info['between_stops'],
            'between_routes'           => $trip_info['between_routes'],
        ];

        if ($last_item_info) {
            $last_item_info['location_id'] = (int)$inserted_id;
            $last_item_info['trip_details'] = $trip_details;
        }

        $response_data = [
            'recorded_count'           => $recorded_count,
            'bus_id'                   => $last_bus_id,
            'trip_id'                  => (string)$trip_info['trip_id'],
            'route_name'               => (string)$trip_info['route_name'],
            'trip_type'                => (string)$trip_info['trip_type'],
            'latest_location'          => $last_item_info,
            'routing_progress'         => $trip_info['routing_progress'],
            'next_stop'                => $trip_info['next_stop'],
            'next_stop_name'           => $trip_info['next_stop_name'],
            'time_to_reach_next_stop'  => $trip_info['time_to_reach_next_stop'],
            'estimated_arrival_time'   => $trip_info['estimated_arrival_time'],
            'distance_to_next_stop_km' => $trip_info['distance_to_next_stop_km'],
            'trip_details'             => $trip_details,
            'pickup_point'             => $trip_info['pickup_point'],
            'drop_point'               => $trip_info['drop_point'],
            'between_stops'            => $trip_info['between_stops'],
            'between_routes'           => $trip_info['between_routes'],
            'timeline'                 => $trip_info['timeline'],
            'locations'                => $batch_data,
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
            $trip_info = $this->_resolve_trip_details($trip_id, $bus_id);
            if (!empty($trip_info['trip_id']) || !empty($trip_info['route_id'])) {
                $first_stop = !empty($trip_info['timeline'][0]) ? $trip_info['timeline'][0] : null;
                $location = [
                    'id'          => 0,
                    'bus_id'      => (string)$bus_id,
                    'trip_id'     => (string)$trip_info['trip_id'],
                    'latitude'    => !empty($first_stop['latitude']) ? (float)$first_stop['latitude'] : 0.0,
                    'longitude'   => !empty($first_stop['longitude']) ? (float)$first_stop['longitude'] : 0.0,
                    'speed'       => 0.0,
                    'heading'     => 0.0,
                    'recorded_at' => date('Y-m-d H:i:s'),
                ];
            } else {
                $this->_error('No location recorded yet for this bus or trip.', 404);
                return;
            }
        }

        $trip_info = $this->_resolve_trip_details(
            $location['trip_id'],
            $location['bus_id'],
            (float)$location['latitude'],
            (float)$location['longitude'],
            isset($location['speed']) ? (float)$location['speed'] : null,
            $location['recorded_at'] ?? null
        );

        $trip_details = [
            'trip_id'                  => (string)$trip_info['trip_id'],
            'route_name'               => (string)$trip_info['route_name'],
            'trip_type'                => (string)$trip_info['trip_type'],
            'status'                   => (string)$trip_info['status'],
            'routing_progress'         => $trip_info['routing_progress'],
            'next_stop'                => $trip_info['next_stop'],
            'next_stop_name'           => $trip_info['next_stop_name'],
            'time_to_reach_next_stop'  => $trip_info['time_to_reach_next_stop'],
            'estimated_arrival_time'   => $trip_info['estimated_arrival_time'],
            'distance_to_next_stop_km' => $trip_info['distance_to_next_stop_km'],
            'between_stops'            => $trip_info['between_stops'],
            'between_routes'           => $trip_info['between_routes'],
        ];

        $response_data = [
            'bus_id'                   => (string)$location['bus_id'],
            'trip_id'                  => (string)$trip_info['trip_id'],
            'route_name'               => (string)$trip_info['route_name'],
            'trip_type'                => (string)$trip_info['trip_type'],
            'latitude'                 => (float)$location['latitude'],
            'longitude'                => (float)$location['longitude'],
            'speed'                    => (float)$location['speed'],
            'heading'                  => (float)$location['heading'],
            'recorded_at'              => $location['recorded_at'],
            'routing_progress'         => $trip_info['routing_progress'],
            'next_stop'                => $trip_info['next_stop'],
            'next_stop_name'           => $trip_info['next_stop_name'],
            'time_to_reach_next_stop'  => $trip_info['time_to_reach_next_stop'],
            'estimated_arrival_time'   => $trip_info['estimated_arrival_time'],
            'distance_to_next_stop_km' => $trip_info['distance_to_next_stop_km'],
            'trip_details'             => $trip_details,
            'pickup_point'             => $trip_info['pickup_point'],
            'drop_point'               => $trip_info['drop_point'],
            'between_stops'            => $trip_info['between_stops'],
            'between_routes'           => $trip_info['between_routes'],
            'timeline'                 => $trip_info['timeline'],
        ];

        $this->_success('Latest bus location retrieved successfully', $response_data);
    }

    /**
     * Helper to resolve trip-based details by checking what trip is started in database
     */
    private function _resolve_trip_details($trip_id = null, $bus_id = null, $bus_lat = null, $bus_lng = null, $bus_speed = null, $recorded_at = null)
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

        // 4. Check bus_locations for latest recorded trip_id for this bus_id
        if (empty($trip) && !empty($bus_id) && $this->db->table_exists('bus_locations') && $this->db->table_exists('trips')) {
            $latest_loc = $this->db->select('trip_id')
                ->where('bus_id', $bus_id)
                ->where('trip_id IS NOT NULL', null, false)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get('bus_locations')
                ->row_array();
            if (!empty($latest_loc['trip_id'])) {
                $trip = $this->db->where('id', $latest_loc['trip_id'])->get('trips')->row_array();
            }
        }

        // 5. Check numeric extracted ID (e.g. BUS_08 -> 8)
        $extracted_id = !empty($bus_id) ? preg_replace('/[^0-9]/', '', (string)$bus_id) : '';
        if (empty($trip) && !empty($extracted_id) && $this->db->table_exists('trips')) {
            $trip = $this->db->where_in('bus_id', [(string)$extracted_id, (int)$extracted_id])
                ->where('status', 'started')
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
            if (empty($trip)) {
                $trip = $this->db->where_in('bus_id', [(string)$extracted_id, (int)$extracted_id])
                    ->order_by('id', 'DESC')
                    ->get('trips')
                    ->row_array();
            }
        }

        // 6. Fallback: check latest trip for this bus_id
        if (empty($trip) && !empty($bus_id) && $this->db->table_exists('trips')) {
            $trip = $this->db->where('bus_id', $bus_id)
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        }

        // 7. Fallback: find any active started trip in the system
        if (empty($trip) && $this->db->table_exists('trips')) {
            $trip = $this->db->where('status', 'started')
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        }

        // 8. Fallback: find latest trip in the system
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

        // If trip route has no stops, or no route_id found, resolve route from vehicle_routes
        $route_stops_count = (!empty($route_id) && $this->db->table_exists('route_pickup_point'))
            ? $this->db->where('transport_route_id', $route_id)->count_all_results('route_pickup_point')
            : 0;

        if ($route_stops_count <= 1 && $this->db->table_exists('vehicle_routes')) {
            $vr = null;
            // Try vehicle lookup by vehicle_no or id
            if (!empty($bus_id) && $this->db->table_exists('vehicles')) {
                $veh = $this->db->where('vehicle_no', $bus_id)
                    ->or_where('id', $bus_id)
                    ->or_where('registration_number', $bus_id)
                    ->get('vehicles')
                    ->row_array();
                if (!empty($veh['id'])) {
                    $vr = $this->db->where('vehicle_id', $veh['id'])->order_by('id', 'DESC')->get('vehicle_routes')->row_array();
                }
            }

            if (empty($vr) && !empty($bus_id)) {
                $vr = $this->db->where('vehicle_id', $bus_id)->order_by('id', 'DESC')->get('vehicle_routes')->row_array();
            }

            if (empty($vr) && !empty($extracted_id)) {
                $vr = $this->db->where('vehicle_id', (int)$extracted_id)->order_by('id', 'DESC')->get('vehicle_routes')->row_array();
            }

            if (empty($vr) && !empty($trip['bus_id'])) {
                $vr = $this->db->where('vehicle_id', $trip['bus_id'])->order_by('id', 'DESC')->get('vehicle_routes')->row_array();
            }

            if (!empty($vr['route_id'])) {
                $vr_stops = $this->db->where('transport_route_id', $vr['route_id'])->count_all_results('route_pickup_point');
                if ($vr_stops > $route_stops_count) {
                    $route_id = (int)$vr['route_id'];
                }
            }
        }

        // Fallback: If still no route with stops, find the active route that has the most stops defined
        if ($route_stops_count <= 1 && $this->db->table_exists('route_pickup_point')) {
            $best_route = $this->db->select('transport_route_id, COUNT(*) as c')
                ->group_by('transport_route_id')
                ->having('c > 1')
                ->order_by('c', 'DESC')
                ->limit(1)
                ->get('route_pickup_point')
                ->row_array();
            if (!empty($best_route['transport_route_id'])) {
                $route_id = (int)$best_route['transport_route_id'];
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
            ? $this->_build_timeline($route_id, $resolved_trip_id, $status, $start_time, $bus_lat, $bus_lng, $bus_speed, $recorded_at)
            : ['timeline' => [], 'pickup_point' => null, 'drop_point' => null, 'between_stops' => [], 'between_routes' => []];

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

        $routing_progress = $this->_calculate_routing_progress($timeline, $status, $next_stop);

        return [
            'trip_id'                  => $resolved_trip_id,
            'route_id'                 => $route_id,
            'route_name'               => $route_name,
            'driver_id'                => $driver_id,
            'trip_type'                => $trip_type,
            'status'                   => $status,
            'start_time'               => $start_time,
            'next_stop'                => $next_stop,
            'next_stop_name'           => $next_stop_name,
            'time_to_reach_next_stop'  => $routing_progress['time_to_reach_next_stop'],
            'estimated_arrival_time'   => $routing_progress['estimated_arrival_time'],
            'distance_to_next_stop_km' => $routing_progress['distance_to_next_stop_km'],
            'routing_progress'         => $routing_progress,
            'pickup_point'             => $timeline_info['pickup_point'],
            'drop_point'               => $timeline_info['drop_point'],
            'between_stops'            => $timeline_info['between_stops'],
            'between_routes'           => $timeline_info['between_routes'],
            'timeline'                 => $timeline,
        ];
    }

    /**
     * Builds real timeline of pickup points from database with live routing progress and ETA
     */
    private function _build_timeline($route_id, $trip_id = null, $trip_status = 'started', $start_time = null, $bus_lat = null, $bus_lng = null, $bus_speed = null, $recorded_at = null)
    {
        $empty_result = [
            'timeline'       => [],
            'pickup_point'   => null,
            'drop_point'     => null,
            'between_stops'  => [],
            'between_routes' => [],
        ];

        if (empty($route_id) || !$this->db->table_exists('route_pickup_point')) {
            return $empty_result;
        }

        $has_point_type = $this->db->field_exists('point_type', 'route_pickup_point');
        $has_order_num  = $this->db->field_exists('order_number', 'route_pickup_point');

        $select_fields = '
            rpp.id as rpp_id,
            rpp.pickup_point_id,
            pp.name as title,
            pp.latitude,
            pp.longitude,
            rpp.pickup_time';
        if ($has_order_num) {
            $select_fields .= ', rpp.order_number';
        }
        if ($has_point_type) {
            $select_fields .= ', rpp.point_type';
        }

        $this->db->select($select_fields)
            ->from('route_pickup_point rpp')
            ->join('pickup_point pp', 'pp.id = rpp.pickup_point_id')
            ->where('rpp.transport_route_id', $route_id);

        if ($has_order_num) {
            $this->db->order_by('rpp.order_number', 'ASC');
        }
        $points = $this->db->order_by('rpp.id', 'ASC')->get()->result_array();

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

        // Setup base time for ETA calculation
        $base_time = time();
        if (!empty($recorded_at)) {
            $rec_parsed = strtotime($recorded_at);
            if ($rec_parsed !== false && abs(time() - $rec_parsed) <= 3600) {
                $base_time = $rec_parsed;
            }
        }

        // Effective transit speed in km/h (school buses in urban traffic average 20-30 km/h)
        $effective_speed = ($bus_speed !== null && (float)$bus_speed >= 10.0 && (float)$bus_speed <= 120.0)
            ? (float)$bus_speed
            : 25.0;

        // Check if bus GPS is in the general vicinity of the route (within 100km)
        $bus_is_nearby = false;
        if (!empty($bus_lat) && !empty($bus_lng) && !empty($parsed_points[0]['latitude'])) {
            $dist_to_route = $this->_calculate_distance_km($bus_lat, $bus_lng, $parsed_points[0]['latitude'], $parsed_points[0]['longitude']);
            if ($dist_to_route <= 100.0) {
                $bus_is_nearby = true;
            }
        }

        $timeline = [];
        $cumulative_road_km = 0.0;
        $cumulative_minutes = 0;

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

            // Calculate distance & estimated time to reach
            $dist_km = null;
            $dist_m = null;
            $dist_txt = null;
            $time_reach = null;
            $eta_mins = null;
            $eta_clock = null;

            if ($status === 'completed') {
                $dist_km = 0.0;
                $dist_m = 0;
                $dist_txt = '0 m';
                $time_reach = 'Reached';
                $eta_mins = 0;
                $eta_clock = !empty($actual_time) ? $actual_time : $p['sched_time'];
            } elseif ($status === 'in_progress') {
                if ($bus_is_nearby && !empty($p['latitude']) && !empty($p['longitude'])) {
                    $direct_dist = $this->_calculate_distance_km($bus_lat, $bus_lng, $p['latitude'], $p['longitude']);
                    $leg_road_km = round($direct_dist * 1.25, 2);
                } elseif ($i > 0 && !empty($parsed_points[$i - 1]['latitude']) && !empty($p['latitude'])) {
                    $leg_dist = $this->_calculate_distance_km(
                        $parsed_points[$i - 1]['latitude'],
                        $parsed_points[$i - 1]['longitude'],
                        $p['latitude'],
                        $p['longitude']
                    );
                    $leg_road_km = round($leg_dist * 1.25, 2);
                } else {
                    $leg_road_km = 2.5;
                }

                $leg_mins = max(1, (int)round(($leg_road_km / $effective_speed) * 60));
                $cumulative_road_km = $leg_road_km;
                $cumulative_minutes = $leg_mins;

                $dist_km = round($cumulative_road_km, 2);
                $dist_m = (int)round($dist_km * 1000);
                $dist_txt = ($dist_km < 1.0) ? ($dist_m . ' m') : ($dist_km . ' km');
                $eta_mins = $cumulative_minutes;
                $time_reach = $eta_mins . ' ' . ($eta_mins === 1 ? 'min' : 'mins');
                $eta_clock = date('h:i A', $base_time + ($cumulative_minutes * 60));
            } else { // upcoming
                if ($i > 0 && !empty($parsed_points[$i - 1]['latitude']) && !empty($p['latitude'])) {
                    $leg_dist = $this->_calculate_distance_km(
                        $parsed_points[$i - 1]['latitude'],
                        $parsed_points[$i - 1]['longitude'],
                        $p['latitude'],
                        $p['longitude']
                    );
                    $leg_road_km = round($leg_dist * 1.25, 2);
                } else {
                    $leg_road_km = 3.0;
                }

                $dwell_time = 1; // 1 min boarding dwell time per stop
                $leg_mins = max(1, (int)round(($leg_road_km / $effective_speed) * 60)) + $dwell_time;

                $cumulative_road_km += $leg_road_km;
                $cumulative_minutes += $leg_mins;

                $dist_km = round($cumulative_road_km, 2);
                $dist_m = (int)round($dist_km * 1000);
                $dist_txt = ($dist_km < 1.0) ? ($dist_m . ' m') : ($dist_km . ' km');
                $eta_mins = $cumulative_minutes;
                $time_reach = $eta_mins . ' mins';
                $eta_clock = date('h:i A', $base_time + ($cumulative_minutes * 60));
            }

            $timeline[] = [
                'sequence'               => (int)$p['seq'],
                'stop_id'                => $p['stop_id'],
                'point_type'             => $p['point_type'],
                'title'                  => $p['title'],
                'latitude'               => (float)$p['latitude'],
                'longitude'              => (float)$p['longitude'],
                'status'                 => $status,
                'scheduled_time'         => $p['sched_time'],
                'actual_time'            => $actual_time,
                'students_count'         => (int)$p['students_count'],
                'distance_km'            => $dist_km !== null ? (float)$dist_km : null,
                'distance_meters'        => $dist_m,
                'distance_text'          => $dist_txt,
                'time_to_reach'          => $time_reach,
                'eta_minutes'            => $eta_mins,
                'estimated_arrival_time' => $eta_clock,
            ];
        }

        $pickup_point = !empty($timeline[0]) ? $timeline[0] : null;
        $drop_point   = (count($timeline) > 1) ? $timeline[count($timeline) - 1] : null;
        $between_stops = [];

        foreach ($timeline as $idx => $st) {
            $is_pickup = ($idx === 0) || (isset($st['point_type']) && $st['point_type'] === 'pickup');
            $is_drop   = (count($timeline) > 1 && $idx === count($timeline) - 1) || (isset($st['point_type']) && $st['point_type'] === 'drop');

            if (isset($st['point_type']) && $st['point_type'] === 'stop') {
                $between_stops[] = $st;
            } elseif (!$is_pickup && !$is_drop) {
                $between_stops[] = $st;
            }
        }

        return [
            'timeline'       => $timeline,
            'pickup_point'   => $pickup_point,
            'drop_point'     => $drop_point,
            'between_stops'  => $between_stops,
            'between_routes' => $between_stops,
        ];
    }

    /**
     * Calculates overall routing progress metrics and next stop ETA
     */
    private function _calculate_routing_progress($timeline, $trip_status = 'started', $next_stop = null)
    {
        $total_stops = count($timeline);
        $completed_stops = 0;
        $remaining_stops = 0;
        $current_seq = 1;

        foreach ($timeline as $st) {
            if ($st['status'] === 'completed') {
                $completed_stops++;
            } else {
                $remaining_stops++;
            }
            if ($st['status'] === 'in_progress') {
                $current_seq = (int)$st['sequence'];
            }
        }

        if ($total_stops === 0) {
            return [
                'status'                     => $trip_status,
                'progress_percentage'        => 0,
                'total_stops'                => 0,
                'completed_stops'            => 0,
                'remaining_stops'            => 0,
                'current_sequence'           => 0,
                'next_stop_id'               => null,
                'next_stop_name'             => null,
                'distance_to_next_stop_km'   => null,
                'distance_to_next_stop_m'    => null,
                'distance_to_next_stop_text' => null,
                'time_to_reach_next_stop'    => null,
                'eta_minutes'                => null,
                'estimated_arrival_time'     => null,
            ];
        }

        $pct = 0;
        if ($trip_status === 'completed') {
            $pct = 100;
            $completed_stops = $total_stops;
            $remaining_stops = 0;
        } elseif ($total_stops > 0) {
            $pct = min(99, (int)round(($completed_stops / $total_stops) * 100));
            if ($pct === 0 && $completed_stops > 0) {
                $pct = (int)round(($completed_stops / $total_stops) * 100);
            }
        }

        $next_id    = !empty($next_stop['stop_id']) ? $next_stop['stop_id'] : null;
        $next_name  = !empty($next_stop['title']) ? $next_stop['title'] : null;
        $dist_km    = isset($next_stop['distance_km']) ? (float)$next_stop['distance_km'] : null;
        $dist_m     = isset($next_stop['distance_meters']) ? (int)$next_stop['distance_meters'] : null;
        $dist_txt   = isset($next_stop['distance_text']) ? $next_stop['distance_text'] : null;
        $time_reach = isset($next_stop['time_to_reach']) ? $next_stop['time_to_reach'] : null;
        $eta_min    = isset($next_stop['eta_minutes']) ? (int)$next_stop['eta_minutes'] : null;
        $eta_time   = isset($next_stop['estimated_arrival_time']) ? $next_stop['estimated_arrival_time'] : null;

        return [
            'status'                     => $trip_status,
            'progress_percentage'        => (int)$pct,
            'total_stops'                => (int)$total_stops,
            'completed_stops'            => (int)$completed_stops,
            'remaining_stops'            => (int)$remaining_stops,
            'current_sequence'           => (int)$current_seq,
            'next_stop_id'               => $next_id,
            'next_stop_name'             => $next_name,
            'distance_to_next_stop_km'   => $dist_km,
            'distance_to_next_stop_m'    => $dist_m,
            'distance_to_next_stop_text' => $dist_txt,
            'time_to_reach_next_stop'    => $time_reach,
            'eta_minutes'                => $eta_min,
            'estimated_arrival_time'     => $eta_time,
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

