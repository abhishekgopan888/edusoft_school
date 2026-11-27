<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Bavo extends Base
{
    public $swagger_meta = [
        'tag' => 'Bavo Parent Tracking',
        'description' => 'Bavo Parent App API for real-time student location, arrival timeline, boarding/drop status, drop-near-location alert, route history, and parent notifications.',
        'methods' => [
            'index' => [
                'path' => '/api/bavo',
                'summary' => 'Get Bavo parent live student tracking, timeline, history, drop location, and notifications',
                'description' => 'Returns complete parent view for Bavo app: where the student is right now, arrival timeline across route stops, boarded/dropped status, drop near location details, student route history, and notifications.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Student ID (e.g. 825)',
                        'schema' => ['type' => 'integer', 'example' => 825]
                    ],
                    [
                        'name' => 'route_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Optional Route ID (auto-resolved from student transport assignment if omitted)',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Tracking date (YYYY-MM-DD). Defaults to today.',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-27']
                    ],
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Max records for route history and notifications (default 30, max 100)',
                        'schema' => ['type' => 'integer', 'example' => 30]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Bavo parent tracking details retrieved successfully'],
                    '400' => ['description' => 'student_id is required'],
                    '404' => ['description' => 'Student not found']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _haversine_meters($lat1, $lon1, $lat2, $lon2)
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }
        $earth_radius = 6371000;
        $dLat = deg2rad((float)$lat2 - (float)$lat1);
        $dLon = deg2rad((float)$lon2 - (float)$lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad((float)$lat1)) * cos(deg2rad((float)$lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earth_radius * $c, 2);
    }

    /**
     * GET /api/bavo
     */
    public function index()
    {
        $this->_check_auth([], false);

        $student_id = $this->input->get('student_id') ?? $this->input->get('studentid');
        if (empty($student_id) || !is_numeric($student_id)) {
            $this->_error('student_id is required and must be numeric', 400);
            return;
        }
        $student_id = (int)$student_id;

        $route_id_param = $this->input->get('route_id') ?? $this->input->get('routeid');
        $date           = $this->input->get('date') ? date('Y-m-d', strtotime($this->input->get('date'))) : date('Y-m-d');
        $limit          = min(100, max(1, (int)($this->input->get('limit') ?: 30)));

        // 1. Fetch Student & Assigned Route / Vehicle / Stop
        $student_row = $this->db->select('
            s.id as student_id,
            s.admission_no,
            s.roll_no,
            TRIM(CONCAT_WS(" ", s.firstname, s.middlename, s.lastname)) as student_name,
            s.mobileno as student_phone,
            s.guardian_name,
            s.guardian_phone,
            s.current_address,
            s.boarding_status as student_boarding_status,
            c.class,
            sec.section,
            ss.id as student_session_id,
            ss.vehroute_id,
            ss.route_pickup_point_id,
            COALESCE(vr.route_id, rpp.transport_route_id) as route_id,
            tr.route_title,
            rpp.pickup_point_id,
            pp.name as pickup_point_name,
            pp.latitude as stop_latitude,
            pp.longitude as stop_longitude,
            rpp.pickup_time,
            rpp.destination_distance,
            rpp.order_number as stop_order,
            v.id as vehicle_id,
            v.vehicle_no as bus_number,
            v.vehicle_model,
            COALESCE(d_vr.driver_name, d_v.driver_name) as driver_name,
            COALESCE(d_vr.contact, d_v.contact) as driver_contact,
            att.attender_name
        ')
        ->from('students s')
        ->join('student_session ss', 'ss.student_id = s.id', 'left')
        ->join('classes c', 'c.id = ss.class_id', 'left')
        ->join('sections sec', 'sec.id = ss.section_id', 'left')
        ->join('route_pickup_point rpp', 'rpp.id = ss.route_pickup_point_id', 'left')
        ->join('pickup_point pp', 'pp.id = rpp.pickup_point_id', 'left')
        ->join('vehicle_routes vr', 'vr.id = ss.vehroute_id', 'left')
        ->join('transport_route tr', 'tr.id = COALESCE(vr.route_id, rpp.transport_route_id)', 'left')
        ->join('vehicles v', 'v.id = vr.vehicle_id', 'left')
        ->join('drivers d_vr', 'd_vr.id = vr.driver_id', 'left')
        ->join('drivers d_v', 'd_v.id = v.driver_id', 'left')
        ->join('attenders att', 'att.id = vr.attender_id', 'left')
        ->where('s.id', $student_id)
        ->order_by('ss.id', 'DESC')
        ->limit(1)
        ->get()->row_array();

        if (empty($student_row)) {
            $this->_error('Student not found', 404);
            return;
        }

        $route_id   = !empty($route_id_param) ? (int)$route_id_param : (!empty($student_row['route_id']) ? (int)$student_row['route_id'] : null);
        $vehicle_id = !empty($student_row['vehicle_id']) ? (int)$student_row['vehicle_id'] : null;

        // 2. Active / Latest Trip for this Route or Vehicle
        $active_trip = null;
        if ($this->db->table_exists('trips') && ($route_id || $vehicle_id)) {
            $this->db->select('t.*, tr.route_title')
                ->from('trips t')
                ->join('transport_route tr', 'tr.id = t.route_id', 'left');
            if ($route_id) {
                $this->db->where('t.route_id', $route_id);
            } elseif ($vehicle_id) {
                $this->db->where('t.bus_id', $vehicle_id);
            }
            $this->db->where('DATE(t.start_time)', $date);
            $this->db->order_by('t.id', 'DESC')->limit(1);
            $active_trip = $this->db->get()->row_array();
        }

        // 3. Today's Boarding / Drop Status from boarding_point
        $bp_today = [];
        if ($this->db->table_exists('boarding_point')) {
            $bp_today = $this->db->select('*')
                ->from('boarding_point')
                ->where('studentid', $student_id)
                ->where('DATE(timestamp)', $date)
                ->order_by('id', 'DESC')
                ->get()->result_array();
        }

        $boarded_time = null;
        $dropped_time = null;
        $pending_time = null;
        $current_status = !empty($student_row['student_boarding_status']) ? strtolower(trim($student_row['student_boarding_status'])) : 'pending';

        if (!empty($bp_today)) {
            $current_status = strtolower(trim($bp_today[0]['status']));
            foreach ($bp_today as $rec) {
                $st_l = strtolower(trim($rec['status']));
                if ($st_l === 'boarded' && empty($boarded_time)) {
                    $boarded_time = $rec['timestamp'];
                } elseif (in_array($st_l, ['dropped', 'deboarded']) && empty($dropped_time)) {
                    $dropped_time = $rec['timestamp'];
                } elseif ($st_l === 'pending' && empty($pending_time)) {
                    $pending_time = $rec['timestamp'];
                }
            }
        }

        $is_boarded = ($current_status === 'boarded');
        $is_dropped = in_array($current_status, ['dropped', 'deboarded']);
        $is_pending = (!$is_boarded && !$is_dropped);

        $boarding_status_block = [
            'status'       => ucfirst($current_status),
            'is_boarded'   => $is_boarded,
            'is_dropped'   => $is_dropped,
            'is_pending'   => $is_pending,
            'boarded_time' => $boarded_time,
            'dropped_time' => $dropped_time,
            'pending_time' => $pending_time
        ];

        // 4. Current Bus GPS Location ("Where is the student at this time")
        $bus_gps = null;
        if ($this->db->table_exists('bus_locations') && $vehicle_id) {
            $bus_gps = $this->db->select('*')
                ->from('bus_locations')
                ->where('bus_id', $vehicle_id)
                ->order_by('recorded_at', 'DESC')
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()->row_array();
        }

        $cur_lat = !empty($bus_gps['latitude']) ? (float)$bus_gps['latitude'] : (!empty($student_row['stop_latitude']) ? (float)$student_row['stop_latitude'] : null);
        $cur_lng = !empty($bus_gps['longitude']) ? (float)$bus_gps['longitude'] : (!empty($student_row['stop_longitude']) ? (float)$student_row['stop_longitude'] : null);

        if ($is_dropped) {
            $location_state = 'dropped_at_stop';
            $location_label = 'Student dropped at ' . (!empty($student_row['pickup_point_name']) ? $student_row['pickup_point_name'] : 'assigned stop');
        } elseif ($is_boarded) {
            $location_state = 'in_bus';
            $location_label = 'Student is currently on board bus ' . ($student_row['bus_number'] ?: '');
        } else {
            $location_state = 'waiting_for_bus';
            $location_label = 'Student is waiting at ' . (!empty($student_row['pickup_point_name']) ? $student_row['pickup_point_name'] : 'pickup point');
        }

        $current_location = [
            'student_location_state' => $location_state,
            'location_description'   => trim($location_label),
            'location_source'        => !empty($bus_gps) ? 'live_bus_gps' : 'assigned_stop',
            'latitude'               => $cur_lat,
            'longitude'              => $cur_lng,
            'speed_kmh'              => !empty($bus_gps['speed']) ? (float)$bus_gps['speed'] : 0,
            'heading'                => !empty($bus_gps['heading']) ? (float)$bus_gps['heading'] : 0,
            'last_updated'           => !empty($bus_gps['recorded_at']) ? $bus_gps['recorded_at'] : ($dropped_time ?? $boarded_time ?? date('Y-m-d H:i:s'))
        ];

        // 5. Arrival Timeline Across Route Stops
        $arrival_timeline = [];
        $nearest_stop_name = $student_row['pickup_point_name'] ?? '';
        $min_stop_dist = null;

        if ($route_id && $this->db->table_exists('route_pickup_point')) {
            $stops_raw = $this->db->select('
                rpp.id as route_pickup_point_id,
                rpp.pickup_point_id,
                rpp.pickup_time,
                rpp.destination_distance,
                rpp.order_number,
                rpp.point_type,
                pp.name as pickup_point_name,
                pp.latitude,
                pp.longitude
            ')
            ->from('route_pickup_point rpp')
            ->join('pickup_point pp', 'pp.id = rpp.pickup_point_id', 'left')
            ->where('rpp.transport_route_id', $route_id)
            ->order_by('rpp.order_number', 'ASC')
            ->order_by('rpp.id', 'ASC')
            ->get()->result_array();

            $student_stop_order = !empty($student_row['stop_order']) ? (int)$student_row['stop_order'] : 1;

            foreach ($stops_raw as $idx => $sp) {
                $s_lat = !empty($sp['latitude']) ? (float)$sp['latitude'] : null;
                $s_lng = !empty($sp['longitude']) ? (float)$sp['longitude'] : null;
                $dist_m = $this->_haversine_meters($cur_lat, $cur_lng, $s_lat, $s_lng);

                if ($dist_m !== null && ($min_stop_dist === null || $dist_m < $min_stop_dist)) {
                    $min_stop_dist = $dist_m;
                    $nearest_stop_name = $sp['pickup_point_name'];
                }

                $speed_kmh = max(20.0, (float)($current_location['speed_kmh'] ?: 30.0));
                $eta_minutes = $dist_m !== null ? (int)ceil(($dist_m / 1000.0) / $speed_kmh * 60) : (($idx + 1) * 5);

                $is_student_stop = ((int)$sp['route_pickup_point_id'] === (int)$student_row['route_pickup_point_id']);
                $ord = !empty($sp['order_number']) ? (int)$sp['order_number'] : ($idx + 1);

                $stop_status = 'upcoming';
                if ($is_dropped || ($active_trip && strtolower($active_trip['status']) === 'completed')) {
                    $stop_status = 'completed';
                } elseif ($dist_m !== null && $dist_m <= 250) {
                    $stop_status = 'current';
                } elseif ($is_boarded && $ord < $student_stop_order) {
                    $stop_status = 'completed';
                }

                $scheduled_ts = !empty($sp['pickup_time']) ? strtotime($date . ' ' . $sp['pickup_time']) : time();
                $arrival_timeline[] = [
                    'stop_order'             => $ord,
                    'route_pickup_point_id'  => (int)$sp['route_pickup_point_id'],
                    'pickup_point_id'        => !empty($sp['pickup_point_id']) ? (int)$sp['pickup_point_id'] : null,
                    'pickup_point_name'      => (string)$sp['pickup_point_name'],
                    'point_type'             => !empty($sp['point_type']) ? $sp['point_type'] : 'stop',
                    'scheduled_time'         => (string)$sp['pickup_time'],
                    'estimated_arrival_time' => date('H:i:s', $scheduled_ts + ($eta_minutes * 60)),
                    'eta_minutes'            => $eta_minutes,
                    'distance_km'            => !empty($sp['destination_distance']) ? (float)$sp['destination_distance'] : null,
                    'latitude'               => $s_lat,
                    'longitude'              => $s_lng,
                    'is_student_stop'        => $is_student_stop,
                    'stop_status'            => $stop_status
                ];
            }
        }
        $current_location['nearest_stop_name'] = $nearest_stop_name;

        // 6. Dropped Near Location Details
        $drop_lat = !empty($student_row['stop_latitude']) ? (float)$student_row['stop_latitude'] : null;
        $drop_lng = !empty($student_row['stop_longitude']) ? (float)$student_row['stop_longitude'] : null;
        $near_distance_meters = $this->_haversine_meters($cur_lat, $cur_lng, $drop_lat, $drop_lng);
        $is_near_drop = ($is_dropped || ($near_distance_meters !== null && $near_distance_meters <= 500));

        $drop_location = [
            'pickup_point_id'       => !empty($student_row['pickup_point_id']) ? (int)$student_row['pickup_point_id'] : null,
            'location_name'         => !empty($student_row['pickup_point_name']) ? $student_row['pickup_point_name'] : 'Assigned Stop',
            'latitude'              => $drop_lat,
            'longitude'             => $drop_lng,
            'scheduled_time'        => (string)$student_row['pickup_time'],
            'actual_dropped_time'   => $dropped_time,
            'near_distance_meters'  => $near_distance_meters,
            'is_near_drop_location' => $is_near_drop,
            'near_location_message' => $is_dropped
                ? ('Dropped safely at ' . ($student_row['pickup_point_name'] ?: 'assigned stop'))
                : ($is_near_drop
                    ? ('Bus is near drop location (' . round($near_distance_meters) . 'm away)')
                    : 'Bus is en route')
        ];

        // 7. Student Route History (day-wise from boarding_point)
        $route_history = [];
        if ($this->db->table_exists('boarding_point')) {
            $hist_rows = $this->db->select('
                bp.id as boarding_id,
                bp.status,
                bp.timestamp,
                DATE(bp.timestamp) as record_date,
                v.vehicle_no as bus_number,
                att.attender_name
            ')
            ->from('boarding_point bp')
            ->join('vehicles v', 'v.id = bp.busid', 'left')
            ->join('attenders att', 'att.id = bp.attenderid', 'left')
            ->where('bp.studentid', $student_id)
            ->order_by('bp.timestamp', 'DESC')
            ->limit($limit)
            ->get()->result_array();

            $grouped_hist = [];
            foreach ($hist_rows as $hr) {
                $d = $hr['record_date'];
                if (!isset($grouped_hist[$d])) {
                    $grouped_hist[$d] = [
                        'date'              => $d,
                        'day'               => date('l', strtotime($d)),
                        'route_id'          => $route_id,
                        'route_title'       => (string)$student_row['route_title'],
                        'pickup_point_name' => (string)$student_row['pickup_point_name'],
                        'bus_number'        => !empty($hr['bus_number']) ? $hr['bus_number'] : (string)$student_row['bus_number'],
                        'driver_name'       => (string)$student_row['driver_name'],
                        'attender_name'     => !empty($hr['attender_name']) ? $hr['attender_name'] : (string)$student_row['attender_name'],
                        'status'            => ucfirst($hr['status']),
                        'boarded_time'      => null,
                        'dropped_time'      => null
                    ];
                }
                $st_l = strtolower($hr['status']);
                if ($st_l === 'boarded' && empty($grouped_hist[$d]['boarded_time'])) {
                    $grouped_hist[$d]['boarded_time'] = $hr['timestamp'];
                } elseif (in_array($st_l, ['dropped', 'deboarded']) && empty($grouped_hist[$d]['dropped_time'])) {
                    $grouped_hist[$d]['dropped_time'] = $hr['timestamp'];
                }
            }
            $route_history = array_values($grouped_hist);
        }

        // 8. Parent Notifications (Transit Alerts + Parent Notices)
        $notifications = [];

        if ($is_dropped) {
            $notifications[] = [
                'id'        => 'transit_drop_' . $student_id . '_' . $date,
                'type'      => 'dropped',
                'title'     => 'Student Dropped',
                'message'   => $student_row['student_name'] . ' was dropped near ' . ($student_row['pickup_point_name'] ?: 'assigned stop') . ($dropped_time ? ' at ' . date('h:i A', strtotime($dropped_time)) : '') . '.',
                'timestamp' => $dropped_time ?? date('Y-m-d H:i:s'),
                'is_read'   => false
            ];
        } elseif ($is_near_drop && $is_boarded) {
            $notifications[] = [
                'id'        => 'transit_near_' . $student_id . '_' . $date,
                'type'      => 'approaching_drop',
                'title'     => 'Bus Near Drop Location',
                'message'   => 'Bus ' . ($student_row['bus_number'] ?: '') . ' is approaching ' . ($student_row['pickup_point_name'] ?: 'your stop') . '.',
                'timestamp' => $current_location['last_updated'],
                'is_read'   => false
            ];
        }

        if ($is_boarded || !empty($boarded_time)) {
            $notifications[] = [
                'id'        => 'transit_board_' . $student_id . '_' . $date,
                'type'      => 'boarded',
                'title'     => 'Student Boarded Bus',
                'message'   => $student_row['student_name'] . ' boarded bus ' . ($student_row['bus_number'] ?: '') . ($boarded_time ? ' at ' . date('h:i A', strtotime($boarded_time)) : '') . '.',
                'timestamp' => $boarded_time ?? date('Y-m-d H:i:s'),
                'is_read'   => false
            ];
        }

        if ($this->db->table_exists('send_notification')) {
            $school_notifs = $this->db->select('sn.id, sn.title, sn.message, sn.publish_date, sn.date, sn.created_at, rn.id as read_id')
                ->from('send_notification sn')
                ->join('read_notification rn', 'rn.notification_id = sn.id AND rn.student_id = ' . $student_id, 'left')
                ->where('sn.visible_parent', 'yes')
                ->order_by('sn.id', 'DESC')
                ->limit($limit)
                ->get()->result_array();

            foreach ($school_notifs as $sn) {
                $notifications[] = [
                    'id'        => (int)$sn['id'],
                    'type'      => 'school_notice',
                    'title'     => $sn['title'],
                    'message'   => strip_tags($sn['message']),
                    'timestamp' => !empty($sn['publish_date']) ? $sn['publish_date'] : $sn['created_at'],
                    'is_read'   => !empty($sn['read_id'])
                ];
            }
        }

        $this->_success('Bavo parent tracking details retrieved successfully', [
            'date'             => $date,
            'student'          => [
                'student_id'      => (int)$student_row['student_id'],
                'student_name'    => $student_row['student_name'],
                'admission_no'    => (string)$student_row['admission_no'],
                'class'           => (string)$student_row['class'],
                'section'         => (string)$student_row['section'],
                'guardian_name'   => (string)$student_row['guardian_name'],
                'guardian_phone'  => (string)$student_row['guardian_phone']
            ],
            'route'            => [
                'route_id'      => $route_id,
                'route_title'   => (string)$student_row['route_title'],
                'vehicle_id'    => $vehicle_id,
                'bus_number'    => (string)$student_row['bus_number'],
                'vehicle_model' => (string)$student_row['vehicle_model'],
                'driver_name'   => (string)$student_row['driver_name'],
                'driver_contact'=> (string)$student_row['driver_contact'],
                'attender_name' => (string)$student_row['attender_name'],
                'trip_id'       => !empty($active_trip['id']) ? (int)$active_trip['id'] : null,
                'trip_type'     => !empty($active_trip['trip_type']) ? $active_trip['trip_type'] : 'pickup',
                'trip_status'   => !empty($active_trip['status']) ? $active_trip['status'] : null
            ],
            'boarding_status'  => $boarding_status_block,
            'current_location' => $current_location,
            'arrival_timeline' => $arrival_timeline,
            'drop_location'    => $drop_location,
            'route_history'    => $route_history,
            'notifications'    => $notifications
        ]);
    }
}
