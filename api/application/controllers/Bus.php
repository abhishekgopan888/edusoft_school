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
                'summary' => 'Record live bus location and GPS telemetry',
                'description' => 'Receives live GPS coordinates (latitude, longitude), speed, heading, and timestamp for a bus during an active trip. Includes trip-based details in the response.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
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
                ],
                'responses' => [
                    '201' => ['description' => 'Bus location recorded successfully with trip details'],
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
     */
    private function _record_location()
    {
        $this->_check_auth([], false);

        $raw = file_get_contents('php://input');
        $params = json_decode($raw, true);
        if (!is_array($params)) {
            $params = $this->input->post();
        }

        $bus_id    = $params['bus_id'] ?? $params['vehicle_id'] ?? $params['bus_number'] ?? null;
        $trip_id   = $params['trip_id'] ?? null;
        $latitude  = $params['latitude'] ?? null;
        $longitude = $params['longitude'] ?? null;
        $speed     = isset($params['speed']) ? (float)$params['speed'] : 0.0;
        $heading   = isset($params['heading']) ? (float)$params['heading'] : 0.0;
        $timestamp = $params['timestamp'] ?? null;

        if (empty($bus_id) || $latitude === null || $longitude === null) {
            $this->_error('bus_id, latitude, and longitude are required.', 400);
            return;
        }

        // Format recorded_at datetime
        $recorded_at = null;
        if (!empty($timestamp)) {
            $parsed_time = strtotime($timestamp);
            if ($parsed_time !== false) {
                $recorded_at = date('Y-m-d H:i:s', $parsed_time);
            }
        }
        if (empty($recorded_at)) {
            $recorded_at = date('Y-m-d H:i:s');
        }

        // Save location
        $insert_data = [
            'bus_id'      => (string)$bus_id,
            'trip_id'     => !empty($trip_id) ? (string)$trip_id : null,
            'latitude'    => (float)$latitude,
            'longitude'   => (float)$longitude,
            'speed'       => $speed,
            'heading'     => $heading,
            'recorded_at' => $recorded_at,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('bus_locations', $insert_data);
        $location_id = $this->db->insert_id();

        // Fetch trip details based on trip_id or bus_id
        $trip_details = $this->_resolve_trip_details($trip_id, $bus_id);

        $response_data = [
            'location_id'  => (int)$location_id,
            'bus_id'       => (string)$bus_id,
            'trip_id'      => !empty($trip_id) ? (string)$trip_id : ($trip_details['trip_id'] ?? null),
            'latitude'     => (float)$latitude,
            'longitude'    => (float)$longitude,
            'speed'        => (float)$speed,
            'heading'      => (float)$heading,
            'timestamp'    => !empty($timestamp) ? $timestamp : date('c', strtotime($recorded_at)),
            'trip_details' => $trip_details,
        ];

        $this->_success('Bus location recorded successfully', $response_data, 201);
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

        $trip_details = $this->_resolve_trip_details($location['trip_id'], $location['bus_id']);

        $response_data = [
            'location_id'  => (int)$location['id'],
            'bus_id'       => (string)$location['bus_id'],
            'trip_id'      => $location['trip_id'],
            'latitude'     => (float)$location['latitude'],
            'longitude'    => (float)$location['longitude'],
            'speed'        => (float)$location['speed'],
            'heading'      => (float)$location['heading'],
            'timestamp'    => !empty($location['recorded_at']) ? date('c', strtotime($location['recorded_at'])) : null,
            'recorded_at'  => $location['recorded_at'],
            'trip_details' => $trip_details,
        ];

        $this->_success('Latest bus location retrieved successfully', $response_data);
    }

    /**
     * Helper to resolve trip-based details from trips and transport_route tables
     */
    private function _resolve_trip_details($trip_id = null, $bus_id = null)
    {
        $trip = null;

        if (!empty($trip_id)) {
            $trip = $this->db->where('id', $trip_id)
                ->get('trips')
                ->row_array();
        }

        if (empty($trip) && !empty($bus_id)) {
            $trip = $this->db->where('bus_id', $bus_id)
                ->where('status', 'started')
                ->order_by('id', 'DESC')
                ->get('trips')
                ->row_array();
        }

        if (empty($trip)) {
            return !empty($trip_id) ? ['trip_id' => $trip_id, 'status' => 'active'] : null;
        }

        $route_title = null;
        if (!empty($trip['route_id'])) {
            $route = $this->db->select('route_title')
                ->where('id', $trip['route_id'])
                ->get('transport_route')
                ->row();
            if ($route) {
                $route_title = $route->route_title;
            }
        }

        return [
            'trip_id'     => $trip['id'],
            'route_id'    => $trip['route_id'],
            'route_title' => $route_title,
            'driver_id'   => $trip['driver_id'],
            'trip_type'   => $trip['trip_type'],
            'status'      => $trip['status'],
            'start_time'  => $trip['start_time'],
        ];
    }
}

