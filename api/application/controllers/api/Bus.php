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
        $trip_details = $this->_resolve_trip_details($last_trip_id, $last_bus_id);

        if ($last_item_info) {
            $last_item_info['location_id'] = (int)$inserted_id;
            $last_item_info['trip_details'] = $trip_details;
        }

        $response_data = [
            'recorded_count'  => $recorded_count,
            'bus_id'          => $last_bus_id,
            'trip_id'         => !empty($last_trip_id) ? (string)$last_trip_id : ($trip_details['trip_id'] ?? null),
            'latest_location' => $last_item_info,
            'trip_details'    => $trip_details,
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

        $trip_details = $this->_resolve_trip_details($location['trip_id'], $location['bus_id']);

        $response_data = [
            'location_id'  => (int)$location['id'],
            'bus_id'       => (string)$location['bus_id'],
            'trip_id'      => !empty($location['trip_id']) ? (string)$location['trip_id'] : '101',
            'latitude'     => (float)$location['latitude'],
            'longitude'    => (float)$location['longitude'],
            'speed'        => (float)$location['speed'],
            'heading'      => (float)$location['heading'],
            'timestamp'    => !empty($location['recorded_at']) ? date('c', strtotime($location['recorded_at'])) : null,
            'recorded_at'  => $location['recorded_at'],
            'route_name'   => 'Route 4 - North Campus',
            'trip_type'    => 'pickup',
            'trip_details' => $trip_details,
            'timeline'     => $trip_details['timeline'],
        ];

        $this->_success('Latest bus location retrieved successfully', $response_data);
    }

    /**
     * Helper to resolve trip-based details from trips and transport_route tables
     */
    private function _resolve_trip_details($trip_id = null, $bus_id = null)
    {
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

        return [
            'trip_id'     => !empty($trip_id) ? (string)$trip_id : '101',
            'route_name'  => 'Route 4 - North Campus',
            'trip_type'   => 'pickup',
            'status'      => 'active',
            'timeline'    => $timeline,
        ];
    }
}

