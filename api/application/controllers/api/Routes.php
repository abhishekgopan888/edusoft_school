<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Routes extends Base
{
    public $swagger_meta = [
        'tag' => 'Route Management',
        'description' => 'Endpoints for managing and retrieving school transport routes, assigned buses (vehicles), and pickup points. Requires X-API-Key or Bearer token.',
        'methods' => [
            'index' => [
                'path' => '/api/routes',
                'summary' => 'Get routes by bus ID or all routes',
                'description' => 'Retrieves transport routes with their assigned bus details (bus number, model, driver, attender) and all pickup points. Pass bus_id (or busid / vehicle_id) to filter routes for a specific bus.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bus_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter routes by Bus / Vehicle ID (e.g. 5, 3)',
                        'schema' => ['type' => 'integer', 'example' => 3]
                    ],
                    [
                        'name' => 'busid',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Alias for bus_id',
                        'schema' => ['type' => 'integer', 'example' => 3]
                    ],
                    [
                        'name' => 'vehicle_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Alias for bus_id (Vehicle ID)',
                        'schema' => ['type' => 'integer', 'example' => 3]
                    ],
                    [
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by specific Route ID',
                        'schema' => ['type' => 'integer', 'example' => 3]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'List of routes and assigned pickup points retrieved successfully'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing authentication token / API key']
                ]
            ],
            'by_bus' => [
                'path' => '/api/routes/by_bus/{bus_id}',
                'summary' => 'Get routes by specific bus ID',
                'description' => 'Retrieves all routes, assigned bus details, driver, attender, and pickup points for a specific bus ID.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bus_id',
                        'in' => 'path',
                        'required' => true,
                        'description' => 'Bus / Vehicle ID (e.g. 3, 5)',
                        'schema' => ['type' => 'integer', 'example' => 3]
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
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Route ID',
                        'schema' => ['type' => 'integer', 'example' => 3]
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
        $this->load->model('vehroute_model');
    }

    /**
     * GET /api/routes
     * Query parameters: bus_id, busid, vehicle_id, route_id, id
     */
    public function index()
    {
        $this->_check_auth();

        $bus_id = $this->input->get('bus_id');
        if ($bus_id === null || $bus_id === '') {
            $bus_id = $this->input->get('busid');
        }
        if ($bus_id === null || $bus_id === '') {
            $bus_id = $this->input->get('vehicle_id');
        }

        $route_id = $this->input->get('route_id');
        if ($route_id === null || $route_id === '') {
            $route_id = $this->input->get('id');
        }

        $routes = $this->_get_routes_data($bus_id, $route_id);

        $msg = !empty($bus_id) 
            ? 'Routes for bus ID ' . $bus_id . ' retrieved successfully' 
            : 'Routes retrieved successfully';

        $this->_success($msg, $routes);
    }

    /**
     * GET /api/routes/by_bus/{bus_id}
     */
    public function by_bus($bus_id = null)
    {
        $this->_check_auth();

        if ($bus_id === null || $bus_id === '') {
            $bus_id = $this->input->get('bus_id');
        }
        if ($bus_id === null || $bus_id === '') {
            $bus_id = $this->input->get('busid');
        }

        if (empty($bus_id) || !is_numeric($bus_id)) {
            $this->_error('Valid bus_id is required', 400);
            return;
        }

        $routes = $this->_get_routes_data($bus_id, null);

        $this->_success('Routes for bus ID ' . $bus_id . ' retrieved successfully', $routes);
    }

    /**
     * GET /api/routes/details?id={route_id}
     */
    public function details()
    {
        $this->_check_auth();

        $id = $this->input->get('id');
        if (empty($id)) {
            $id = $this->input->get('route_id');
        }

        if (empty($id) || !is_numeric($id)) {
            $this->_error('Route ID is required', 400);
            return;
        }

        $routes = $this->_get_routes_data(null, $id);
        if (empty($routes)) {
            $this->_error('Route not found', 404);
            return;
        }

        $this->_success('Route details retrieved successfully', $routes[0]);
    }

    /**
     * Query routes with bus and pickup points
     */
    private function _get_routes_data($bus_id = null, $route_id = null)
    {
        $this->db->select('
            transport_route.id as route_id,
            transport_route.route_title,
            transport_route.no_of_vehicle,
            transport_route.note,
            vehicle_routes.id as vehicle_route_id,
            vehicle_routes.vehicle_id as bus_id,
            vehicles.vehicle_no as bus_no,
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

        if (!empty($bus_id)) {
            $this->db->where('vehicle_routes.vehicle_id', $bus_id);
        }

        if (!empty($route_id)) {
            $this->db->where('transport_route.id', $route_id);
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

            $route['pickup_points'] = $pickup_points;
            $route['total_pickup_points'] = count($pickup_points);

            $result[] = $route;
        }

        return $result;
    }
}

