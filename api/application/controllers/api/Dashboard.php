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
        'description' => 'Dashboard API returning route-wise student boarding summary (route, total_students, boarded, pending, dropped, boarding_progress) under a single API key: dashboard-api-secret-key-2026',
        'methods' => [
            'index' => [
                'path' => '/dashboard',
                'summary' => 'Get Route-based Dashboard summary (Total students, Boarded, Pending, Dropped, Boarding progress)',
                'description' => 'Retrieves route-based dashboard summary containing only route details, total students in the route, boarded count, pending count, dropped count, and boarding progress percentage.',
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
                    '200' => ['description' => 'Route dashboard data retrieved successfully'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing API key'],
                    '404' => ['description' => 'Route not found']
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

        $date       = $this->input->get('date') ? trim($this->input->get('date')) : date('Y-m-d');
        $route_id   = $this->input->get('route_id') ? (int)$this->input->get('route_id') : null;
        $vehicle_id = $this->input->get('vehicle_id') ? (int)$this->input->get('vehicle_id') : null;

        // 1. Fetch Transport Attendance Map for Date
        $attendance_map = [];
        if (!empty($date) && $this->db->table_exists('student_attendences')) {
            $att_records = $this->db->select('student_session_id, attendence_type_id, remark')
                                    ->from('student_attendences')
                                    ->where('date', $date)
                                    ->get()->result_array();
            foreach ($att_records as $att) {
                $attendance_map[$att['student_session_id']] = $att;
            }
        }

        // 2. Fetch Active Students Assigned to Transport
        $all_students = [];
        if ($this->db->table_exists('student_session') && $this->db->table_exists('students')) {
            $this->db->select('
                s.id as student_id,
                s.boarding_status,
                ss.id as student_session_id,
                COALESCE(vr.route_id, rpp.transport_route_id) as route_id,
                vr.vehicle_id
            ')
            ->from('student_session ss')
            ->join('students s', 's.id = ss.student_id')
            ->join('route_pickup_point rpp', 'rpp.id = ss.route_pickup_point_id', 'left')
            ->join('vehicle_routes vr', 'vr.id = ss.vehroute_id', 'left')
            ->where('s.is_active', 'yes')
            ->where('((ss.vehroute_id IS NOT NULL AND ss.vehroute_id > 0) OR (ss.route_pickup_point_id IS NOT NULL AND ss.route_pickup_point_id > 0))', null, false);

            if ($route_id) {
                $this->db->where('(vr.route_id = ' . $route_id . ' OR rpp.transport_route_id = ' . $route_id . ')', null, false);
            }
            if ($vehicle_id) {
                $this->db->where('vr.vehicle_id', $vehicle_id);
            }

            $all_students = $this->db->get()->result_array();
        }

        // Group students by route and resolve boarding status
        $students_by_route = [];
        foreach ($all_students as $st) {
            $s_id  = (int)$st['student_id'];
            $ss_id = !empty($st['student_session_id']) ? (int)$st['student_session_id'] : $s_id;

            $b_status = !empty($st['boarding_status']) ? strtolower(trim($st['boarding_status'])) : 'pending';
            if (isset($attendance_map[$ss_id])) {
                $att_type = (int)$attendance_map[$ss_id]['attendence_type_id'];
                if ($att_type === 1) {
                    $b_status = 'boarded';
                } elseif ($att_type === 6 || (isset($attendance_map[$ss_id]['remark']) && strpos(strtolower($attendance_map[$ss_id]['remark']), 'drop') !== false)) {
                    $b_status = 'dropped';
                }
            }

            $r_id = !empty($st['route_id']) ? (int)$st['route_id'] : 0;
            if ($r_id > 0) {
                $students_by_route[$r_id][] = $b_status;
            }
        }

        // 3. Fetch Routes
        $routes_query = [];
        if ($this->db->table_exists('transport_route')) {
            $this->db->select('tr.id, tr.route_title');
            if ($route_id) {
                $this->db->where('tr.id', $route_id);
            }
            if ($vehicle_id && $this->db->table_exists('vehicle_routes')) {
                $this->db->join('vehicle_routes vr_filter', 'vr_filter.route_id = tr.id', 'inner');
                $this->db->where('vr_filter.vehicle_id', $vehicle_id);
                $this->db->group_by('tr.id');
            }
            $this->db->order_by('tr.id', 'ASC');
            $routes_query = $this->db->get('transport_route tr')->result_array();
        }

        if ($route_id && empty($routes_query)) {
            $this->_error('Route not found', 404);
            return;
        }

        $routes_list = [];
        $all_total   = 0;
        $all_boarded = 0;
        $all_dropped = 0;
        $all_pending = 0;

        foreach ($routes_query as $r) {
            $r_id = (int)$r['id'];
            $statuses = isset($students_by_route[$r_id]) ? $students_by_route[$r_id] : [];

            $r_total   = count($statuses);
            $r_boarded = 0;
            $r_dropped = 0;
            $r_pending = 0;

            foreach ($statuses as $b_st) {
                if ($b_st === 'boarded') {
                    $r_boarded++;
                } elseif ($b_st === 'dropped') {
                    $r_dropped++;
                } else {
                    $r_pending++;
                }
            }

            $r_progress = $r_total > 0 ? (float)number_format((($r_boarded + $r_dropped) / $r_total) * 100, 2, '.', '') : 0.00;

            $all_total   += $r_total;
            $all_boarded += $r_boarded;
            $all_dropped += $r_dropped;
            $all_pending += $r_pending;

            $routes_list[] = [
                'route_id'          => $r_id,
                'route_title'       => $r['route_title'],
                'total_students'    => $r_total,
                'boarded'           => $r_boarded,
                'pending'           => $r_pending,
                'dropped'           => $r_dropped,
                'boarding_progress' => $r_progress
            ];
        }

        if ($route_id && !empty($routes_list)) {
            $response_data = $routes_list[0];
        } else {
            $all_progress = $all_total > 0 ? (float)number_format((($all_boarded + $all_dropped) / $all_total) * 100, 2, '.', '') : 0.00;
            $response_data = [
                'total_routes'      => count($routes_list),
                'total_students'    => $all_total,
                'boarded'           => $all_boarded,
                'pending'           => $all_pending,
                'dropped'           => $all_dropped,
                'boarding_progress' => $all_progress
            ];
        }

        $this->_success('Dashboard route details retrieved successfully', $response_data);
    }
}
