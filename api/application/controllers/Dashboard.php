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
                'description' => 'Retrieves complete dashboard overview including bus details, route-based dashboard details (stops, vehicles, assigned students, boarding stats), total students, total students boarded, total students dropped, and total pending students in a single response.',
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
                        'description' => 'Filter dashboard data by specific Route ID to get corresponding route-based dashboard details',
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

        $date       = $this->input->get('date') ? trim($this->input->get('date')) : date('Y-m-d');
        $route_id   = $this->input->get('route_id') ? (int)$this->input->get('route_id') : null;
        $vehicle_id = $this->input->get('vehicle_id') ? (int)$this->input->get('vehicle_id') : null;

        // ---------------------------------------------------------
        // 1. Fetch Transport Attendance Map for Date
        // ---------------------------------------------------------
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

        // ---------------------------------------------------------
        // 2. Fetch All Active Students with Transport Assignment
        // ---------------------------------------------------------
        $all_students = [];
        if ($this->db->table_exists('student_session') && $this->db->table_exists('students')) {
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
                ss.id as student_session_id,
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

            $all_students = $this->db->get()->result_array();
        }

        // Map boarding status and index students by route & stop
        $students_by_route = [];
        $students_by_stop  = [];
        $students_by_vehicle = [];

        foreach ($all_students as &$st) {
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
            $st['boarding_status'] = $b_status;

            $r_id = !empty($st['route_id']) ? (int)$st['route_id'] : 0;
            if ($r_id > 0) {
                $students_by_route[$r_id][] = $st;
            }

            $rpp_id = !empty($st['route_pickup_point_id']) ? (int)$st['route_pickup_point_id'] : 0;
            if ($rpp_id > 0) {
                $students_by_stop[$rpp_id][] = [
                    'student_id'      => $s_id,
                    'student_name'    => trim($st['student_name']),
                    'admission_no'    => $st['admission_no'],
                    'roll_no'         => $st['roll_no'],
                    'class'           => $st['class'],
                    'section'         => $st['section'],
                    'student_phone'   => $st['student_phone'],
                    'father_name'     => $st['father_name'],
                    'father_phone'    => $st['father_phone'],
                    'guardian_name'   => $st['guardian_name'],
                    'guardian_phone'  => $st['guardian_phone'],
                    'boarding_status' => $b_status
                ];
            }

            $v_id = !empty($st['vehicle_id']) ? (int)$st['vehicle_id'] : 0;
            if ($v_id > 0) {
                $students_by_vehicle[$v_id][] = $st;
            }
        }
        unset($st);

        // ---------------------------------------------------------
        // 3. Fetch Pickup Points / Stops Grouped by Route
        // ---------------------------------------------------------
        $stops_by_route = [];
        if ($this->db->table_exists('route_pickup_point')) {
            $this->db->select('rpp.*, pp.name as pickup_point_name');
            $this->db->from('route_pickup_point rpp');
            $this->db->join('pickup_point pp', 'pp.id = rpp.pickup_point_id', 'left');
            $this->db->order_by('rpp.transport_route_id', 'ASC');
            $this->db->order_by('rpp.order_number', 'ASC');
            $this->db->order_by('rpp.id', 'ASC');
            $rpp_rows = $this->db->get()->result_array();

            foreach ($rpp_rows as $sp) {
                $tr_id  = (int)$sp['transport_route_id'];
                $rpp_id = (int)$sp['id'];
                $stop_students = isset($students_by_stop[$rpp_id]) ? $students_by_stop[$rpp_id] : [];

                $stops_by_route[$tr_id][] = [
                    'route_pickup_point_id' => $rpp_id,
                    'pickup_point_id'       => !empty($sp['pickup_point_id']) ? (int)$sp['pickup_point_id'] : null,
                    'pickup_point_name'     => $sp['pickup_point_name'],
                    'pickup_time'           => $sp['pickup_time'],
                    'fees'                  => $sp['fees'],
                    'destination_distance'  => $sp['destination_distance'],
                    'order_number'          => !empty($sp['order_number']) ? (int)$sp['order_number'] : 1,
                    'point_type'            => !empty($sp['point_type']) ? $sp['point_type'] : 'stop',
                    'students_count'        => count($stop_students),
                    'students'              => $stop_students
                ];
            }
        }

        // ---------------------------------------------------------
        // 4. Fetch Vehicles & Vehicle Routes Mapping
        // ---------------------------------------------------------
        $all_buses = [];
        $total_buses = 0;
        $active_buses = 0;
        $maintenance_buses = 0;

        if ($this->db->table_exists('vehicles')) {
            $this->db->select('v.*');
            if ($vehicle_id) {
                $this->db->where('v.id', $vehicle_id);
            }
            $vehicles_query = $this->db->order_by('v.id', 'ASC')->get('vehicles')->result_array();

            foreach ($vehicles_query as $v) {
                $v_id = (int)$v['id'];

                $vr = null;
                if ($this->db->table_exists('vehicle_routes')) {
                    $vr = $this->db->select('vr.*, tr.route_title')
                                   ->from('vehicle_routes vr')
                                   ->join('transport_route tr', 'tr.id = vr.route_id', 'left')
                                   ->where('vr.vehicle_id', $v_id)
                                   ->get()->row_array();
                }

                // If filtering by route_id, skip vehicles not on this route
                if ($route_id && (!isset($vr['route_id']) || (int)$vr['route_id'] !== $route_id)) {
                    continue;
                }

                // Driver resolution
                $final_driver_id = !empty($v['driver_id']) ? (int)$v['driver_id'] : (!empty($vr['driver_id']) ? (int)$vr['driver_id'] : null);
                $driver_name = null;
                $driver_contact = null;
                $driver_details = null;

                if ($final_driver_id && $this->db->table_exists('drivers')) {
                    $driver_row = $this->db->get_where('drivers', ['id' => $final_driver_id])->row_array();
                    if ($driver_row) {
                        $driver_name    = $driver_row['driver_name'];
                        $driver_contact = isset($driver_row['contact']) ? $driver_row['contact'] : null;
                        $driver_details = [
                            'id'           => (int)$driver_row['id'],
                            'driver_name'  => $driver_row['driver_name'],
                            'age'          => isset($driver_row['age']) ? $driver_row['age'] : null,
                            'licence'      => isset($driver_row['licence']) ? $driver_row['licence'] : null,
                            'contact'      => isset($driver_row['contact']) ? $driver_row['contact'] : null,
                            'address'      => isset($driver_row['address']) ? $driver_row['address'] : null,
                            'driver_image' => isset($driver_row['driver_image']) ? $driver_row['driver_image'] : null,
                            'status'       => isset($driver_row['status']) ? $driver_row['status'] : null
                        ];
                    }
                }

                // Attender resolution
                $final_attender_id = !empty($vr['attender_id']) ? (int)$vr['attender_id'] : null;
                $attender_name = null;
                $attender_details = null;

                if ($final_attender_id && $this->db->table_exists('attenders')) {
                    $attender_row = $this->db->get_where('attenders', ['id' => $final_attender_id])->row_array();
                    if ($attender_row) {
                        $attender_name = $attender_row['attender_name'];
                        $attender_details = [
                            'id'             => (int)$attender_row['id'],
                            'attender_name'  => $attender_row['attender_name'],
                            'age'            => isset($attender_row['age']) ? $attender_row['age'] : null,
                            'address'        => isset($attender_row['address']) ? $attender_row['address'] : null,
                            'id_proof'       => isset($attender_row['id_proof']) ? $attender_row['id_proof'] : null,
                            'attender_image' => isset($attender_row['attender_image']) ? $attender_row['attender_image'] : null,
                            'documents'      => isset($attender_row['documents']) ? $attender_row['documents'] : null,
                            'note'           => isset($attender_row['note']) ? $attender_row['note'] : null,
                            'status'         => isset($attender_row['status']) ? $attender_row['status'] : null
                        ];
                    }
                }

                $assigned_count = isset($students_by_vehicle[$v_id]) ? count($students_by_vehicle[$v_id]) : 0;

                $st_val = strtolower($v['status']);
                if ($st_val === 'active') {
                    $active_buses++;
                } elseif ($st_val === 'maintanance' || $st_val === 'maintenance') {
                    $maintenance_buses++;
                }
                $total_buses++;

                $all_buses[] = [
                    'vehicle_id'              => $v_id,
                    'vehicle_no'              => $v['vehicle_no'],
                    'vehicle_model'           => $v['vehicle_model'],
                    'max_seating_capacity'    => $v['max_seating_capacity'],
                    'status'                  => $v['status'],
                    'vehicle_photo'           => $v['vehicle_photo'],
                    'driver_id'               => $final_driver_id,
                    'driver_name'             => $driver_name,
                    'driver_contact'          => $driver_contact,
                    'driver_details'          => $driver_details,
                    'attender_id'             => $final_attender_id,
                    'attender_name'           => $attender_name,
                    'attender_details'        => $attender_details,
                    'route_id'                => !empty($vr['route_id']) ? (int)$vr['route_id'] : null,
                    'route_title'             => !empty($vr['route_title']) ? $vr['route_title'] : null,
                    'assigned_students_count' => $assigned_count
                ];
            }
        }

        // ---------------------------------------------------------
        // 5. Fetch Route Details with Full Route-Based Dashboard
        // ---------------------------------------------------------
        $routes_query = [];
        if ($this->db->table_exists('transport_route')) {
            $this->db->select('tr.*');
            if ($route_id) {
                $this->db->where('tr.id', $route_id);
            }
            $this->db->order_by('tr.id', 'ASC');
            $routes_query = $this->db->get('transport_route tr')->result_array();
        }

        $routes_list                 = [];
        $total_route_vehicles_all    = 0;
        $total_route_stops_all       = 0;
        $total_route_students_all    = 0;
        $total_route_boarded_all     = 0;
        $total_route_dropped_all     = 0;
        $total_route_pending_all     = 0;

        foreach ($routes_query as $r) {
            $r_id = (int)$r['id'];

            // Vehicles on this route
            $r_vehicles = [];
            foreach ($all_buses as $bus_item) {
                if (!empty($bus_item['route_id']) && (int)$bus_item['route_id'] === $r_id) {
                    $r_vehicles[] = [
                        'vehicle_id'           => $bus_item['vehicle_id'],
                        'vehicle_no'           => $bus_item['vehicle_no'],
                        'vehicle_model'        => $bus_item['vehicle_model'],
                        'max_seating_capacity' => $bus_item['max_seating_capacity'],
                        'status'               => $bus_item['status'],
                        'driver_id'            => $bus_item['driver_id'],
                        'driver_name'          => $bus_item['driver_name'],
                        'driver_contact'       => $bus_item['driver_contact'],
                        'attender_id'          => $bus_item['attender_id'],
                        'attender_name'        => $bus_item['attender_name']
                    ];
                }
            }

            // Stops on this route
            $r_stops = isset($stops_by_route[$r_id]) ? $stops_by_route[$r_id] : [];

            // Students on this route
            $r_students = isset($students_by_route[$r_id]) ? $students_by_route[$r_id] : [];
            $r_total    = count($r_students);
            $r_boarded  = 0;
            $r_dropped  = 0;
            $r_pending  = 0;

            foreach ($r_students as $st_item) {
                if ($st_item['boarding_status'] === 'boarded') {
                    $r_boarded++;
                } elseif ($st_item['boarding_status'] === 'dropped') {
                    $r_dropped++;
                } else {
                    $r_pending++;
                }
            }

            $r_progress = $r_total > 0 ? (float)number_format((($r_boarded + $r_dropped) / $r_total) * 100, 2, '.', '') : 0.00;
            $r_b_pct    = $r_total > 0 ? (float)number_format(($r_boarded / $r_total) * 100, 2, '.', '') : 0.00;
            $r_d_pct    = $r_total > 0 ? (float)number_format(($r_dropped / $r_total) * 100, 2, '.', '') : 0.00;
            $r_p_pct    = $r_total > 0 ? (float)number_format(($r_pending / $r_total) * 100, 2, '.', '') : 0.00;

            $total_route_vehicles_all += count($r_vehicles);
            $total_route_stops_all    += count($r_stops);
            $total_route_students_all += $r_total;
            $total_route_boarded_all  += $r_boarded;
            $total_route_dropped_all  += $r_dropped;
            $total_route_pending_all  += $r_pending;

            $routes_list[] = [
                'route_id'                     => $r_id,
                'route_title'                  => $r['route_title'],
                'note'                         => $r['note'],
                'is_active'                    => $r['is_active'],
                'total_vehicles'               => count($r_vehicles),
                'vehicles'                     => $r_vehicles,
                'total_stops'                  => count($r_stops),
                'stops'                        => $r_stops,
                'total_students'               => $r_total,
                'total_boarded'                => $r_boarded,
                'total_dropped'                => $r_dropped,
                'total_pending'                => $r_pending,
                'boarding_progress_percentage' => $r_progress,
                'boarded_percentage'           => $r_b_pct,
                'dropped_percentage'           => $r_d_pct,
                'pending_percentage'           => $r_p_pct,
                'students'                     => $r_students
            ];
        }

        // ---------------------------------------------------------
        // 6. Build Student Stats (Overall or Filtered)
        // ---------------------------------------------------------
        $filtered_students = $all_students;
        if ($route_id) {
            $filtered_students = array_values(array_filter($filtered_students, function($s) use ($route_id) {
                return !empty($s['route_id']) && (int)$s['route_id'] === $route_id;
            }));
        }
        if ($vehicle_id) {
            $filtered_students = array_values(array_filter($filtered_students, function($s) use ($vehicle_id) {
                return !empty($s['vehicle_id']) && (int)$s['vehicle_id'] === $vehicle_id;
            }));
        }

        $total_students = count($filtered_students);
        $total_boarded  = 0;
        $total_dropped  = 0;
        $total_pending  = 0;

        foreach ($filtered_students as $st_f) {
            if ($st_f['boarding_status'] === 'boarded') {
                $total_boarded++;
            } elseif ($st_f['boarding_status'] === 'dropped') {
                $total_dropped++;
            } else {
                $total_pending++;
            }
        }

        $overall_progress = $total_students > 0 ? (float)number_format((($total_boarded + $total_dropped) / $total_students) * 100, 2, '.', '') : 0.00;
        $overall_b_pct    = $total_students > 0 ? (float)number_format(($total_boarded / $total_students) * 100, 2, '.', '') : 0.00;
        $overall_d_pct    = $total_students > 0 ? (float)number_format(($total_dropped / $total_students) * 100, 2, '.', '') : 0.00;
        $overall_p_pct    = $total_students > 0 ? (float)number_format(($total_pending / $total_students) * 100, 2, '.', '') : 0.00;

        $routes_summary_progress = $total_route_students_all > 0 ? (float)number_format((($total_route_boarded_all + $total_route_dropped_all) / $total_route_students_all) * 100, 2, '.', '') : 0.00;

        // ---------------------------------------------------------
        // 7. Assemble Dashboard Response Data
        // ---------------------------------------------------------
        $response_data = [
            'date' => $date,
            'bus_details' => [
                'summary' => [
                    'total_buses'       => $total_buses,
                    'active_buses'      => $active_buses,
                    'maintenance_buses' => $maintenance_buses
                ],
                'buses' => $all_buses
            ],
            'route_details' => [
                'summary' => [
                    'total_routes'                 => count($routes_list),
                    'total_vehicles'               => $total_route_vehicles_all,
                    'total_stops'                  => $total_route_stops_all,
                    'total_assigned_students'      => $total_route_students_all,
                    'total_boarded'                => $total_route_boarded_all,
                    'total_dropped'                => $total_route_dropped_all,
                    'total_pending'                => $total_route_pending_all,
                    'boarding_progress_percentage' => $routes_summary_progress
                ],
                'routes' => $routes_list
            ],
            'student_stats' => [
                'total_students'               => $total_students,
                'total_boarded'                => $total_boarded,
                'total_dropped'                => $total_dropped,
                'total_pending'                => $total_pending,
                'boarding_progress_percentage' => $overall_progress,
                'boarded_percentage'           => $overall_b_pct,
                'dropped_percentage'           => $overall_d_pct,
                'pending_percentage'           => $overall_p_pct,
                'students'                     => $filtered_students
            ]
        ];

        // Route summary convenience field when filtered by route_id
        if ($route_id && !empty($routes_list)) {
            $matched_route = $routes_list[0];
            $response_data['route_summary'] = [
                'route_id'                     => $matched_route['route_id'],
                'route_title'                  => $matched_route['route_title'],
                'is_active'                    => $matched_route['is_active'],
                'total_vehicles'               => $matched_route['total_vehicles'],
                'total_stops'                  => $matched_route['total_stops'],
                'total_students'               => $matched_route['total_students'],
                'total_boarded'                => $matched_route['total_boarded'],
                'total_dropped'                => $matched_route['total_dropped'],
                'total_pending'                => $matched_route['total_pending'],
                'boarding_progress_percentage' => $matched_route['boarding_progress_percentage']
            ];
        }

        $this->_success('Dashboard details retrieved successfully', $response_data);
    }
}
