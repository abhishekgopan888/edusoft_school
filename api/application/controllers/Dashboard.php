<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Dashboard API Controller
 * API Key: dashboard-api-secret-key-2026
 */
class Dashboard extends CI_Controller
{
    public $swagger_meta = [
        'tag' => 'Dashboard',
        'description' => 'Dashboard API returning Bus details, Route details, and Student transport status (Total, Boarded, Dropped, Pending) under a single API key: dashboard-api-secret-key-2026',
        'methods' => [
            'index' => [
                'path' => '/dashboard',
                'summary' => 'Get Dashboard summary (Bus details, Route details, Student boarding stats)',
                'description' => 'Retrieves complete dashboard overview including bus details, route details, total students, total students boarded, total students dropped, and total pending students in a single response.',
                'http_method' => 'get',
                'security' => [
                    ['ApiKeyAuth' => []],
                    ['BearerAuth' => []]
                ],
                'parameters' => [
                    [
                        'name' => 'api_key',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'API Key for authentication. Defaults to dashboard-api-secret-key-2026 if omitted.',
                        'schema' => ['type' => 'string', 'example' => 'dashboard-api-secret-key-2026']
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Date for attendance/boarding stats (YYYY-MM-DD). Defaults to today.',
                        'schema' => ['type' => 'string', 'example' => '2026-08-05']
                    ],
                    [
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter dashboard data by specific Route ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'vehicle_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter dashboard data by specific Vehicle ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Dashboard data retrieved successfully'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing API key']
                ]
            ]
        ]
    ];

    private $valid_keys = [
        'dashboard-api-secret-key-2026',
        'system-admin-api-key-2026'
    ];

    private function _check_auth()
    {
        $api_key = $this->input->get_request_header('X-API-Key', true);
        if (empty($api_key)) {
            $api_key = $this->input->get('api_key');
        }
        if (empty($api_key)) {
            $api_key = $this->input->get('X-API-Key');
        }
        if (empty($api_key)) {
            $auth = $this->input->get_request_header('Authorization', true);
            if ($auth && preg_match('/Bearer\s+(.*)$/i', $auth, $m)) {
                $api_key = trim($m[1]);
            }
        }
        if (empty($api_key) && isset($_SERVER['HTTP_X_API_KEY'])) {
            $api_key = $_SERVER['HTTP_X_API_KEY'];
        }

        if (!empty($api_key) && in_array($api_key, $this->valid_keys)) {
            return true;
        }
        if (!empty($api_key)) {
            $session = $this->db->select('expired_at')->from('users_authentication')->where('token', $api_key)->get()->row();
            if ($session && $session->expired_at >= date('Y-m-d H:i:s')) {
                return true;
            }
        }
        $this->output->set_status_header(401)->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized - Invalid or missing X-API-Key']));
        exit;
    }

    private function _success($message, $data = [], $code = 200)
    {
        $payload = ['status' => 'success', 'message' => $message];
        if (!empty($data) || is_array($data)) {
            $payload['data'] = $data;
        }
        $this->output->set_status_header($code)->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function _error($message, $code = 400)
    {
        $this->output->set_status_header($code)->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'error', 'message' => $message]));
    }

    public function index()
    {
        $this->_check_auth();

        $date = $this->input->get('date') ? trim($this->input->get('date')) : date('Y-m-d');
        $route_id = $this->input->get('route_id') ? (int)$this->input->get('route_id') : null;
        $vehicle_id = $this->input->get('vehicle_id') ? (int)$this->input->get('vehicle_id') : null;

        // 1. Fetch Bus Details
        if ($vehicle_id) {
            $this->db->where('id', $vehicle_id);
        }
        $buses_query = $this->db->order_by('id', 'asc')->get('vehicles')->result_array();

        $buses = [];
        $total_buses = 0;
        $active_buses = 0;
        $maintenance_buses = 0;

        foreach ($buses_query as $v) {
            $v_id = (int)$v['id'];

            // Get vehicle_routes mapping for this vehicle
            $vr = $this->db->select('vr.*, tr.route_title')
                           ->from('vehicle_routes vr')
                           ->join('transport_route tr', 'tr.id = vr.route_id', 'left')
                           ->where('vr.vehicle_id', $v_id)
                           ->get()->row_array();

            if ($route_id && (!isset($vr['route_id']) || (int)$vr['route_id'] !== $route_id)) {
                continue;
            }

            // Driver resolution: check vehicles.driver_id first, then vehicle_routes.driver_id
            $final_driver_id = !empty($v['driver_id']) ? (int)$v['driver_id'] : (!empty($vr['driver_id']) ? (int)$vr['driver_id'] : null);
            $driver_name = null;
            $driver_contact = null;
            $driver_details = null;

            if ($final_driver_id) {
                $driver_row = $this->db->get_where('drivers', ['id' => $final_driver_id])->row_array();
                if ($driver_row) {
                    $driver_name = $driver_row['driver_name'];
                    $driver_contact = isset($driver_row['contact']) ? $driver_row['contact'] : null;
                    $driver_details = [
                        'id' => (int)$driver_row['id'],
                        'driver_name' => $driver_row['driver_name'],
                        'age' => isset($driver_row['age']) ? $driver_row['age'] : null,
                        'licence' => isset($driver_row['licence']) ? $driver_row['licence'] : null,
                        'contact' => isset($driver_row['contact']) ? $driver_row['contact'] : null,
                        'address' => isset($driver_row['address']) ? $driver_row['address'] : null,
                        'driver_image' => isset($driver_row['driver_image']) ? $driver_row['driver_image'] : null,
                        'status' => isset($driver_row['status']) ? $driver_row['status'] : null
                    ];
                }
            }

            // Attender resolution: check vehicle_routes.attender_id
            $final_attender_id = !empty($vr['attender_id']) ? (int)$vr['attender_id'] : null;
            $attender_name = null;
            $attender_details = null;

            if ($final_attender_id) {
                $attender_row = $this->db->get_where('attenders', ['id' => $final_attender_id])->row_array();
                if ($attender_row) {
                    $attender_name = $attender_row['attender_name'];
                    $attender_details = [
                        'id' => (int)$attender_row['id'],
                        'attender_name' => $attender_row['attender_name'],
                        'age' => isset($attender_row['age']) ? $attender_row['age'] : null,
                        'address' => isset($attender_row['address']) ? $attender_row['address'] : null,
                        'id_proof' => isset($attender_row['id_proof']) ? $attender_row['id_proof'] : null,
                        'attender_image' => isset($attender_row['attender_image']) ? $attender_row['attender_image'] : null,
                        'documents' => isset($attender_row['documents']) ? $attender_row['documents'] : null,
                        'note' => isset($attender_row['note']) ? $attender_row['note'] : null,
                        'status' => isset($attender_row['status']) ? $attender_row['status'] : null
                    ];
                }
            }

            // Count assigned students for this vehicle
            $assigned_count = 0;
            if (!empty($vr['id'])) {
                $this->db->from('student_session ss');
                $this->db->join('students s', 's.id = ss.student_id');
                $this->db->where('ss.vehroute_id', $vr['id']);
                $this->db->where('s.is_active', 'yes');
                $assigned_count = $this->db->count_all_results();
            }

            $st = strtolower($v['status']);
            if ($st === 'active') {
                $active_buses++;
            } elseif ($st === 'maintanance' || $st === 'maintenance') {
                $maintenance_buses++;
            }
            $total_buses++;

            $buses[] = [
                'vehicle_id' => $v_id,
                'vehicle_no' => $v['vehicle_no'],
                'vehicle_model' => $v['vehicle_model'],
                'max_seating_capacity' => $v['max_seating_capacity'],
                'status' => $v['status'],
                'vehicle_photo' => $v['vehicle_photo'],
                'driver_id' => $final_driver_id,
                'driver_name' => $driver_name,
                'driver_contact' => $driver_contact,
                'driver_details' => $driver_details,
                'attender_id' => $final_attender_id,
                'attender_name' => $attender_name,
                'attender_details' => $attender_details,
                'route_id' => !empty($vr['route_id']) ? (int)$vr['route_id'] : null,
                'route_title' => !empty($vr['route_title']) ? $vr['route_title'] : null,
                'assigned_students_count' => $assigned_count
            ];
        }

        // 2. Fetch Route Details
        $this->db->select('tr.*');
        $this->db->from('transport_route tr');
        if ($route_id) {
            $this->db->where('tr.id', $route_id);
        }
        $routes_raw = $this->db->get()->result_array();

        $routes = [];
        $total_routes = count($routes_raw);

        foreach ($routes_raw as $r) {
            $r_id = (int)$r['id'];

            // Get vehicles on this route
            $this->db->select('v.id as vehicle_id, v.vehicle_no, v.vehicle_model');
            $this->db->from('vehicle_routes vr');
            $this->db->join('vehicles v', 'v.id = vr.vehicle_id');
            $this->db->where('vr.route_id', $r_id);
            $r_vehicles = $this->db->get()->result_array();

            // Get pickup points on this route
            $this->db->select('rpp.id, rpp.fees, rpp.pickup_time, pp.name as pickup_point_name');
            $this->db->from('route_pickup_point rpp');
            $this->db->join('pickup_point pp', 'pp.id = rpp.pickup_point_id', 'left');
            $this->db->where('rpp.transport_route_id', $r_id);
            $r_pickups = $this->db->get()->result_array();

            // Assigned students count
            $this->db->from('student_session ss');
            $this->db->join('students s', 's.id = ss.student_id');
            $this->db->join('vehicle_routes vr', 'vr.id = ss.vehroute_id');
            $this->db->where('vr.route_id', $r_id);
            $this->db->where('s.is_active', 'yes');
            $r_students_count = $this->db->count_all_results();

            $routes[] = [
                'route_id' => $r_id,
                'route_title' => $r['route_title'],
                'no_of_vehicles' => count($r_vehicles),
                'note' => $r['note'],
                'is_active' => $r['is_active'],
                'vehicles' => $r_vehicles,
                'pickup_points' => $r_pickups,
                'assigned_students_count' => $r_students_count
            ];
        }

        // 3. Fetch Student Transport & Boarding Details
        $this->db->select('
            s.id as student_id,
            CONCAT_WS(" ", s.firstname, s.middlename, s.lastname) as student_name,
            s.admission_no,
            s.roll_no,
            s.mobileno,
            c.class,
            sec.section,
            tr.id as route_id,
            tr.route_title,
            v.id as vehicle_id,
            v.vehicle_no,
            pp.name as pickup_point_name
        ');
        $this->db->from('student_session ss');
        $this->db->join('students s', 's.id = ss.student_id');
        $this->db->join('classes c', 'c.id = ss.class_id', 'left');
        $this->db->join('sections sec', 'sec.id = ss.section_id', 'left');
        $this->db->join('vehicle_routes vr', 'vr.id = ss.vehroute_id', 'left');
        $this->db->join('transport_route tr', 'tr.id = vr.route_id', 'left');
        $this->db->join('vehicles v', 'v.id = vr.vehicle_id', 'left');
        $this->db->join('route_pickup_point rpp', 'rpp.id = ss.route_pickup_point_id', 'left');
        $this->db->join('pickup_point pp', 'pp.id = rpp.pickup_point_id', 'left');
        $this->db->where('s.is_active', 'yes');

        if ($route_id) {
            $this->db->where('vr.route_id', $route_id);
        }
        if ($vehicle_id) {
            $this->db->where('vr.vehicle_id', $vehicle_id);
        }

        $students_raw = $this->db->get()->result_array();

        // Check if any transport attendance records exist for given date
        $attendance_map = [];
        if (!empty($date)) {
            $this->db->select('student_session_id, attendence_type_id, remark');
            $this->db->from('student_attendences');
            $this->db->where('date', $date);
            $att_records = $this->db->get()->result_array();
            foreach ($att_records as $att) {
                $attendance_map[$att['student_session_id']] = $att;
            }
        }

        $students_list = [];
        $total_students = count($students_raw);
        $total_boarded = 0;
        $total_dropped = 0;
        $total_pending = 0;

        foreach ($students_raw as $s_item) {
            $s_id = (int)$s_item['student_id'];
            $b_status = 'pending';

            if (isset($attendance_map[$s_id])) {
                $att_type = (int)$attendance_map[$s_id]['attendence_type_id'];
                if ($att_type === 1) {
                    $b_status = 'boarded';
                } elseif ($att_type === 6 || strpos(strtolower($attendance_map[$s_id]['remark']), 'drop') !== false) {
                    $b_status = 'dropped';
                }
            }

            if ($b_status === 'boarded') {
                $total_boarded++;
            } elseif ($b_status === 'dropped') {
                $total_dropped++;
            } else {
                $total_pending++;
            }

            $students_list[] = [
                'student_id' => $s_id,
                'student_name' => trim($s_item['student_name']),
                'admission_no' => $s_item['admission_no'],
                'roll_no' => $s_item['roll_no'],
                'class' => $s_item['class'],
                'section' => $s_item['section'],
                'route_id' => $s_item['route_id'] ? (int)$s_item['route_id'] : null,
                'route_title' => $s_item['route_title'],
                'vehicle_id' => $s_item['vehicle_id'] ? (int)$s_item['vehicle_id'] : null,
                'vehicle_no' => $s_item['vehicle_no'],
                'pickup_point' => $s_item['pickup_point_name'],
                'boarding_status' => $b_status
            ];
        }

        $boarding_progress_percentage = $total_students > 0 ? (float)number_format((($total_boarded + $total_dropped) / $total_students) * 100, 2, '.', '') : 0.00;
        $boarded_percentage = $total_students > 0 ? (float)number_format(($total_boarded / $total_students) * 100, 2, '.', '') : 0.00;
        $dropped_percentage = $total_students > 0 ? (float)number_format(($total_dropped / $total_students) * 100, 2, '.', '') : 0.00;
        $pending_percentage = $total_students > 0 ? (float)number_format(($total_pending / $total_students) * 100, 2, '.', '') : 0.00;

        $response_data = [
            'date' => $date,
            'bus_details' => [
                'summary' => [
                    'total_buses' => $total_buses,
                    'active_buses' => $active_buses,
                    'maintenance_buses' => $maintenance_buses
                ],
                'buses' => $buses
            ],
            'route_details' => [
                'summary' => [
                    'total_routes' => $total_routes
                ],
                'routes' => $routes
            ],
            'student_stats' => [
                'total_students' => $total_students,
                'total_boarded' => $total_boarded,
                'total_dropped' => $total_dropped,
                'total_pending' => $total_pending,
                'boarding_progress_percentage' => $boarding_progress_percentage,
                'boarded_percentage' => $boarded_percentage,
                'dropped_percentage' => $dropped_percentage,
                'pending_percentage' => $pending_percentage,
                'students' => $students_list
            ]
        ];

        $this->_success('Dashboard details retrieved successfully', $response_data);
    }
}
