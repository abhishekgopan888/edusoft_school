<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Studentdetails extends Base
{
    public $swagger_meta = [
        'tag' => 'Student Details Management',
        'description' => 'Endpoints for searching, viewing, registering student details, and managing student boarding & drop status (Pending, Boarded, Dropped). Requires X-API-Key: student-details-api-secret-key-2026',
        'methods' => [
            'index' => [
                'path' => '/api/studentdetails',
                'summary' => 'Get students list with attendance status and summary counts',
                'description' => 'Retrieves a list of students with grade, address, boarding status (Pending, Boarded, Dropped), and summary counts.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'class_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Class ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'section_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Section ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Max number of records',
                        'schema' => ['type' => 'integer', 'example' => 50]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'List of students and status summary retrieved successfully'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing API key']
                ]
            ],
            'details' => [
                'path' => '/api/studentdetails/details',
                'summary' => 'Get single student details',
                'description' => 'Retrieves full details of a specific student by student ID or parent phone number (parent login check without token).',
                'http_method' => 'get',
                'security' => [],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Student ID, Admission No, or Roll No',
                        'schema' => ['type' => 'integer', 'example' => 803]
                    ],
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Alias for student ID',
                        'schema' => ['type' => 'integer', 'example' => 803]
                    ],
                    [
                        'name' => 'parent_phone',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Parent phone number for parent login check without token',
                        'schema' => ['type' => 'string', 'example' => '9876543210']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Student details retrieved successfully'],
                    '404' => ['description' => 'Student not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'latest_boarding_point' => [
                'path' => '/api/studentdetails/latest_boarding_point',
                'summary' => 'Get latest boarding point and transport info',
                'description' => 'Retrieves the latest boarding point record for a student, along with assigned route, pickup point, bus information, driver, and attender details by student ID and parent phone number.',
                'http_method' => 'get',
                'security' => [],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Student ID, Admission No, or Roll No',
                        'schema' => ['type' => 'integer', 'example' => 803]
                    ],
                    [
                        'name' => 'phone_number',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Parent phone number (guardian, father, mother, or student mobile)',
                        'schema' => ['type' => 'string', 'example' => '9876543210']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Latest student boarding point and transport info retrieved successfully'],
                    '400' => ['description' => 'Missing student_id or phone_number'],
                    '404' => ['description' => 'Student not found']
                ]
            ],
            'status' => [
                'path' => '/api/studentdetails/status',
                'summary' => 'Update student boarding status (Mark Boarded / Mark Dropped)',
                'description' => 'Updates student boarding status. Accepts student_id (or admission_no) and action ("Mark Boarded", "Mark Dropped", "Mark Pending") or status ("Boarded", "Dropped", "Pending").',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['student_id'],
                                'properties' => [
                                    'student_id' => ['type' => 'integer', 'example' => 803, 'description' => 'Student ID or Admission No'],
                                    'action' => ['type' => 'string', 'example' => 'Mark Boarded', 'description' => 'Action: "Mark Boarded", "Mark Dropped", or "Mark Pending"'],
                                    'boarding_status' => ['type' => 'string', 'example' => 'Boarded', 'description' => 'Status: "Boarded", "Dropped", or "Pending"']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Student boarding status updated successfully'],
                    '400' => ['description' => 'Invalid parameters'],
                    '404' => ['description' => 'Student not found'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'search' => [
                'path' => '/api/studentdetails/search',
                'summary' => 'Search students',
                'description' => 'Searches students by name, admission number, or roll number.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['search_text'],
                                'properties' => [
                                    'search_text' => ['type' => 'string', 'example' => 'Sophia']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Search results retrieved successfully'],
                    '400' => ['description' => 'Search text missing'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'register' => [
                'path' => '/api/studentdetails/register',
                'summary' => 'Register new student',
                'description' => 'Registers a new student record into the system.',
                'http_method' => 'post',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'required' => ['name', 'student_class', 'division'],
                                'properties' => [
                                    'name' => ['type' => 'string', 'example' => 'Sophia Carter'],
                                    'student_class' => ['type' => 'string', 'example' => '10'],
                                    'division' => ['type' => 'string', 'example' => 'A'],
                                    'address' => ['type' => 'string', 'example' => '123 Tech City'],
                                    'dob' => ['type' => 'string', 'format' => 'date', 'example' => '2008-04-12'],
                                    'phone_number' => ['type' => 'string', 'example' => '9876543210', 'description' => 'Student phone / mobile number'],
                                    'mobileno' => ['type' => 'string', 'example' => '9876543210', 'description' => 'Alias for student phone number'],
                                    'guardian_name' => ['type' => 'string', 'example' => 'Michael Carter'],
                                    'guardian_phone' => ['type' => 'string', 'example' => '9876543210']
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Student registered successfully'],
                    '400' => ['description' => 'Required fields missing'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ]
        ]
    ];

    public function index()
    {
        $this->_check_auth(['student-details-api-secret-key-2026', 'miki-external-secret-key-2024']);
        $class_id   = $this->input->get('class_id');
        $section_id = $this->input->get('section_id');
        $limit      = $this->input->get('limit') ? (int)$this->input->get('limit') : 100;

        // Calculate summary counts across active students
        $summary = [
            'total'   => (int)$this->db->where('is_active', 'yes')->count_all_results('students'),
            'boarded' => (int)$this->db->where('is_active', 'yes')->where('boarding_status', 'Boarded')->count_all_results('students'),
            'dropped' => (int)$this->db->where('is_active', 'yes')->where('boarding_status', 'Dropped')->count_all_results('students'),
            'pending' => (int)$this->db->where('is_active', 'yes')->group_start()->where('boarding_status', 'Pending')->or_where('boarding_status IS NULL')->or_where("boarding_status = ''")->group_end()->count_all_results('students')
        ];

        $this->db->select('students.id, students.admission_no, students.roll_no, students.firstname, students.lastname, students.gender, students.dob, students.mobileno, students.email, students.current_address, students.guardian_name, students.guardian_phone, COALESCE(NULLIF(students.boarding_status, ""), "Pending") as boarding_status, classes.class, sections.section')
                 ->from('students')
                 ->join('student_session', 'student_session.student_id = students.id', 'left')
                 ->join('classes', 'classes.id = student_session.class_id', 'left')
                 ->join('sections', 'sections.id = student_session.section_id', 'left')
                 ->where('students.is_active', 'yes');

        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', $section_id);
        }

        $students = $this->db->limit($limit)->get()->result_array();

        // Format grade, full_name, and address for each student and remove any status field
        foreach ($students as &$s) {
            $s['full_name'] = trim($s['firstname'] . ' ' . $s['lastname']);
            $s['phone_number'] = !empty($s['mobileno']) ? $s['mobileno'] : '';
            $s['address'] = $s['current_address'];
            $s['grade'] = !empty($s['class']) ? ('Grade ' . $s['class'] . ($s['section'] ? ' ' . $s['section'] : '')) : '';
            unset($s['status']);
        }

        $payload = [
            'message' => 'Students list retrieved successfully',
            'data' => [
                'summary' => $summary,
                'students' => $students
            ]
        ];

        $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function details()
    {
        $parent_phone = $this->input->get('parent_phone') ?: $this->input->get('phone');
        $id = $this->input->get('id') ?: $this->input->get('student_id');

        if (empty($parent_phone)) {
            $this->_check_auth(['student-details-api-secret-key-2026', 'miki-external-secret-key-2024']);
            if (empty($id)) {
                $this->_error('Student ID is required', 400);
                return;
            }
        }

        $query = $this->db->select('students.*, COALESCE(NULLIF(students.boarding_status, ""), "Pending") as boarding_status, classes.class, sections.section')
                          ->from('students')
                          ->join('student_session', 'student_session.student_id = students.id', 'left')
                          ->join('classes', 'classes.id = student_session.class_id', 'left')
                          ->join('sections', 'sections.id = student_session.section_id', 'left');

        if (!empty($id)) {
            $query->group_start()
                  ->where('students.id', $id)
                  ->or_where('students.admission_no', $id)
                  ->or_where('students.roll_no', $id)
                  ->group_end();
        }

        if (!empty($parent_phone)) {
            $clean_phone = preg_replace('/\D/', '', $parent_phone);
            $query->group_start()
                  ->where('students.guardian_phone', $parent_phone)
                  ->or_where('students.father_phone', $parent_phone)
                  ->or_where('students.mother_phone', $parent_phone)
                  ->or_where('students.mobileno', $parent_phone);
            if (!empty($clean_phone) && strlen($clean_phone) >= 10) {
                $last10 = substr($clean_phone, -10);
                $query->or_like('students.guardian_phone', $last10)
                      ->or_like('students.father_phone', $last10)
                      ->or_like('students.mother_phone', $last10)
                      ->or_like('students.mobileno', $last10);
            }
            $query->group_end();
        }

        $student = $query->get()->row_array();
        if (!$student) {
            $this->_error('Student not found', 404);
            return;
        }

        $student['full_name'] = trim($student['firstname'] . ' ' . $student['lastname']);
        $student['phone_number'] = !empty($student['mobileno']) ? $student['mobileno'] : '';
        $student['address'] = $student['current_address'];
        $student['grade'] = !empty($student['class']) ? ('Grade ' . $student['class'] . ($student['section'] ? ' ' . $student['section'] : '')) : '';

        $this->_success('Student details retrieved successfully', $student);
    }

    public function latest_boarding_point()
    {
        $student_id = $this->input->get('student_id') ?: $this->input->get('id');
        $phone_number = $this->input->get('phone_number') ?: ($this->input->get('phone') ?: $this->input->get('parent_phone'));

        if (empty($student_id) && empty($phone_number)) {
            $this->_error('student_id or phone_number is required', 400);
            return;
        }

        $student = null;
        if (!empty($student_id)) {
            $this->db->select('students.*, classes.class, sections.section, student_session.vehroute_id, student_session.route_pickup_point_id')
                     ->from('students')
                     ->join('student_session', 'student_session.student_id = students.id', 'left')
                     ->join('classes', 'classes.id = student_session.class_id', 'left')
                     ->join('sections', 'sections.id = student_session.section_id', 'left')
                     ->group_start()
                     ->where('students.id', $student_id)
                     ->or_where('students.admission_no', $student_id)
                     ->or_where('students.roll_no', $student_id)
                     ->group_end();

            if (!empty($phone_number)) {
                $clean_phone = preg_replace('/\D/', '', $phone_number);
                $this->db->group_start()
                         ->where('students.guardian_phone', $phone_number)
                         ->or_where('students.father_phone', $phone_number)
                         ->or_where('students.mother_phone', $phone_number)
                         ->or_where('students.mobileno', $phone_number);
                if (!empty($clean_phone) && strlen($clean_phone) >= 10) {
                    $last10 = substr($clean_phone, -10);
                    $this->db->or_like('students.guardian_phone', $last10)
                             ->or_like('students.father_phone', $last10)
                             ->or_like('students.mother_phone', $last10)
                             ->or_like('students.mobileno', $last10);
                }
                $this->db->group_end();
            }
            $student = $this->db->get()->row_array();
        } else {
            $clean_phone = preg_replace('/\D/', '', $phone_number);
            $this->db->select('students.*, classes.class, sections.section, student_session.vehroute_id, student_session.route_pickup_point_id')
                     ->from('students')
                     ->join('student_session', 'student_session.student_id = students.id', 'left')
                     ->join('classes', 'classes.id = student_session.class_id', 'left')
                     ->join('sections', 'sections.id = student_session.section_id', 'left')
                     ->group_start()
                     ->where('students.guardian_phone', $phone_number)
                     ->or_where('students.father_phone', $phone_number)
                     ->or_where('students.mother_phone', $phone_number)
                     ->or_where('students.mobileno', $phone_number);
            if (!empty($clean_phone) && strlen($clean_phone) >= 10) {
                $last10 = substr($clean_phone, -10);
                $this->db->or_like('students.guardian_phone', $last10)
                         ->or_like('students.father_phone', $last10)
                         ->or_like('students.mother_phone', $last10)
                         ->or_like('students.mobileno', $last10);
            }
            $this->db->group_end();
            $student = $this->db->order_by('students.id', 'DESC')->get()->row_array();
        }

        if (!$student) {
            $this->_error('Student not found with the provided details', 404);
            return;
        }

        $sid = $student['id'];

        // 1. Check for latest live boarding record in boarding_point table
        $latest_bp = $this->db->select('boarding_point.*, vehicles.vehicle_no, vehicles.vehicle_model, attenders.attender_name')
                              ->from('boarding_point')
                              ->join('vehicles', 'vehicles.id = boarding_point.busid', 'left')
                              ->join('attenders', 'attenders.id = boarding_point.attenderid', 'left')
                              ->where('boarding_point.studentid', $sid)
                              ->order_by('boarding_point.id', 'DESC')
                              ->limit(1)
                              ->get()->row_array();

        // 2. Fetch assigned route & pickup point details
        $assigned_route = null;
        $assigned_pickup_point = null;
        $bus_id = !empty($latest_bp['busid']) ? $latest_bp['busid'] : 0;
        $attender_id = !empty($latest_bp['attenderid']) ? $latest_bp['attenderid'] : 0;
        $driver_id = 0;

        if (!empty($student['vehroute_id'])) {
            $vr = $this->db->select('vehicle_routes.*, transport_route.route_title')
                           ->from('vehicle_routes')
                           ->join('transport_route', 'transport_route.id = vehicle_routes.route_id', 'left')
                           ->where('vehicle_routes.id', $student['vehroute_id'])
                           ->get()->row_array();
            if ($vr) {
                $assigned_route = [
                    'vehicle_route_id' => (int)$vr['id'],
                    'route_id'         => (int)$vr['route_id'],
                    'route_title'      => $vr['route_title']
                ];
                if (empty($bus_id) && !empty($vr['vehicle_id'])) {
                    $bus_id = $vr['vehicle_id'];
                }
                if (empty($attender_id) && !empty($vr['attender_id'])) {
                    $attender_id = $vr['attender_id'];
                }
                if (empty($driver_id) && !empty($vr['driver_id'])) {
                    $driver_id = $vr['driver_id'];
                }
            }
        }

        if (!empty($student['route_pickup_point_id'])) {
            $rpp = $this->db->select('route_pickup_point.*, pickup_point.name as pickup_point_name')
                            ->from('route_pickup_point')
                            ->join('pickup_point', 'pickup_point.id = route_pickup_point.pickup_point_id', 'left')
                            ->where('route_pickup_point.id', $student['route_pickup_point_id'])
                            ->get()->row_array();
            if ($rpp) {
                $assigned_pickup_point = [
                    'route_pickup_point_id' => (int)$rpp['id'],
                    'pickup_point_id'       => (int)$rpp['pickup_point_id'],
                    'pickup_point_name'     => $rpp['pickup_point_name'],
                    'fees'                  => $rpp['fees'],
                    'destination_distance'  => $rpp['destination_distance'],
                    'pickup_time'           => isset($rpp['pickup_time']) ? $rpp['pickup_time'] : ''
                ];
            }
        }

        // 3. Bus information
        $bus_info = null;
        if (!empty($bus_id)) {
            $bus = $this->db->select('vehicles.*')->from('vehicles')->where('vehicles.id', $bus_id)->get()->row_array();
            if ($bus) {
                if (empty($driver_id) && !empty($bus['driver_id'])) {
                    $driver_id = $bus['driver_id'];
                }
                $bus_info = [
                    'id'                   => (int)$bus['id'],
                    'vehicle_no'           => $bus['vehicle_no'],
                    'vehicle_model'        => $bus['vehicle_model'],
                    'manufacture_year'     => $bus['manufacture_year'],
                    'registration_number'  => $bus['registration_number'],
                    'chasis_number'        => $bus['chasis_number'],
                    'max_seating_capacity' => (int)$bus['max_seating_capacity'],
                    'driver_name'          => isset($bus['driver_name']) ? $bus['driver_name'] : '',
                    'driver_licence'       => isset($bus['driver_licence']) ? $bus['driver_licence'] : '',
                    'driver_contact'       => isset($bus['driver_contact']) ? $bus['driver_contact'] : ''
                ];
            }
        }

        // 4. Driver information
        $driver_info = null;
        if (!empty($driver_id)) {
            if ($driver_id >= 100000) {
                $staff_id = (int)$driver_id - 100000;
                $staff = $this->db->select('id, name, surname, contact_no, email, local_address, permanent_address, image, employee_id')
                                  ->from('staff')
                                  ->where('id', $staff_id)
                                  ->get()->row_array();
                if ($staff) {
                    $driver_info = [
                        'id'           => (int)$driver_id,
                        'driver_name'  => trim($staff['name'] . ' ' . $staff['surname']),
                        'contact'      => $staff['contact_no'],
                        'licence'      => '',
                        'address'      => !empty($staff['local_address']) ? $staff['local_address'] : $staff['permanent_address'],
                        'status'       => 'Active',
                        'driver_image' => $staff['image']
                    ];
                }
            } else {
                $drv = $this->db->get_where('drivers', ['id' => $driver_id])->row_array();
                if ($drv) {
                    $driver_info = [
                        'id'           => (int)$drv['id'],
                        'driver_name'  => $drv['driver_name'],
                        'contact'      => $drv['contact'],
                        'licence'      => $drv['licence'],
                        'age'          => $drv['age'],
                        'address'      => $drv['address'],
                        'status'       => $drv['status'],
                        'driver_image' => $drv['driver_image']
                    ];
                }
            }
        }

        // Fallback driver from bus record if driver_model has not filled it
        if (!$driver_info && $bus_info && (!empty($bus_info['driver_name']) || !empty($bus_info['driver_contact']))) {
            $driver_info = [
                'driver_name' => $bus_info['driver_name'],
                'contact'     => $bus_info['driver_contact'],
                'licence'     => $bus_info['driver_licence']
            ];
        }

        // 5. Attender information
        $attender_info = null;
        if (!empty($attender_id)) {
            $att = $this->db->get_where('attenders', ['id' => $attender_id])->row_array();
            if ($att) {
                $attender_info = [
                    'id'             => (int)$att['id'],
                    'attender_name'  => $att['attender_name'],
                    'contact'        => isset($att['contact']) ? $att['contact'] : (isset($att['phone']) ? $att['phone'] : ''),
                    'age'            => isset($att['age']) ? $att['age'] : '',
                    'address'        => isset($att['address']) ? $att['address'] : '',
                    'status'         => isset($att['status']) ? $att['status'] : 'Active',
                    'attender_image' => isset($att['attender_image']) ? $att['attender_image'] : ''
                ];
            } else {
                // Check if staff attender
                $staff = $this->db->get_where('staff', ['id' => $attender_id])->row_array();
                if ($staff) {
                    $attender_info = [
                        'id'             => (int)$staff['id'],
                        'attender_name'  => trim($staff['name'] . ' ' . $staff['surname']),
                        'contact'        => $staff['contact_no'],
                        'address'        => !empty($staff['local_address']) ? $staff['local_address'] : $staff['permanent_address'],
                        'status'         => 'Active',
                        'attender_image' => $staff['image']
                    ];
                }
            }
        }

        // 6. Assemble latest boarding point record details
        $boarding_point_data = null;
        if ($latest_bp) {
            $boarding_point_data = [
                'id'                 => (int)$latest_bp['id'],
                'studentid'          => (int)$latest_bp['studentid'],
                'busid'              => (int)$latest_bp['busid'],
                'attenderid'         => (int)$latest_bp['attenderid'],
                'status'             => $latest_bp['status'],
                'timestamp'          => $latest_bp['timestamp'],
                'pickup_point_name'  => !empty($assigned_pickup_point['pickup_point_name']) ? $assigned_pickup_point['pickup_point_name'] : ''
            ];
        } elseif ($assigned_pickup_point) {
            $boarding_point_data = [
                'id'                 => 0,
                'studentid'          => (int)$sid,
                'busid'              => (int)$bus_id,
                'attenderid'         => (int)$attender_id,
                'status'             => !empty($student['boarding_status']) ? $student['boarding_status'] : 'Pending',
                'timestamp'          => date('Y-m-d H:i:s'),
                'pickup_point_name'  => $assigned_pickup_point['pickup_point_name']
            ];
        }

        $response_data = [
            'student' => [
                'id'              => (int)$student['id'],
                'admission_no'    => $student['admission_no'],
                'roll_no'         => $student['roll_no'],
                'student_name'    => trim($student['firstname'] . ' ' . $student['lastname']),
                'phone_number'    => !empty($student['mobileno']) ? $student['mobileno'] : '',
                'mobileno'        => !empty($student['mobileno']) ? $student['mobileno'] : '',
                'grade'           => !empty($student['class']) ? ('Grade ' . $student['class'] . ($student['section'] ? ' ' . $student['section'] : '')) : '',
                'class'           => $student['class'],
                'section'         => $student['section'],
                'current_address' => $student['current_address'],
                'guardian_name'   => $student['guardian_name'],
                'guardian_phone'  => $student['guardian_phone'],
                'father_phone'    => $student['father_phone'],
                'mother_phone'    => $student['mother_phone'],
                'boarding_status' => !empty($student['boarding_status']) ? $student['boarding_status'] : 'Pending',
            ],
            'latest_boarding_point' => $boarding_point_data,
            'assigned_pickup_point' => $assigned_pickup_point,
            'assigned_route'        => $assigned_route,
            'bus_information'       => $bus_info,
            'driver'                => $driver_info,
            'attender'              => $attender_info
        ];

        $this->_success('Latest student boarding point and transport information retrieved successfully', $response_data);
    }

    public function status()
    {
        $this->_check_auth(['student-details-api-secret-key-2026', 'miki-external-secret-key-2024']);
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $student_id = isset($input['student_id']) ? trim($input['student_id']) : (isset($input['id']) ? trim($input['id']) : '');
        $action = isset($input['action']) ? trim($input['action']) : '';
        $boarding_status = isset($input['boarding_status']) ? trim($input['boarding_status']) : (isset($input['status']) ? trim($input['status']) : '');

        if (empty($student_id)) {
            $this->_error('student_id parameter is required', 400);
            return;
        }

        // Determine new boarding status from action or status
        $new_status = '';
        if (strcasecmp($action, 'Mark Boarded') === 0 || strcasecmp($action, 'Boarded') === 0 || strcasecmp($boarding_status, 'Boarded') === 0) {
            $new_status = 'Boarded';
        } elseif (strcasecmp($action, 'Mark Dropped') === 0 || strcasecmp($action, 'Dropped') === 0 || strcasecmp($boarding_status, 'Dropped') === 0) {
            $new_status = 'Dropped';
        } elseif (strcasecmp($action, 'Mark Pending') === 0 || strcasecmp($action, 'Pending') === 0 || strcasecmp($boarding_status, 'Pending') === 0) {
            $new_status = 'Pending';
        } else {
            $this->_error('Invalid action or status. Allowed values for action are "Mark Boarded", "Mark Dropped", "Mark Pending" or status "Boarded", "Dropped", "Pending"', 400);
            return;
        }

        $student = $this->db->group_start()
                            ->where('id', $student_id)
                            ->or_where('admission_no', $student_id)
                            ->or_where('roll_no', $student_id)
                            ->group_end()
                            ->get('students')->row_array();

        if (!$student) {
            $this->_error('Student not found. Please provide a valid student_id (e.g. 803, 804, 829 or Admission No)', 404);
            return;
        }

        $this->db->where('id', $student['id'])->update('students', ['boarding_status' => $new_status]);

        $this->_success('Student boarding status updated successfully', [
            'student_id' => (int)$student['id'],
            'admission_no' => $student['admission_no'],
            'student_name' => trim($student['firstname'] . ' ' . $student['lastname']),
            'phone_number' => !empty($student['mobileno']) ? $student['mobileno'] : '',
            'mobileno' => !empty($student['mobileno']) ? $student['mobileno'] : '',
            'boarding_status' => $new_status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function search()
    {
        $this->_check_auth(['student-details-api-secret-key-2026', 'miki-external-secret-key-2024']);
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) {
            $input = $this->input->post();
        }

        $search_text = isset($input['search_text']) ? trim($input['search_text']) : '';
        if (empty($search_text)) {
            $this->_error('search_text parameter is required', 400);
            return;
        }

        $this->db->select('students.id, students.admission_no, students.roll_no, students.firstname, students.lastname, students.mobileno, students.guardian_name, COALESCE(NULLIF(students.boarding_status, ""), "Pending") as boarding_status, classes.class, sections.section')
                 ->from('students')
                 ->join('student_session', 'student_session.student_id = students.id', 'left')
                 ->join('classes', 'classes.id = student_session.class_id', 'left')
                 ->join('sections', 'sections.id = student_session.section_id', 'left')
                 ->where('students.is_active', 'yes')
                 ->group_start()
                 ->like('students.firstname', $search_text)
                 ->or_like('students.lastname', $search_text)
                 ->or_like('students.admission_no', $search_text)
                 ->or_like('students.roll_no', $search_text)
                 ->group_end();

        $results = $this->db->limit(50)->get()->result_array();
        foreach ($results as &$s) {
            $s['full_name'] = trim($s['firstname'] . ' ' . $s['lastname']);
            $s['phone_number'] = !empty($s['mobileno']) ? $s['mobileno'] : '';
            $s['grade'] = !empty($s['class']) ? ('Grade ' . $s['class'] . ($s['section'] ? ' ' . $s['section'] : '')) : '';
        }

        $this->_success('Search results retrieved successfully', $results);
    }

    public function register()
    {
        $this->_check_auth(['student-details-api-secret-key-2026', 'miki-external-secret-key-2024']);
        $params = json_decode(file_get_contents('php://input'), true);
        if (empty($params)) {
            $params = $this->input->post();
        }

        $name          = isset($params['name']) ? $params['name'] : '';
        $student_class = isset($params['student_class']) ? $params['student_class'] : '';
        $division      = isset($params['division']) ? $params['division'] : '';
        $address       = isset($params['address']) ? $params['address'] : '';
        $dob           = isset($params['dob']) ? $params['dob'] : '';
        $phone_number  = isset($params['phone_number']) ? $params['phone_number'] : (isset($params['mobileno']) ? $params['mobileno'] : (isset($params['mobile_number']) ? $params['mobile_number'] : (isset($params['phone']) ? $params['phone'] : '')));
        $guardian_name = isset($params['guardian_name']) ? $params['guardian_name'] : '';
        $guardian_phone= isset($params['guardian_phone']) ? $params['guardian_phone'] : '';
        $boarding_status = isset($params['boarding_status']) ? $params['boarding_status'] : 'Pending';

        if (empty($name) || empty($student_class) || empty($division)) {
            $this->_error('Required fields missing: name, student_class, division', 400);
            return;
        }

        $class_query = $this->db->get_where('classes', ['class' => $student_class])->row();
        if (!$class_query) {
            $this->_error('Class not found', 404);
            return;
        }

        $section_query = $this->db->get_where('sections', ['section' => $division])->row();
        if (!$section_query) {
            $this->_error('Division/Section not found', 404);
            return;
        }

        $parts = explode(' ', $name);
        $lastname = array_pop($parts);
        $firstname = implode(' ', $parts);
        if (empty($firstname)) {
            $firstname = $lastname;
            $lastname = '';
        }

        $student_data = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'dob' => $dob,
            'mobileno' => $phone_number,
            'current_address' => $address,
            'guardian_name' => $guardian_name,
            'guardian_phone' => $guardian_phone,
            'boarding_status' => $boarding_status,
            'is_active' => 'yes'
        ];

        $this->db->insert('students', $student_data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            $this->load->model('setting_model');
            $session = $this->setting_model->getCurrentSession();
            $session_data = [
                'student_id' => $insert_id,
                'class_id' => $class_query->id,
                'section_id' => $section_query->id,
                'session_id' => $session
            ];
            $this->db->insert('student_session', $session_data);

            $this->_success('Student registered successfully', ['student_id' => $insert_id], 201);
        } else {
            $this->_error('Failed to register student', 500);
        }
    }
}

