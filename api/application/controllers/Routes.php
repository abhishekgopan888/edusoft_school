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
            ],
            'students' => [
                'path' => '/api/routes/students',
                'summary' => 'Get students assigned to transport routes and pickup stops',
                'description' => 'Retrieves all students assigned to a specific transport route, stop, or vehicle, including class, section, guardian details, and stop-level breakdown.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Route ID to filter students (e.g. 2)',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'pickup_point_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Pickup Point ID to filter students by stop (e.g. 2)',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'vehicle_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Vehicle ID or Bus Number (e.g. 12 or "KL 17 Y 8989")',
                        'schema' => ['type' => 'string', 'example' => '12']
                    ],
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Search student by name, admission number, or mobile',
                        'schema' => ['type' => 'string', 'example' => 'KAILAS']
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter student day-wise history by date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-26']
                    ],
                    [
                        'name' => 'date_from',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history from start date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-01']
                    ],
                    [
                        'name' => 'date_to',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history up to end date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-30']
                    ],
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Number of history records to return (default 50, max 200)',
                        'schema' => ['type' => 'integer', 'example' => 50]
                    ],
                    [
                        'name' => 'offset',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Number of records to skip (default 0)',
                        'schema' => ['type' => 'integer', 'example' => 0]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Assigned route students and day-wise student history retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'history' => [
                'path' => '/api/routes/history',
                'summary' => 'Get student transit and day-wise timeline history for routes',
                'description' => 'Retrieves day-wise student transit timeline history including boarding/drop times, status, vehicle, driver, attender, and stop details. Supports filtering by route_id and student_id.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history by Route ID (e.g. 2)',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history by Student ID (e.g. 825)',
                        'schema' => ['type' => 'integer', 'example' => 825]
                    ],
                    [
                        'name' => 'pickup_point_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history by Pickup Point ID (e.g. 2)',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'vehicle_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history by Vehicle ID or Bus Number (e.g. 12, "KL 17 Y 8989")',
                        'schema' => ['type' => 'string', 'example' => '12']
                    ],
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Search student by name, admission number, or mobile',
                        'schema' => ['type' => 'string', 'example' => 'KAILAS']
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history by date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'example' => '2026-11-13']
                    ],
                    [
                        'name' => 'date_from',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history from start date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'example' => '2026-11-01']
                    ],
                    [
                        'name' => 'date_to',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter history up to end date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'example' => '2026-11-30']
                    ],
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Number of history records to return (default 50, max 200)',
                        'schema' => ['type' => 'integer', 'example' => 50]
                    ],
                    [
                        'name' => 'offset',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Number of records to skip (default 0)',
                        'schema' => ['type' => 'integer', 'example' => 0]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Route trip history retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'timeline' => [
                'path' => '/api/routes/timeline',
                'summary' => 'Get sequential stop timeline and live progress for a route',
                'description' => 'Retrieves stop timeline for a route with live progress, ETA to next stop, distance, and student count per stop.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Route ID',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'bus_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Bus / Vehicle ID (e.g. 12)',
                        'schema' => ['type' => 'string', 'example' => '12']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Route timeline retrieved successfully'],
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

        $route_students = $this->_get_route_students($route_id);
        $students_by_stop = [];
        foreach ($route_students as $st) {
            $sp_id = !empty($st['pickup_point_id']) ? $st['pickup_point_id'] : 0;
            if (!isset($students_by_stop[$sp_id])) {
                $students_by_stop[$sp_id] = [];
            }
            $students_by_stop[$sp_id][] = $st;
        }

        if (!empty($route_data['timeline'])) {
            foreach ($route_data['timeline'] as &$tl) {
                $tl_sp_id = !empty($tl['pickup_point_id']) ? $tl['pickup_point_id'] : (!empty($tl['stop_id']) ? $tl['stop_id'] : 0);
                $tl['students'] = isset($students_by_stop[$tl_sp_id]) ? $students_by_stop[$tl_sp_id] : [];
                $tl['students_count'] = count($tl['students']);
            }
            unset($tl);
        }

        $response_data = [
            'route_id'                 => !empty($route_id) ? (int)$route_id : 0,
            'route_name'               => (string)$route_name,
            'total_students_count'     => count($route_students),
            'students'                 => $route_students,
            'routing_progress'         => $route_data['routing_progress'],
            'next_stop'                => $route_data['next_stop'],
            'next_stop_name'           => $route_data['next_stop_name'],
            'time_to_reach_next_stop'  => $route_data['time_to_reach_next_stop'],
            'estimated_arrival_time'   => $route_data['estimated_arrival_time'],
            'distance_to_next_stop_km' => $route_data['distance_to_next_stop_km'],
            'pickup_point'             => $route_data['pickup_point'],
            'drop_point'               => $route_data['drop_point'],
            'between_stops'            => $route_data['between_stops'],
            'between_routes'           => $route_data['between_stops'],
            'timeline'                 => $route_data['timeline'],
        ];

        $this->_success('Route timeline retrieved successfully', $response_data);
    }

    /**
     * GET /api/routes/students
     * Query parameters: route_id, vehicle_id, bus_number, pickup_point_id, search, date, date_from, date_to, limit, offset
     */
    public function students()
    {
        $this->_check_auth([], false);

        $route_id        = $this->input->get('route_id') ?? $this->input->get('routeid') ?? $this->input->get('id');
        $pickup_point_id = $this->input->get('pickup_point_id') ?? $this->input->get('pickuppointid');
        $vehicle_id      = $this->input->get('vehicle_id') ?? $this->input->get('bus_id') ?? $this->input->get('busid');
        $bus_number      = $this->input->get('bus_number') ?? $this->input->get('bus_no') ?? $this->input->get('vehicle_no');
        $search          = $this->input->get('search') ?? $this->input->get('q');
        $date            = $this->input->get('date');
        $date_from       = $this->input->get('date_from');
        $date_to         = $this->input->get('date_to');
        $limit           = min(200, max(1, (int)($this->input->get('limit') ?: 50)));
        $offset          = max(0, (int)($this->input->get('offset') ?: 0));

        if (!empty($vehicle_id) && !is_numeric($vehicle_id) && empty($bus_number)) {
            $bus_number = $vehicle_id;
            $vehicle_id = null;
        }

        $students = $this->_get_route_students($route_id, $pickup_point_id, !empty($bus_number) ? $bus_number : $vehicle_id, $search);

        // Fetch day-wise history for these students
        $history_result = $this->_get_student_day_wise_history(
            $students, 
            $route_id, 
            !empty($bus_number) ? $bus_number : $vehicle_id, 
            $pickup_point_id, 
            $date, 
            $date_from, 
            $date_to, 
            $limit, 
            $offset
        );

        $student_history  = $history_result['student_history'] ?? [];
        $day_wise_history = $history_result['day_wise_history'] ?? [];

        // Attach day-wise history to each individual student record
        $history_by_student = [];
        foreach ($student_history as $h_item) {
            $sid = (int)$h_item['student_id'];
            if (!isset($history_by_student[$sid])) {
                $history_by_student[$sid] = [];
            }
            $history_by_student[$sid][] = $h_item;
        }

        foreach ($students as &$st) {
            $sid = (int)$st['student_id'];
            $st['day_wise_history'] = $history_by_student[$sid] ?? [];
            $st['student_history']  = $history_by_student[$sid] ?? [];
        }
        unset($st);

        // Group students by stop for stops_summary
        $stops_grouped = [];
        foreach ($students as $st) {
            $sp_id = !empty($st['pickup_point_id']) ? $st['pickup_point_id'] : 0;
            if (!isset($stops_grouped[$sp_id])) {
                $stops_grouped[$sp_id] = [
                    'pickup_point_id'   => $sp_id,
                    'pickup_point_name' => !empty($st['pickup_point_name']) ? $st['pickup_point_name'] : 'Unassigned Stop',
                    'pickup_time'       => $st['pickup_time'],
                    'students_count'    => 0,
                    'students'          => []
                ];
            }
            $stops_grouped[$sp_id]['students_count']++;
            $stops_grouped[$sp_id]['students'][] = $st;
        }

        $route_name = '';
        if (!empty($route_id) && $this->db->table_exists('transport_route')) {
            $r = $this->db->where('id', (int)$route_id)->get('transport_route')->row_array();
            if (!empty($r['route_title'])) {
                $route_name = $r['route_title'];
            }
        }

        $response_data = [
            'route_id'               => !empty($route_id) ? (int)$route_id : null,
            'route_title'            => $route_name,
            'total_students'         => count($students),
            'total_stops'            => count($stops_grouped),
            'total_history_records'  => $history_result['total_records'] ?? count($student_history),
            'stops_summary'          => array_values($stops_grouped),
            'student_history'        => $student_history,
            'day_wise_history'       => $day_wise_history,
            'students'               => $students
        ];

        $this->_success('Assigned route students and day-wise student history retrieved successfully', $response_data);
    }

    /**
     * GET /api/routes/history
     * Query parameters: route_id, bus_id, vehicle_id, bus_number, trip_id, status, date, date_from, date_to, limit, offset
     */
    public function history()
    {
        $this->_check_auth([], false);

        $route_id        = $this->input->get('route_id') ?? $this->input->get('routeid') ?? $this->input->get('id');
        $student_id      = $this->input->get('student_id') ?? $this->input->get('studentid');
        $bus_id          = $this->input->get('bus_id') ?? $this->input->get('busid') ?? $this->input->get('vehicle_id');
        $bus_number      = $this->input->get('bus_number') ?? $this->input->get('bus_no') ?? $this->input->get('vehicle_no');
        $pickup_point_id = $this->input->get('pickup_point_id') ?? $this->input->get('pickuppointid');
        $search          = $this->input->get('search') ?? $this->input->get('q');
        $date            = $this->input->get('date');
        $date_from       = $this->input->get('date_from');
        $date_to         = $this->input->get('date_to');
        $limit           = min(200, max(1, (int)($this->input->get('limit') ?: 50)));
        $offset          = max(0, (int)($this->input->get('offset') ?: 0));

        if (!empty($bus_id) && !is_numeric($bus_id) && empty($bus_number)) {
            $bus_number = $bus_id;
            $bus_id = null;
        }

        $vehicle_filter = !empty($bus_number) ? $bus_number : $bus_id;
        $students = $this->_get_route_students($route_id, $pickup_point_id, $vehicle_filter, $search, $student_id);

        $st_history_result = $this->_get_student_day_wise_history(
            $students,
            $route_id,
            $vehicle_filter,
            $pickup_point_id,
            $date,
            $date_from,
            $date_to,
            $limit,
            $offset
        );

        $resolved_route_id = !empty($route_id) ? (int)$route_id : null;
        $resolved_route_title = '';
        if (!empty($resolved_route_id) && $this->db->table_exists('transport_route')) {
            $r_row = $this->db->select('route_title')->where('id', $resolved_route_id)->get('transport_route')->row_array();
            if (!empty($r_row['route_title'])) {
                $resolved_route_title = $r_row['route_title'];
            }
        } elseif (!empty($students) && !empty($students[0]['route_id'])) {
            $resolved_route_id    = (int)$students[0]['route_id'];
            $resolved_route_title = $students[0]['route_title'] ?? '';
        }

        $response_data = [
            'route_id'         => $resolved_route_id,
            'route_title'      => $resolved_route_title,
            'student_id'       => !empty($student_id) ? (int)$student_id : null,
            'total_students'   => count($students),
            'total_records'    => $st_history_result['total_records'] ?? 0,
            'student_history'  => $st_history_result['student_history'] ?? [],
            'day_wise_history' => $st_history_result['day_wise_history'] ?? [],
            'students'         => $students
        ];

        $this->_success('Student timeline details history retrieved successfully', $response_data);
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
            $this->db->where('(vehicle_routes.vehicle_id = ' . (int)$vehicle_id . ' OR transport_route.id IN (SELECT route_id FROM trips WHERE bus_id = ' . $this->db->escape($vehicle_id) . '))', null, false);
        }

        if (!empty($bus_number)) {
            $clean_bus = preg_replace('/\s+/', '', $bus_number);
            $escaped_bus = $this->db->escape_str(trim($bus_number));
            $escaped_clean = $this->db->escape_str($clean_bus);
            $this->db->where("(vehicles.vehicle_no = '{$escaped_bus}' OR REPLACE(vehicles.vehicle_no, ' ', '') = '{$escaped_clean}' OR vehicles.vehicle_no LIKE '%{$escaped_bus}%' OR transport_route.id IN (SELECT route_id FROM trips WHERE bus_id = '{$escaped_bus}'))", null, false);
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

            // Fetch assigned students for this route
            $route_students = $this->_get_route_students($r_id, $pickup_point_id, !empty($bus_number) ? $bus_number : $vehicle_id);
            $route['total_students_count'] = count($route_students);
            $route['students']             = $route_students;

            // Index students by pickup_point_id for quick stop lookup
            $students_by_stop = [];
            foreach ($route_students as $st) {
                $sp_id = !empty($st['pickup_point_id']) ? $st['pickup_point_id'] : 0;
                if (!isset($students_by_stop[$sp_id])) {
                    $students_by_stop[$sp_id] = [];
                }
                $students_by_stop[$sp_id][] = $st;
            }

            // Attach students to each pickup point
            foreach ($pickup_points as &$pp) {
                $pp_sp_id = !empty($pp['pickup_point_id']) ? $pp['pickup_point_id'] : 0;
                $pp['students'] = isset($students_by_stop[$pp_sp_id]) ? $students_by_stop[$pp_sp_id] : [];
                $pp['students_count'] = count($pp['students']);
            }
            unset($pp);

            $point_names = array_filter(array_column($pickup_points, 'pickup_point_name'));
            $route['pickup_point_name'] = !empty($point_names) ? reset($point_names) : null;
            $route['pickup_point_names'] = implode(', ', $point_names);
            $route['pickup_points'] = $pickup_points;
            $route['total_pickup_points'] = count($pickup_points);

            $route_data = $this->_build_route_timeline($r_id, $route['vehicle_id'] ?? null);

            // Attach students to timeline stops as well
            if (!empty($route_data['timeline'])) {
                foreach ($route_data['timeline'] as &$tl) {
                    $tl_sp_id = !empty($tl['pickup_point_id']) ? $tl['pickup_point_id'] : (!empty($tl['stop_id']) ? $tl['stop_id'] : 0);
                    $tl['students'] = isset($students_by_stop[$tl_sp_id]) ? $students_by_stop[$tl_sp_id] : [];
                    $tl['students_count'] = count($tl['students']);
                }
                unset($tl);
            }

            $route['routing_progress']         = $route_data['routing_progress'];
            $route['next_stop']                = $route_data['next_stop'];
            $route['next_stop_name']           = $route_data['next_stop_name'];
            $route['time_to_reach_next_stop']  = $route_data['time_to_reach_next_stop'];
            $route['estimated_arrival_time']   = $route_data['estimated_arrival_time'];
            $route['distance_to_next_stop_km'] = $route_data['distance_to_next_stop_km'];
            $route['pickup_point']             = $route_data['pickup_point'];
            $route['drop_point']               = $route_data['drop_point'];
            $route['between_stops']            = $route_data['between_stops'];
            $route['between_routes']           = $route_data['between_stops'];
            $route['timeline']                 = $route_data['timeline'];

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
            'timeline'                 => [],
            'pickup_point'             => null,
            'drop_point'               => null,
            'between_stops'            => [],
            'between_routes'           => [],
            'next_stop'                => null,
            'next_stop_name'           => null,
            'routing_progress'         => null,
            'time_to_reach_next_stop'  => null,
            'estimated_arrival_time'   => null,
            'distance_to_next_stop_km' => null,
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

        // Check if there is an active trip for this route / vehicle
        $trip = null;
        $bus_lat = null;
        $bus_lng = null;
        $bus_speed = null;
        $recorded_at = null;
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
                $bus_speed = isset($loc['speed']) ? (float)$loc['speed'] : null;
                $recorded_at = $loc['recorded_at'] ?? null;
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

        // Determine movement status
        $is_moving = ($bus_speed !== null && (float)$bus_speed >= 3.0);
        $geofence_km = 0.35; // 350 meters stop arrival geofence

        // Distances from current bus position to all stops
        $dists_from_bus = [];
        $valid_bus_coords = (!empty($bus_lat) && !empty($bus_lng) && ((float)$bus_lat != 0.0 || (float)$bus_lng != 0.0));
        if ($valid_bus_coords) {
            foreach ($parsed_points as $i => $p) {
                if ($p['latitude'] != 0.0 && $p['longitude'] != 0.0) {
                    $dists_from_bus[$i] = $this->_calculate_distance_km((float)$bus_lat, (float)$bus_lng, $p['latitude'], $p['longitude']);
                } else {
                    $dists_from_bus[$i] = PHP_FLOAT_MAX;
                }
            }
        }

        $min_dist_to_route = !empty($dists_from_bus) ? min($dists_from_bus) : PHP_FLOAT_MAX;
        $bus_is_nearby = ($min_dist_to_route <= 5.0); // Within 5 km of route

        // Find which stops have been reached during this trip
        $visited_stop_times = [];
        if (!empty($trip) && !empty($trip['id']) && $this->db->table_exists('bus_locations')) {
            $history_points = $this->db->select('latitude, longitude, recorded_at')
                ->from('bus_locations')
                ->where('trip_id', (string)$trip['id'])
                ->order_by('id', 'ASC')
                ->get()
                ->result_array();

            foreach ($history_points as $hp) {
                $h_lat = (float)$hp['latitude'];
                $h_lng = (float)$hp['longitude'];
                if ($h_lat == 0.0 || $h_lng == 0.0) continue;

                foreach ($parsed_points as $idx => $p) {
                    if ($p['latitude'] != 0.0 && $p['longitude'] != 0.0 && !isset($visited_stop_times[$idx])) {
                        $h_dist = $this->_calculate_distance_km($h_lat, $h_lng, $p['latitude'], $p['longitude']);
                        if ($h_dist <= $geofence_km) {
                            $visited_stop_times[$idx] = $hp['recorded_at'];
                        }
                    }
                }
            }
        }

        // Current location geofence check
        if ($valid_bus_coords) {
            foreach ($parsed_points as $idx => $p) {
                if (isset($dists_from_bus[$idx]) && $dists_from_bus[$idx] <= $geofence_km && !isset($visited_stop_times[$idx])) {
                    $visited_stop_times[$idx] = !empty($recorded_at) ? $recorded_at : date('Y-m-d H:i:s');
                }
            }
        }

        // Determine sequential progression
        $completed_map = [];
        $in_progress_idx = 0;

        if ($trip_status === 'completed') {
            foreach ($parsed_points as $i => $p) {
                $completed_map[$i] = true;
            }
            $in_progress_idx = $total_points - 1;
        } elseif ($trip_status === 'started' && $valid_bus_coords && $bus_is_nearby && $total_points > 1) {
            for ($i = 0; $i < $total_points; $i++) {
                if ($i === 0) {
                    $at_stop_0 = (isset($dists_from_bus[0]) && $dists_from_bus[0] <= $geofence_km);
                    if ($at_stop_0 && !$is_moving) {
                        // Bus is stationary at start stop -> cannot be completed, stays in_progress
                        $in_progress_idx = 0;
                        break;
                    } elseif (!$at_stop_0 && (isset($dists_from_bus[1]) && isset($dists_from_bus[0]) && $dists_from_bus[1] < $dists_from_bus[0] || !empty($visited_stop_times[1]))) {
                        // Bus has moved away from stop 0 towards stop 1
                        $completed_map[0] = true;
                        $in_progress_idx = 1;
                    } elseif (!$at_stop_0 && $is_moving && isset($dists_from_bus[0]) && $dists_from_bus[0] > $geofence_km) {
                        $completed_map[0] = true;
                        $in_progress_idx = 1;
                    } else {
                        $in_progress_idx = 0;
                        break;
                    }
                } else {
                    $has_reached_i = !empty($visited_stop_times[$i]) || (isset($dists_from_bus[$i]) && $dists_from_bus[$i] <= $geofence_km);
                    if (!$has_reached_i) {
                        $in_progress_idx = $i;
                        break;
                    }

                    $at_stop_i = (isset($dists_from_bus[$i]) && $dists_from_bus[$i] <= $geofence_km);
                    if ($at_stop_i && !$is_moving) {
                        // Bus is stationary at stop i -> stays in_progress, cannot advance
                        $in_progress_idx = $i;
                        break;
                    }

                    if ($i === $total_points - 1) {
                        $in_progress_idx = $i;
                        break;
                    }

                    // Check departure towards stop i+1
                    if ((isset($dists_from_bus[$i + 1]) && isset($dists_from_bus[$i]) && $dists_from_bus[$i + 1] < $dists_from_bus[$i]) || !empty($visited_stop_times[$i + 1])) {
                        $completed_map[$i] = true;
                        $in_progress_idx = $i + 1;
                    } else {
                        $in_progress_idx = $i;
                        break;
                    }
                }
            }
        } else {
            // Bus is outside route boundary or stationary prior to start -> Stop 0 is in_progress, 0 stops completed
            $in_progress_idx = 0;
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

        $timeline = [];
        $cumulative_road_km = 0.0;
        $cumulative_minutes = 0;

        foreach ($parsed_points as $i => $p) {
            $status = 'upcoming';
            $actual_time = null;

            if ($trip_status === 'completed') {
                $status = 'completed';
                $actual_time = !empty($visited_stop_times[$i]) ? date('h:i A', strtotime($visited_stop_times[$i])) : $p['sched_time'];
            } elseif (!empty($completed_map[$i])) {
                $status = 'completed';
                $actual_time = !empty($visited_stop_times[$i]) ? date('h:i A', strtotime($visited_stop_times[$i])) : $p['sched_time'];
            } elseif ($i === $in_progress_idx) {
                $status = 'in_progress';
                $actual_time = null;
            } else {
                $status = 'upcoming';
                $actual_time = null;
            }

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
                $at_this_stop = (isset($dists_from_bus[$i]) && $dists_from_bus[$i] <= $geofence_km);

                if ($at_this_stop) {
                    $dist_km = 0.0;
                    $dist_m = 0;
                    $dist_txt = '0 m';
                    $time_reach = $is_moving ? 'Departing' : 'At Stop';
                    $eta_mins = 0;
                    $eta_clock = !empty($p['sched_time']) ? $p['sched_time'] : date('h:i A', $base_time);
                    $cumulative_road_km = 0.0;
                    $cumulative_minutes = 0;
                } else {
                    if ($bus_is_nearby && isset($dists_from_bus[$i])) {
                        $direct_dist = $dists_from_bus[$i];
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

                    $leg_mins = $is_moving 
                        ? max(1, (int)round(($leg_road_km / $effective_speed) * 60))
                        : max(2, (int)round(($leg_road_km / 20.0) * 60));
                    $cumulative_road_km = $leg_road_km;
                    $cumulative_minutes = $leg_mins;

                    $dist_km = round($cumulative_road_km, 2);
                    $dist_m = (int)round($dist_km * 1000);
                    $dist_txt = ($dist_km < 1.0) ? ($dist_m . ' m') : ($dist_km . ' km');
                    $eta_mins = $cumulative_minutes;
                    $time_reach = $eta_mins . ' ' . ($eta_mins === 1 ? 'min' : 'mins');
                    $eta_clock = date('h:i A', $base_time + ($cumulative_minutes * 60));
                }
            } else {
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

                $dwell_time = 1;
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

        $routing_progress = $this->_calculate_routing_progress($timeline, $trip_status ?? 'started', $next_stop, $is_moving);

        return [
            'timeline'                 => $timeline,
            'is_moving'                => (bool)$is_moving,
            'pickup_point'             => $pickup_point,
            'drop_point'               => $drop_point,
            'between_stops'            => $between_stops,
            'between_routes'           => $between_stops,
            'next_stop'                => $next_stop,
            'next_stop_name'           => $next_stop_name,
            'routing_progress'         => $routing_progress,
            'time_to_reach_next_stop'  => $routing_progress['time_to_reach_next_stop'],
            'estimated_arrival_time'   => $routing_progress['estimated_arrival_time'],
            'distance_to_next_stop_km' => $routing_progress['distance_to_next_stop_km'],
        ];
    }

    /**
     * Calculates overall routing progress metrics and next stop ETA
     */
    private function _calculate_routing_progress($timeline, $trip_status = 'started', $next_stop = null, $is_moving = false)
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
                'is_moving'                  => (bool)$is_moving,
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
        } elseif ($completed_stops > 0 && $total_stops > 0) {
            $pct = min(99, (int)round(($completed_stops / $total_stops) * 100));
        } else {
            $pct = 0;
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
            'is_moving'                  => (bool)$is_moving,
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

    /**
     * Fetch active students assigned to a route, stop, or vehicle
     */
    private function _get_route_students($route_id = null, $pickup_point_id = null, $vehicle_id = null, $search = null, $student_id = null)
    {
        if (!$this->db->table_exists('student_session') || !$this->db->table_exists('students')) {
            return [];
        }

        $this->db->select('
            s.id as student_id,
            s.admission_no,
            s.roll_no,
            s.firstname,
            s.lastname,
            CONCAT_WS(" ", s.firstname, s.lastname) as student_name,
            s.gender,
            s.mobileno as student_phone,
            s.email as student_email,
            s.father_name,
            s.father_phone,
            s.mother_name,
            s.mother_phone,
            s.guardian_name,
            s.guardian_phone,
            s.guardian_relation,
            s.guardian_address,
            s.current_address,
            s.boarding_status,
            c.class,
            sec.section,
            ss.route_pickup_point_id,
            ss.vehroute_id,
            COALESCE(vr.route_id, rpp.transport_route_id) as route_id,
            tr.route_title,
            rpp.pickup_point_id,
            pp.name as pickup_point_name,
            rpp.pickup_time,
            rpp.order_number as stop_order,
            v.id as vehicle_id,
            v.vehicle_no as bus_number
        ')
        ->from('student_session ss')
        ->join('students s', 's.id = ss.student_id')
        ->join('classes c', 'c.id = ss.class_id', 'left')
        ->join('sections sec', 'sec.id = ss.section_id', 'left')
        ->join('route_pickup_point rpp', 'rpp.id = ss.route_pickup_point_id', 'left')
        ->join('pickup_point pp', 'pp.id = rpp.pickup_point_id', 'left')
        ->join('vehicle_routes vr', 'vr.id = ss.vehroute_id', 'left')
        ->join('transport_route tr', 'tr.id = COALESCE(vr.route_id, rpp.transport_route_id)', 'left')
        ->join('vehicles v', 'v.id = vr.vehicle_id', 'left')
        ->where('s.is_active', 'yes')
        ->where('((ss.vehroute_id IS NOT NULL AND ss.vehroute_id > 0) OR (ss.route_pickup_point_id IS NOT NULL AND ss.route_pickup_point_id > 0))', null, false);

        if (!empty($route_id)) {
            $this->db->where('(vr.route_id = ' . (int)$route_id . ' OR rpp.transport_route_id = ' . (int)$route_id . ')', null, false);
        }

        if (!empty($student_id)) {
            $this->db->where('s.id', (int)$student_id);
        }

        if (!empty($pickup_point_id)) {
            $this->db->where('(rpp.pickup_point_id = ' . (int)$pickup_point_id . ' OR ss.route_pickup_point_id = ' . (int)$pickup_point_id . ')', null, false);
        }

        if (!empty($vehicle_id)) {
            if (is_numeric($vehicle_id)) {
                $this->db->where('(vr.vehicle_id = ' . (int)$vehicle_id . ' OR vr.route_id IN (SELECT route_id FROM trips WHERE bus_id = ' . $this->db->escape($vehicle_id) . '))', null, false);
            } else {
                $clean_bus = preg_replace('/\s+/', '', $vehicle_id);
                $escaped = $this->db->escape_str(trim($vehicle_id));
                $escaped_clean = $this->db->escape_str($clean_bus);
                $this->db->where("(v.vehicle_no = '{$escaped}' OR REPLACE(v.vehicle_no, ' ', '') = '{$escaped_clean}' OR v.vehicle_no LIKE '%{$escaped}%' OR vr.route_id IN (SELECT route_id FROM trips WHERE bus_id = '{$escaped}'))", null, false);
            }
        }

        if (!empty($search)) {
            $escaped_search = $this->db->escape_like_str(trim($search));
            $this->db->where("(s.firstname LIKE '%{$escaped_search}%' OR s.lastname LIKE '%{$escaped_search}%' OR s.admission_no LIKE '%{$escaped_search}%' OR s.mobileno LIKE '%{$escaped_search}%')", null, false);
        }

        $this->db->order_by('rpp.order_number', 'ASC');
        $this->db->order_by('s.firstname', 'ASC');

        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return [];
        }

        $students = [];
        foreach ($rows as $r) {
            $students[] = [
                'student_id'            => (int)$r['student_id'],
                'admission_no'          => (string)$r['admission_no'],
                'roll_no'               => (string)$r['roll_no'],
                'student_name'          => trim($r['student_name']),
                'firstname'             => (string)$r['firstname'],
                'lastname'              => (string)$r['lastname'],
                'gender'                => (string)$r['gender'],
                'student_phone'         => (string)$r['student_phone'],
                'student_email'         => (string)$r['student_email'],
                'father_name'           => (string)$r['father_name'],
                'father_phone'          => (string)$r['father_phone'],
                'mother_name'           => (string)$r['mother_name'],
                'mother_phone'          => (string)$r['mother_phone'],
                'guardian_name'         => (string)$r['guardian_name'],
                'guardian_phone'        => (string)$r['guardian_phone'],
                'guardian_relation'     => (string)$r['guardian_relation'],
                'guardian_address'      => (string)$r['guardian_address'],
                'current_address'       => (string)$r['current_address'],
                'class'                 => (string)$r['class'],
                'section'               => (string)$r['section'],
                'class_section'         => trim($r['class'] . ' ' . $r['section']),
                'boarding_status'       => !empty($r['boarding_status']) ? $r['boarding_status'] : 'Pending',
                'route_id'              => !empty($r['route_id']) ? (int)$r['route_id'] : null,
                'route_title'           => (string)$r['route_title'],
                'route_pickup_point_id' => !empty($r['route_pickup_point_id']) ? (int)$r['route_pickup_point_id'] : null,
                'pickup_point_id'       => !empty($r['pickup_point_id']) ? (int)$r['pickup_point_id'] : null,
                'pickup_point_name'     => (string)$r['pickup_point_name'],
                'pickup_time'           => !empty($r['pickup_time']) ? date('h:i A', strtotime($r['pickup_time'])) : null,
                'stop_order'            => isset($r['stop_order']) ? (float)$r['stop_order'] : 0,
                'vehicle_id'            => !empty($r['vehicle_id']) ? (int)$r['vehicle_id'] : null,
                'bus_number'            => (string)$r['bus_number'],
            ];
        }

        return $students;
    }

    /**
     * Fetch day-wise boarding / transit history for students
     */
    private function _get_student_day_wise_history($students = [], $route_id = null, $vehicle_id = null, $pickup_point_id = null, $date = null, $date_from = null, $date_to = null, $limit = 50, $offset = 0)
    {
        if (empty($students)) {
            return [
                'total_records'    => 0,
                'student_history'  => [],
                'day_wise_history' => []
            ];
        }

        $student_ids = array_map(function ($s) {
            return (int)$s['student_id'];
        }, $students);
        $student_ids = array_filter(array_unique($student_ids));
        if (empty($student_ids)) {
            return [
                'total_records'    => 0,
                'student_history'  => [],
                'day_wise_history' => []
            ];
        }

        $student_ids_str = implode(',', $student_ids);

        // 1. Fetch boarding_point records
        $bp_records = [];
        if ($this->db->table_exists('boarding_point')) {
            $this->db->select("
                bp.id as boarding_id,
                bp.studentid as student_id,
                bp.busid,
                bp.attenderid,
                bp.status,
                bp.timestamp,
                DATE(bp.timestamp) as record_date,
                TIME(bp.timestamp) as record_time,
                v.vehicle_no as bus_number,
                v.vehicle_model,
                att.attender_name,
                att.attender_phone
            ")
            ->from('boarding_point bp')
            ->join('vehicles v', '(v.id = bp.busid OR v.vehicle_no = bp.busid)', 'left')
            ->join('attenders att', 'att.id = bp.attenderid', 'left')
            ->where("bp.studentid IN ($student_ids_str)", null, false);

            if (!empty($date)) {
                $this->db->where('DATE(bp.timestamp)', date('Y-m-d', strtotime($date)));
            }
            if (!empty($date_from)) {
                $this->db->where('DATE(bp.timestamp) >=', date('Y-m-d', strtotime($date_from)));
            }
            if (!empty($date_to)) {
                $this->db->where('DATE(bp.timestamp) <=', date('Y-m-d', strtotime($date_to)));
            }

            $this->db->order_by('bp.timestamp', 'DESC');
            $bp_records = $this->db->get()->result_array();
        }

        // 2. Fetch trips for the relevant routes/buses
        $trip_records = [];
        if ($this->db->table_exists('trips')) {
            $this->db->select("
                t.id as trip_id,
                t.bus_id,
                t.driver_id,
                t.route_id,
                t.trip_type,
                t.status as trip_status,
                t.start_time,
                t.end_time,
                t.note as trip_note,
                DATE(t.start_time) as trip_date,
                tr.route_title,
                v.vehicle_no as bus_number,
                v.vehicle_model,
                COALESCE(d.driver_name, vr_d.driver_name) as driver_name,
                COALESCE(d.contact, vr_d.contact) as driver_contact,
                att.attender_name,
                att.attender_phone
            ")
            ->from('trips t')
            ->join('transport_route tr', 'tr.id = t.route_id', 'left')
            ->join('vehicles v', '(v.id = t.bus_id OR v.vehicle_no = t.bus_id)', 'left')
            ->join('vehicle_routes vr', 'vr.route_id = t.route_id', 'left')
            ->join('drivers d', 'd.id = t.driver_id', 'left')
            ->join('drivers vr_d', 'vr_d.id = vr.driver_id', 'left')
            ->join('attenders att', 'att.id = vr.attender_id', 'left');

            $trip_conditions = [];
            if (!empty($route_id)) {
                $trip_conditions[] = 't.route_id = ' . (int)$route_id;
            }
            if (!empty($vehicle_id)) {
                $escaped_v = $this->db->escape_str($vehicle_id);
                $trip_conditions[] = "(t.bus_id = '{$escaped_v}' OR v.id = '{$escaped_v}')";
            }
            if (empty($trip_conditions)) {
                $s_routes = array_unique(array_filter(array_column($students, 'route_id')));
                if (!empty($s_routes)) {
                    $trip_conditions[] = 't.route_id IN (' . implode(',', $s_routes) . ')';
                }
            }
            if (!empty($trip_conditions)) {
                $this->db->where('(' . implode(' OR ', $trip_conditions) . ')', null, false);
            }

            if (!empty($date)) {
                $this->db->where('DATE(t.start_time)', date('Y-m-d', strtotime($date)));
            }
            if (!empty($date_from)) {
                $this->db->where('DATE(t.start_time) >=', date('Y-m-d', strtotime($date_from)));
            }
            if (!empty($date_to)) {
                $this->db->where('DATE(t.start_time) <=', date('Y-m-d', strtotime($date_to)));
            }

            $this->db->group_by('t.id');
            $this->db->order_by('t.start_time', 'DESC');
            $trip_records = $this->db->get()->result_array();
        }

        // Organize boarding records by [student_id][date]
        $bp_by_student_date = [];
        foreach ($bp_records as $bp) {
            $sid = (int)$bp['student_id'];
            $dt  = $bp['record_date'];
            if (!isset($bp_by_student_date[$sid][$dt])) {
                $bp_by_student_date[$sid][$dt] = [];
            }
            $bp_by_student_date[$sid][$dt][] = $bp;
        }

        // Organize trips by date
        $trips_by_date = [];
        foreach ($trip_records as $tr) {
            $dt = $tr['trip_date'];
            if (!isset($trips_by_date[$dt])) {
                $trips_by_date[$dt] = [];
            }
            $trips_by_date[$dt][] = $tr;
        }

        // Gather all relevant dates
        $all_bp_dates = [];
        foreach ($bp_by_student_date as $s_dates) {
            foreach (array_keys($s_dates) as $d_key) {
                $all_bp_dates[] = $d_key;
            }
        }
        $all_dates = array_unique(array_merge(array_keys($trips_by_date), $all_bp_dates));

        // If no past dates recorded or date filter is today, include today
        $today = date('Y-m-d');
        if (empty($all_dates) || (empty($date_from) && empty($date_to) && empty($date) && !in_array($today, $all_dates))) {
            $all_dates[] = $today;
        }

        rsort($all_dates);

        $history_entries = [];

        foreach ($all_dates as $cur_date) {
            $matching_trips = $trips_by_date[$cur_date] ?? [];
            $primary_trip   = !empty($matching_trips) ? $matching_trips[0] : null;

            foreach ($students as $st) {
                $sid = (int)$st['student_id'];
                $student_bp = $bp_by_student_date[$sid][$cur_date] ?? [];

                // Determine boarding/drop timestamps from boarding_point
                $boarded_time = null;
                $dropped_time = null;
                $pending_time = null;
                $bp_status    = null;
                $bp_bus       = null;
                $bp_attender  = null;

                if (!empty($student_bp)) {
                    foreach ($student_bp as $rec) {
                        $st_lower = strtolower($rec['status']);
                        if ($st_lower === 'boarded' && empty($boarded_time)) {
                            $boarded_time = $rec['timestamp'];
                        }
                        if (in_array($st_lower, ['dropped', 'deboarded']) && empty($dropped_time)) {
                            $dropped_time = $rec['timestamp'];
                        }
                        if ($st_lower === 'pending' && empty($pending_time)) {
                            $pending_time = $rec['timestamp'];
                        }
                        if (empty($bp_bus) && !empty($rec['bus_number'])) {
                            $bp_bus = $rec['bus_number'];
                        }
                        if (empty($bp_attender) && !empty($rec['attender_name'])) {
                            $bp_attender = $rec['attender_name'];
                        }
                    }
                    $bp_status = ucfirst($student_bp[0]['status']);
                }

                if (empty($bp_status)) {
                    if (!empty($primary_trip)) {
                        $bp_status = (strtolower($primary_trip['trip_status']) === 'completed') ? 'Dropped' : 'Boarded';
                        $boarded_time = $primary_trip['start_time'];
                        $dropped_time = $primary_trip['end_time'];
                    } else {
                        $bp_status = !empty($st['boarding_status']) ? ucfirst($st['boarding_status']) : 'Pending';
                    }
                }

                $resolved_bus_id = !empty($primary_trip['bus_id']) ? $primary_trip['bus_id'] : ($st['vehicle_id'] ?? $vehicle_id);
                $resolved_bus_no = !empty($primary_trip['bus_number']) ? $primary_trip['bus_number'] : ($bp_bus ?? ($st['bus_number'] ?? ''));

                $history_entries[] = [
                    'date'                => $cur_date,
                    'day'                 => date('l', strtotime($cur_date)),
                    'student_id'          => $sid,
                    'student_name'        => $st['student_name'],
                    'admission_no'        => (string)$st['admission_no'],
                    'roll_no'             => (string)$st['roll_no'],
                    'class'               => (string)$st['class'],
                    'section'             => (string)$st['section'],
                    'student_phone'       => (string)$st['student_phone'],
                    'guardian_name'       => (string)$st['guardian_name'],
                    'guardian_phone'      => (string)$st['guardian_phone'],
                    'route_id'            => !empty($st['route_id']) ? (int)$st['route_id'] : (!empty($route_id) ? (int)$route_id : null),
                    'route_title'         => !empty($st['route_title']) ? $st['route_title'] : (!empty($primary_trip['route_title']) ? $primary_trip['route_title'] : ''),
                    'pickup_point_id'     => !empty($st['pickup_point_id']) ? (int)$st['pickup_point_id'] : null,
                    'pickup_point_name'   => !empty($st['pickup_point_name']) ? $st['pickup_point_name'] : '',
                    'pickup_time'         => (string)$st['pickup_time'],
                    'vehicle_id'          => $resolved_bus_id,
                    'bus_number'          => $resolved_bus_no,
                    'vehicle_model'       => !empty($primary_trip['vehicle_model']) ? $primary_trip['vehicle_model'] : ($st['vehicle_model'] ?? ''),
                    'status'              => $bp_status,
                    'boarded_time'        => $boarded_time,
                    'dropped_time'        => $dropped_time,
                    'pending_time'        => $pending_time,
                    'driver_name'         => !empty($primary_trip['driver_name']) ? $primary_trip['driver_name'] : '',
                    'driver_contact'      => !empty($primary_trip['driver_contact']) ? $primary_trip['driver_contact'] : '',
                    'attender_name'       => !empty($primary_trip['attender_name']) ? $primary_trip['attender_name'] : ($bp_attender ?? ''),
                    'attender_phone'      => !empty($primary_trip['attender_phone']) ? $primary_trip['attender_phone'] : '',
                    'trip_id'             => !empty($primary_trip['trip_id']) ? (int)$primary_trip['trip_id'] : null,
                    'trip_type'           => !empty($primary_trip['trip_type']) ? $primary_trip['trip_type'] : 'pickup',
                    'trip_status'         => !empty($primary_trip['trip_status']) ? $primary_trip['trip_status'] : null,
                    'trip_start_time'     => !empty($primary_trip['start_time']) ? $primary_trip['start_time'] : null,
                    'trip_end_time'       => !empty($primary_trip['end_time']) ? $primary_trip['end_time'] : null,
                    'note'                => !empty($primary_trip['trip_note']) ? $primary_trip['trip_note'] : ''
                ];
            }
        }

        // Group day-wise
        $day_wise_grouped = [];
        foreach ($history_entries as $entry) {
            $d = $entry['date'];
            if (!isset($day_wise_grouped[$d])) {
                $day_wise_grouped[$d] = [
                    'date'            => $d,
                    'day'             => $entry['day'],
                    'total_students'  => 0,
                    'boarded_count'   => 0,
                    'dropped_count'   => 0,
                    'pending_count'   => 0,
                    'trips_count'     => isset($trips_by_date[$d]) ? count($trips_by_date[$d]) : 0,
                    'records'         => []
                ];
            }
            $day_wise_grouped[$d]['total_students']++;
            $st_low = strtolower($entry['status']);
            if ($st_low === 'boarded') {
                $day_wise_grouped[$d]['boarded_count']++;
            } elseif ($st_low === 'dropped') {
                $day_wise_grouped[$d]['dropped_count']++;
            } else {
                $day_wise_grouped[$d]['pending_count']++;
            }
            $day_wise_grouped[$d]['records'][] = $entry;
        }

        return [
            'total_records'    => count($history_entries),
            'student_history'  => array_slice($history_entries, $offset, $limit),
            'day_wise_history' => array_values($day_wise_grouped)
        ];
    }

    /**
     * Query trip history for transport routes
     */
    private function _get_route_history($filters = [])
    {
        if (!$this->db->table_exists('trips')) {
            return ['total_records' => 0, 'filters_applied' => array_filter($filters), 'trips' => []];
        }

        $this->db->select('
            trips.id as trip_id,
            trips.bus_id,
            trips.driver_id,
            trips.route_id,
            trips.trip_type,
            trips.status,
            trips.start_time,
            trips.end_time,
            trips.note,
            trips.created_at,
            tr.route_title as route_name,
            v.vehicle_no as bus_number,
            v.vehicle_model,
            COALESCE(d.driver_name, vr_d.driver_name) as driver_name,
            COALESCE(d.contact, vr_d.contact) as driver_contact,
            COALESCE(d.licence, vr_d.licence) as driver_licence,
            att.attender_name
        ')
        ->from('trips')
        ->join('transport_route tr', 'tr.id = trips.route_id', 'left')
        ->join('vehicles v', '(v.id = trips.bus_id OR v.vehicle_no = trips.bus_id)', 'left')
        ->join('vehicle_routes vr', 'vr.route_id = trips.route_id', 'left')
        ->join('drivers d', 'd.id = trips.driver_id', 'left')
        ->join('drivers vr_d', 'vr_d.id = vr.driver_id', 'left')
        ->join('attenders att', 'att.id = vr.attender_id', 'left');

        if (!empty($filters['route_id'])) {
            $this->db->where('trips.route_id', (int)$filters['route_id']);
        }

        if (!empty($filters['bus_id'])) {
            $b_id = $this->db->escape_str($filters['bus_id']);
            $this->db->where("(trips.bus_id = '{$b_id}' OR v.id = '{$b_id}')", null, false);
        }

        if (!empty($filters['bus_number'])) {
            $b_no = $this->db->escape_like_str($filters['bus_number']);
            $this->db->where("(v.vehicle_no LIKE '%{$b_no}%' OR trips.bus_id LIKE '%{$b_no}%')", null, false);
        }

        if (!empty($filters['trip_id'])) {
            $this->db->where('trips.id', (int)$filters['trip_id']);
        }

        if (!empty($filters['date'])) {
            $this->db->where('DATE(trips.start_time)', $filters['date']);
        }

        if (!empty($filters['date_from'])) {
            $this->db->where('DATE(trips.start_time) >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $this->db->where('DATE(trips.start_time) <=', $filters['date_to']);
        }

        if (!empty($filters['status']) && strtolower($filters['status']) !== 'all') {
            $this->db->where('trips.status', strtolower(trim($filters['status'])));
        }

        $this->db->group_by('trips.id');
        $this->db->order_by('trips.start_time', 'DESC');
        $this->db->order_by('trips.id', 'DESC');

        if (!empty($filters['limit'])) {
            $this->db->limit((int)$filters['limit'], (int)($filters['offset'] ?? 0));
        }

        $trips = $this->db->get()->result_array();

        if (empty($trips)) {
            return [
                'total_records'   => 0,
                'filters_applied' => array_filter($filters),
                'trips'           => []
            ];
        }

        $has_locations = $this->db->table_exists('bus_locations');

        $result_trips = [];
        foreach ($trips as $t) {
            $trip_id = (string)$t['trip_id'];
            $route_id = !empty($t['route_id']) ? (int)$t['route_id'] : null;

            // Duration calculation
            $duration_mins = null;
            $duration_formatted = null;
            $start_ts = !empty($t['start_time']) ? strtotime($t['start_time']) : null;
            $end_ts   = !empty($t['end_time']) ? strtotime($t['end_time']) : null;

            if ($start_ts && $end_ts && $end_ts >= $start_ts) {
                $secs = $end_ts - $start_ts;
                $duration_mins = max(1, (int)round($secs / 60));
                $hrs = floor($duration_mins / 60);
                $mins = $duration_mins % 60;
                $duration_formatted = ($hrs > 0) ? "{$hrs}h {$mins}m" : "{$mins} mins";
            }

            // GPS telemetry summary for this trip
            $gps_summary = [
                'total_points'     => 0,
                'first_recorded'   => null,
                'last_recorded'    => null,
                'start_latitude'   => null,
                'start_longitude'  => null,
                'end_latitude'     => null,
                'end_longitude'    => null,
                'avg_speed_kmh'    => 0.0,
                'max_speed_kmh'    => 0.0,
                'total_distance_km'=> 0.0
            ];

            if ($has_locations) {
                $loc_stats = $this->db->select('COUNT(*) as total_points, MIN(recorded_at) as first_recorded, MAX(recorded_at) as last_recorded, AVG(speed) as avg_speed, MAX(speed) as max_speed')
                    ->from('bus_locations')
                    ->where('trip_id', $trip_id)
                    ->get()
                    ->row_array();

                if (!empty($loc_stats) && (int)$loc_stats['total_points'] > 0) {
                    $gps_summary['total_points']   = (int)$loc_stats['total_points'];
                    $gps_summary['first_recorded'] = $loc_stats['first_recorded'];
                    $gps_summary['last_recorded']  = $loc_stats['last_recorded'];
                    $gps_summary['avg_speed_kmh']  = round((float)$loc_stats['avg_speed'], 1);
                    $gps_summary['max_speed_kmh']  = round((float)$loc_stats['max_speed'], 1);

                    // If trip end_time was empty but locations exist, compute duration from telemetry
                    if (!$duration_mins && !empty($loc_stats['first_recorded']) && !empty($loc_stats['last_recorded'])) {
                        $f_ts = strtotime($loc_stats['first_recorded']);
                        $l_ts = strtotime($loc_stats['last_recorded']);
                        if ($l_ts >= $f_ts) {
                            $duration_mins = max(1, (int)round(($l_ts - $f_ts) / 60));
                            $hrs = floor($duration_mins / 60);
                            $mins = $duration_mins % 60;
                            $duration_formatted = ($hrs > 0) ? "{$hrs}h {$mins}m" : "{$mins} mins";
                        }
                    }

                    // Start and end positions
                    $first_loc = $this->db->select('latitude, longitude')
                        ->from('bus_locations')
                        ->where('trip_id', $trip_id)
                        ->order_by('id', 'ASC')
                        ->limit(1)
                        ->get()
                        ->row_array();

                    $last_loc = $this->db->select('latitude, longitude')
                        ->from('bus_locations')
                        ->where('trip_id', $trip_id)
                        ->order_by('id', 'DESC')
                        ->limit(1)
                        ->get()
                        ->row_array();

                    if (!empty($first_loc)) {
                        $gps_summary['start_latitude']  = (float)$first_loc['latitude'];
                        $gps_summary['start_longitude'] = (float)$first_loc['longitude'];
                    }
                    if (!empty($last_loc)) {
                        $gps_summary['end_latitude']    = (float)$last_loc['latitude'];
                        $gps_summary['end_longitude']   = (float)$last_loc['longitude'];
                    }
                }
            }

            // Route stops and completion
            $stops_completed = 0;
            $total_stops = 0;
            if ($route_id && $this->db->table_exists('route_pickup_point')) {
                $total_stops = (int)$this->db->where('transport_route_id', $route_id)->count_all_results('route_pickup_point');
                if ($t['status'] === 'completed') {
                    $stops_completed = $total_stops;
                }
            }

            // Students count for this route
            $students_count = 0;
            if ($route_id) {
                $students_count = count($this->_get_route_students($route_id));
            }

            $result_trips[] = [
                'trip_id'            => (int)$t['trip_id'],
                'route_id'           => $route_id,
                'route_name'         => (string)$t['route_name'],
                'bus_id'             => $t['bus_id'],
                'bus_number'         => (string)$t['bus_number'],
                'vehicle_model'      => (string)$t['vehicle_model'],
                'driver_name'        => (string)$t['driver_name'],
                'driver_contact'     => (string)$t['driver_contact'],
                'driver_licence'     => (string)$t['driver_licence'],
                'attender_name'      => (string)$t['attender_name'],
                'trip_type'          => (string)$t['trip_type'],
                'status'             => (string)$t['status'],
                'start_time'         => $t['start_time'],
                'end_time'           => $t['end_time'],
                'duration_minutes'   => $duration_mins,
                'duration_formatted' => $duration_formatted,
                'total_stops'        => $total_stops,
                'stops_completed'    => $stops_completed,
                'students_count'     => $students_count,
                'note'               => (string)$t['note'],
                'gps_summary'        => $gps_summary,
                'created_at'         => $t['created_at']
            ];
        }

        return [
            'total_records'   => count($result_trips),
            'filters_applied' => array_filter($filters),
            'trips'           => $result_trips
        ];
    }
}

