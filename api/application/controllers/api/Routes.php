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
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Route ID (e.g. 1, 2, 5). Alias: routeid, id',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'vehicle_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Vehicle / Bus ID (e.g. 2, 3, 5). Alias: vehicleid, bus_id, busid',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'pickup_point_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Pickup Point ID (e.g. 1, 2, 4). Alias: pickuppointid',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'pickup_point_name',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter routes by Pickup Point Name (e.g. Kanyakumarii, Demo Location). Case-insensitive search.',
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
                        'required' => true,
                        'description' => 'Route ID',
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
     * Query parameters: route_id, vehicle_id, pickup_point_id, pickup_point_name (and aliases)
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

        $routes = $this->_get_routes_data($vehicle_id, $route_id, $pickup_point_id, $pickup_point_name);

        $filters = [];
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

        if (empty($bus_id) || !is_numeric($bus_id)) {
            $this->_error('Valid bus_id or vehicle_id is required', 400);
            return;
        }

        $routes = $this->_get_routes_data($bus_id, null, null, null);

        $this->_success('Routes for vehicle/bus ID ' . $bus_id . ' retrieved successfully', $routes);
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

        if (empty($id) || !is_numeric($id)) {
            $this->_error('Route ID is required', 400);
            return;
        }

        $routes = $this->_get_routes_data(null, $id, null, null);
        if (empty($routes)) {
            $this->_error('Route not found', 404);
            return;
        }

        $this->_success('Route details retrieved successfully', $routes[0]);
    }

    /**
     * Query routes with vehicle and pickup points
     */
    private function _get_routes_data($vehicle_id = null, $route_id = null, $pickup_point_id = null, $pickup_point_name = null)
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

        if (!empty($vehicle_id)) {
            $this->db->where('vehicle_routes.vehicle_id', $vehicle_id);
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

            $result[] = $route;
        }

        return $result;
    }
}
