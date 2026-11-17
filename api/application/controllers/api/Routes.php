<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Routes extends Base
{
    public $swagger_meta = [
        'tag' => 'Route Management',
        'description' => 'Endpoints for managing and retrieving school transport routes (from /admin/route), assigned vehicles/buses, and pickup points. Requires X-API-Key or Bearer token.',
        'methods' => [
            'index' => [
                'path' => '/api/routes',
                'summary' => 'Get transport routes with vehicle and pickup point details',
                'description' => 'Retrieves school transport routes matching the admin route management page (/admin/route). Supports filtering by route_id, vehicle_id (or bus_id), and pickup_point_id.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'bus_number',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Bus Number from auth login (e.g. "KL 38 M 0990", "KL 17 Y 8989"). Alias: bus_no, vehicle_no',
                        'schema' => ['type' => 'string', 'example' => 'KL 38 M 0990']
                    ],
                    [
                        'name' => 'vehicle_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Vehicle / Bus ID (e.g. 2, 5) or Bus Number. Alias: vehicleid, bus_id, busid',
                        'schema' => ['type' => 'string', 'example' => '2']
                    ],
                    [
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Route ID (optional). Alias: routeid, id',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'pickup_point_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Pickup Point ID (optional). Alias: pickuppointid',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'pickup_point_name',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter routes by Pickup Point Name (optional). Case-insensitive search.',
                        'schema' => ['type' => 'string', 'example' => 'Kanyakumarii']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'List of routes with assigned vehicles and pickup points retrieved successfully'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing authentication token / API key']
                ]
            ],
            'by_bus' => [
                'path' => '/api/routes/by_bus/{bus_id}',
                'summary' => 'Get routes by specific bus / vehicle ID',
                'description' => 'Retrieves all routes, assigned bus details, driver, attender, and pickup points for a specific bus or vehicle ID.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'bus_id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'Bus / Vehicle ID (e.g. 2, 3, 5)',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Routes for the specified bus retrieved successfully'],
                    '400' => ['description' => 'Invalid or missing bus ID'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'details' => [
                'path' => '/api/routes/details',
                'summary' => 'Get single route details by route ID',
                'description' => 'Retrieves full details of a specific route including assigned bus and pickup points by route ID.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Route ID (optional)',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Route details retrieved successfully'],
                    '404' => ['description' => 'Route not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
        $this->load->model('vehroute_model');
    }

    /**
     * GET /api/routes
     * Query parameters: bus_number, vehicle_id, route_id, pickup_point_id, pickup_point_name (and aliases)
     */
    public function index()
    {
        // Public/optional authentication so Swagger UI and client apps can query directly
        $this->_check_auth([], false);

        $vehicle_id = $this->input->get('vehicle_id');
        if ($vehicle_id === null || $vehicle_id === '') {
            $vehicle_id = $this->input->get('vehicleid');
        }
        if ($vehicle_id === null || $vehicle_id === '') {
            $vehicle_id = $this->input->get('bus_id');
        }
        if ($vehicle_id === null || $vehicle_id === '') {
            $vehicle_id = $this->input->get('busid');
        }

        $bus_number = $this->input->get('bus_number');
        if ($bus_number === null || $bus_number === '') {
            $bus_number = $this->input->get('bus_no');
        }
        if ($bus_number === null || $bus_number === '') {
            $bus_number = $this->input->get('vehicle_no');
        }
        if ($bus_number === null || $bus_number === '') {
            $bus_number = $this->input->get('vehicleno');
        }

        // If vehicle_id is provided as a non-numeric string (e.g. "KL 38 M 0990"), treat it as bus_number
        if (!empty($vehicle_id) && !is_numeric($vehicle_id) && empty($bus_number)) {
            $bus_number = $vehicle_id;
            $vehicle_id = null;
        }

        $route_id = $this->input->get('route_id');
        if ($route_id === null || $route_id === '') {
            $route_id = $this->input->get('routeid');
        }
        if ($route_id === null || $route_id === '') {
            $route_id = $this->input->get('id');
        }

        $pickup_point_id = $this->input->get('pickup_point_id');
        if ($pickup_point_id === null || $pickup_point_id === '') {
            $pickup_point_id = $this->input->get('pickuppointid');
        }
        if (($pickup_point_id === null || $pickup_point_id === '') && is_numeric($this->input->get('pickup_point'))) {
            $pickup_point_id = $this->input->get('pickup_point');
        }

        $pickup_point_name = $this->input->get('pickup_point_name');
        if (empty($pickup_point_name) && !is_numeric($this->input->get('pickup_point'))) {
            $pickup_point_name = $this->input->get('pickup_point');
        }

        $routes = $this->_get_routes_data($vehicle_id, $route_id, $pickup_point_id, $pickup_point_name, $bus_number);

        $filters = [];
        if (!empty($bus_number)) {
            $filters[] = 'bus number "' . $bus_number . '"';
        }
        if (!empty($route_id)) {
            $filters[] = 'route ID ' . $route_id;
        }
        if (!empty($vehicle_id)) {
            $filters[] = 'vehicle ID ' . $vehicle_id;
        }
        if (!empty($pickup_point_id)) {
            $filters[] = 'pickup point ID ' . $pickup_point_id;
        }
        if (!empty($pickup_point_name)) {
            $filters[] = 'pickup point "' . $pickup_point_name . '"';
        }

        $msg = !empty($filters) 
            ? 'Routes filtered by ' . implode(', ', $filters) . ' retrieved successfully' 
            : 'Routes retrieved successfully';

        $this->_success($msg, $routes);
    }

    /**
     * GET /api/routes/by_bus/{bus_id}
     */
    public function by_bus($bus_id = null)
    {
        $this->_check_auth([], false);

        if ($bus_id === null || $bus_id === '') {
            $bus_id = $this->input->get('bus_id');
        }
        if ($bus_id === null || $bus_id === '') {
            $bus_id = $this->input->get('busid');
        }
        if ($bus_id === null || $bus_id === '') {
            $bus_id = $this->input->get('vehicle_id');
        }

        $bus_number = null;
        if (!empty($bus_id) && !is_numeric($bus_id)) {
            $bus_number = $bus_id;
            $bus_id = null;
        }

        $routes = $this->_get_routes_data($bus_id, null, null, null, $bus_number);

        $this->_success('Routes retrieved successfully', $routes);
    }

    /**
     * GET /api/routes/details?id={route_id}
     */
    public function details()
    {
        $this->_check_auth([], false);

        $id = $this->input->get('id');
        if (empty($id)) {
            $id = $this->input->get('route_id');
        }
        if (empty($id)) {
            $id = $this->input->get('routeid');
        }

        if (empty($id)) {
            $routes = $this->_get_routes_data(null, null, null, null, null);
            $this->_success('Routes retrieved successfully', $routes);
            return;
        }

        $routes = $this->_get_routes_data(null, $id, null, null, null);
        if (empty($routes)) {
            $this->_error('Route not found', 404);
            return;
        }

        $this->_success('Route details retrieved successfully', $routes[0]);
    }

    /**
     * GET /api/routes/timeline
     * Query: route_id (optional), bus_id (optional)
     * Returns standalone route stop timeline in requested format
     */
    public function timeline()
    {
        $this->_check_auth([], false);

        $route_id   = $this->input->get('route_id') ?? $this->input->get('routeid') ?? $this->input->get('id');
        $bus_id     = $this->input->get('bus_id') ?? $this->input->get('vehicle_id');

        // If route_id is missing, try to resolve via bus_id / vehicle_routes
        if (empty($route_id) && !empty($bus_id) && $this->db->table_exists('vehicle_routes')) {
            $vr = $this->db->where('vehicle_id', $bus_id)->get('vehicle_routes')->row_array();
            if (!empty($vr['route_id'])) {
                $route_id = (int)$vr['route_id'];
            }
        }
        if (empty($route_id) && $this->db->table_exists('transport_route')) {
            $r = $this->db->select('id')->order_by('id', 'ASC')->limit(1)->get('transport_route')->row_array();
            if (!empty($r['id'])) {
                $route_id = (int)$r['id'];
            }
        }

        $route_name = '';
        if (!empty($route_id) && $this->db->table_exists('transport_route')) {
            $r = $this->db->where('id', $route_id)->get('transport_route')->row_array();
            if (!empty($r['route_title'])) {
                $route_name = $r['route_title'];
            }
        }

        $route_data = $this->_build_route_timeline($route_id, $bus_id);

        $response_data = [
            'route_id'       => !empty($route_id) ? (int)$route_id : 0,
            'route_name'     => (string)$route_name,
            'next_stop'      => $route_data['next_stop'],
            'next_stop_name' => $route_data['next_stop_name'],
            'pickup_point'   => $route_data['pickup_point'],
            'drop_point'     => $route_data['drop_point'],
            'between_stops'  => $route_data['between_stops'],
            'timeline'       => $route_data['timeline'],
        ];

        $this->_success('Route timeline retrieved successfully', $response_data);
    }

    /**
     * Query routes with vehicle and pickup points
     */
    private function _get_routes_data($vehicle_id = null, $route_id = null, $pickup_point_id = null, $pickup_point_name = null, $bus_number = null)
    {
        $this->db->select('
            transport_route.id as route_id,
            transport_route.route_title,
            transport_route.no_of_vehicle,
            transport_route.note,
            vehicle_routes.id as vehicle_route_id,
            vehicle_routes.vehicle_id,
            vehicle_routes.vehicle_id as bus_id,
            vehicles.vehicle_no,
            vehicles.vehicle_no as bus_no,
            vehicles.vehicle_no as bus_number,
            vehicles.vehicle_model,
            vehicles.vehicle_model as bus_model,
            vehicles.registration_number,
            vehicles.chasis_number,
            vehicles.max_seating_capacity,
            COALESCE(vr_drivers.driver_name, v_drivers.driver_name) as driver_name,
            COALESCE(vr_drivers.contact, v_drivers.contact) as driver_contact,
            COALESCE(vr_drivers.licence, v_drivers.licence) as driver_licence,
            attenders.id as attender_id,
            attenders.attender_name
        ')
        ->from('transport_route')
        ->join('vehicle_routes', 'vehicle_routes.route_id = transport_route.id', 'left')
        ->join('vehicles', 'vehicles.id = vehicle_routes.vehicle_id', 'left')
        ->join('drivers as vr_drivers', 'vr_drivers.id = vehicle_routes.driver_id', 'left')
        ->join('drivers as v_drivers', 'v_drivers.id = vehicles.driver_id', 'left')
        ->join('attenders', 'attenders.id = vehicle_routes.attender_id', 'left');

        if (!empty($vehicle_id) && is_numeric($vehicle_id)) {
            $this->db->where('vehicle_routes.vehicle_id', (int)$vehicle_id);
        }

        if (!empty($bus_number)) {
            $clean_bus = preg_replace('/\s+/', '', $bus_number);
            $escaped_bus = $this->db->escape_str(trim($bus_number));
            $escaped_clean = $this->db->escape_str($clean_bus);
            $this->db->where("(vehicles.vehicle_no = '{$escaped_bus}' OR REPLACE(vehicles.vehicle_no, ' ', '') = '{$escaped_clean}' OR vehicles.vehicle_no LIKE '%{$escaped_bus}%')", null, false);
        }

        if (!empty($route_id)) {
            $this->db->where('transport_route.id', $route_id);
        }

        if (!empty($pickup_point_id)) {
            $this->db->where("transport_route.id IN (SELECT DISTINCT transport_route_id FROM route_pickup_point WHERE pickup_point_id = " . (int)$pickup_point_id . ")", null, false);
        }

        if (!empty($pickup_point_name)) {
            $escaped_name = $this->db->escape_like_str($pickup_point_name);
            $this->db->where("transport_route.id IN (SELECT DISTINCT rpp.transport_route_id FROM route_pickup_point rpp JOIN pickup_point pp ON pp.id = rpp.pickup_point_id WHERE pp.name LIKE '%{$escaped_name}%')", null, false);
        }

        $this->db->order_by('transport_route.id', 'ASC');
        $routes = $this->db->get()->result_array();

        if (empty($routes)) {
            return [];
        }

        $result = [];
        foreach ($routes as $route) {
            $r_id = $route['route_id'];

            // Fetch pickup points along this route
            $this->db->select('
                route_pickup_point.id as route_pickup_point_id,
                route_pickup_point.pickup_point_id,
                pickup_point.name as pickup_point_name,
                pickup_point.latitude,
                pickup_point.longitude,
                route_pickup_point.fees,
                route_pickup_point.destination_distance,
                route_pickup_point.pickup_time,
                route_pickup_point.order_number
            ')
            ->from('route_pickup_point')
            ->join('pickup_point', 'pickup_point.id = route_pickup_point.pickup_point_id')
            ->where('route_pickup_point.transport_route_id', $r_id)
            ->order_by('route_pickup_point.order_number', 'ASC')
            ->order_by('route_pickup_point.id', 'ASC');

            $pickup_points = $this->db->get()->result_array();

            $point_names = array_filter(array_column($pickup_points, 'pickup_point_name'));
            $route['pickup_point_name'] = !empty($point_names) ? reset($point_names) : null;
            $route['pickup_point_names'] = implode(', ', $point_names);
            $route['pickup_points'] = $pickup_points;
            $route['total_pickup_points'] = count($pickup_points);

            $route_data = $this->_build_route_timeline($r_id, $route['vehicle_id'] ?? null);
            $route['next_stop']      = $route_data['next_stop'];
            $route['next_stop_name'] = $route_data['next_stop_name'];
            $route['pickup_point']   = $route_data['pickup_point'];
            $route['drop_point']     = $route_data['drop_point'];
            $route['between_stops']  = $route_data['between_stops'];
            $route['timeline']       = $route_data['timeline'];

            $result[] = $route;
        }

        return $result;
    }

    /**
     * Builds standardized timeline of pickup points for a route
     */
    private function _build_route_timeline($route_id, $vehicle_id = null)
    {
        $empty_result = [
            'timeline'       => [],
            'pickup_point'   => null,
            'drop_point'     => null,
            'between_stops'  => [],
            'next_stop'      => null,
            'next_stop_name' => null,
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

        // Check if there is an active trip for this route / vehicle
        $trip = null;
        $bus_lat = null;
        $bus_lng = null;
        if ($this->db->table_exists('trips')) {
            $this->db->from('trips');
            $this->db->where('route_id', $route_id);
            if (!empty($vehicle_id)) {
                $this->db->where('bus_id', (string)$vehicle_id);
            }
            $this->db->order_by('id', 'DESC')->limit(1);
            $trip = $this->db->get()->row_array();
        }

        $trip_status = !empty($trip['status']) ? $trip['status'] : null;
        $start_time = !empty($trip['start_time']) ? $trip['start_time'] : null;

        if ($this->db->table_exists('bus_locations') && !empty($trip)) {
            $loc = $this->db->from('bus_locations')
                ->where('trip_id', (string)$trip['id'])
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()
                ->row_array();
            if (!empty($loc)) {
                $bus_lat = (float)$loc['latitude'];
                $bus_lng = (float)$loc['longitude'];
            }
        }

        $total_points = count($points);
        $parsed_points = [];

        foreach ($points as $idx => $pt) {
            // Strictly sequential sequence numbering (1, 2, 3...)
            $seq = $idx + 1;
            $stop_id = 'STP_' . str_pad($pt['pickup_point_id'], 2, '0', STR_PAD_LEFT);

            list($lat, $lng) = $this->_parse_lat_lng($pt['latitude'], $pt['longitude']);

            $sched_time = null;
            if (!empty($pt['pickup_time']) && $pt['pickup_time'] !== '00:00:00') {
                $sched_time = date('h:i A', strtotime($pt['pickup_time']));
            } elseif (!empty($start_time)) {
                $sched_time = date('h:i A', strtotime($start_time) + ($idx * 900));
            }

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

        $in_progress_idx = -1;
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
            'timeline'       => $timeline,
            'pickup_point'   => $pickup_point,
            'drop_point'     => $drop_point,
            'between_stops'  => $between_stops,
            'next_stop'      => $next_stop,
            'next_stop_name' => $next_stop_name,
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

