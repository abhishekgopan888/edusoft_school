<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Attendance extends Base
{
    public $swagger_meta = [
        'tag' => 'Student Attendance',
        'description' => 'Endpoints for retrieving student attendance records and attendance summaries.',
        'methods' => [
            'index' => [
                'path' => '/api/attendance',
                'summary' => 'Get student attendance list',
                'description' => 'Retrieves student attendance records with attendance type (Present, Absent, Late, Half Day, Holiday), in/out times, and remarks. Supports filtering by student_id, class_id, section_id, date, date_from, date_to, month, and attendence_type_id.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Student ID',
                        'schema' => ['type' => 'integer', 'example' => 825]
                    ],
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
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by exact date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-27']
                    ],
                    [
                        'name' => 'date_from',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter from start date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date']
                    ],
                    [
                        'name' => 'date_to',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter up to end date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date']
                    ],
                    [
                        'name' => 'month',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by month (YYYY-MM)',
                        'schema' => ['type' => 'string', 'example' => '2026-11']
                    ],
                    [
                        'name' => 'attendence_type_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Attendance Type ID (1=Present, 3=Late, 4=Absent, 5=Holiday, 6=Half Day)',
                        'schema' => ['type' => 'integer']
                    ],
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Maximum number of records to return (default 50, max 200)',
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
                    '200' => ['description' => 'Student attendance retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'details' => [
                'path' => '/api/attendance/details',
                'summary' => 'Get student attendance summary and records by student_id',
                'description' => 'Retrieves complete attendance summary (present, absent, late, half day, holiday, percentage) and day-wise records for a specific student.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Student ID',
                        'schema' => ['type' => 'integer', 'example' => 825]
                    ],
                    [
                        'name' => 'month',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Optional month filter (YYYY-MM)',
                        'schema' => ['type' => 'string', 'example' => '2026-11']
                    ],
                    [
                        'name' => 'date_from',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Optional start date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date']
                    ],
                    [
                        'name' => 'date_to',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Optional end date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Student attendance details retrieved successfully'],
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

    private function _fetch_attendance($filters = [])
    {
        if (!$this->db->table_exists('student_attendences')) {
            return [];
        }

        $this->db->select('
            sa.id,
            sa.student_session_id,
            sa.date,
            sa.attendence_type_id,
            sa.remark,
            sa.in_time,
            sa.out_time,
            sa.biometric_attendence,
            sa.qrcode_attendance,
            at.type as attendance_type,
            at.key_value,
            s.id as student_id,
            s.admission_no,
            s.roll_no,
            TRIM(CONCAT_WS(" ", s.firstname, s.middlename, s.lastname)) as student_name,
            ss.class_id,
            c.class,
            ss.section_id,
            sec.section
        ')
        ->from('student_attendences sa')
        ->join('attendence_type at', 'at.id = sa.attendence_type_id', 'left')
        ->join('student_session ss', 'ss.id = sa.student_session_id')
        ->join('students s', 's.id = ss.student_id')
        ->join('classes c', 'c.id = ss.class_id', 'left')
        ->join('sections sec', 'sec.id = ss.section_id', 'left')
        ->where('s.is_active', 'yes');

        if (!empty($filters['student_id'])) {
            $this->db->where('s.id', (int)$filters['student_id']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('ss.class_id', (int)$filters['class_id']);
        }
        if (!empty($filters['section_id'])) {
            $this->db->where('ss.section_id', (int)$filters['section_id']);
        }
        if (!empty($filters['date'])) {
            $this->db->where('sa.date', date('Y-m-d', strtotime($filters['date'])));
        }
        if (!empty($filters['date_from'])) {
            $this->db->where('sa.date >=', date('Y-m-d', strtotime($filters['date_from'])));
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('sa.date <=', date('Y-m-d', strtotime($filters['date_to'])));
        }
        if (!empty($filters['month'])) {
            $this->db->like('sa.date', $filters['month'], 'after');
        }
        if (!empty($filters['attendence_type_id'])) {
            $this->db->where('sa.attendence_type_id', (int)$filters['attendence_type_id']);
        }

        $this->db->order_by('sa.date', 'DESC');
        $this->db->order_by('sa.id', 'DESC');

        if (isset($filters['limit'])) {
            $this->db->limit((int)$filters['limit'], (int)($filters['offset'] ?? 0));
        }

        return $this->db->get()->result_array();
    }

    private function _compute_summary($rows)
    {
        $summary = [
            'total_records' => count($rows),
            'present'       => 0,
            'late'          => 0,
            'absent'        => 0,
            'holiday'       => 0,
            'half_day'      => 0
        ];

        foreach ($rows as $r) {
            $type_id = (int)$r['attendence_type_id'];
            $key     = strtoupper(trim((string)($r['key_value'] ?? '')));
            if ($type_id === 1 || $key === 'P') {
                $summary['present']++;
            } elseif ($type_id === 3 || $key === 'L') {
                $summary['late']++;
            } elseif ($type_id === 4 || $key === 'A') {
                $summary['absent']++;
            } elseif ($type_id === 5 || $key === 'F') {
                $summary['holiday']++;
            } elseif ($type_id === 6 || $key === 'H') {
                $summary['half_day']++;
            }
        }

        $working_days = $summary['total_records'] - $summary['holiday'];
        $attended     = $summary['present'] + $summary['late'] + ($summary['half_day'] * 0.5);
        $summary['attendance_percentage'] = $working_days > 0 ? round(($attended / $working_days) * 100, 2) : 0.0;

        return $summary;
    }

    /**
     * GET /api/attendance
     */
    public function index()
    {
        $this->_check_auth([], false);

        $limit  = min(200, max(1, (int)($this->input->get('limit') ?: 50)));
        $offset = max(0, (int)($this->input->get('offset') ?: 0));

        $filters = [
            'student_id'         => $this->input->get('student_id') ?? $this->input->get('studentid'),
            'class_id'           => $this->input->get('class_id'),
            'section_id'         => $this->input->get('section_id'),
            'date'               => $this->input->get('date'),
            'date_from'          => $this->input->get('date_from'),
            'date_to'            => $this->input->get('date_to'),
            'month'              => $this->input->get('month'),
            'attendence_type_id' => $this->input->get('attendence_type_id'),
            'limit'              => $limit,
            'offset'             => $offset
        ];

        $rows    = $this->_fetch_attendance($filters);
        $summary = $this->_compute_summary($rows);

        $this->_success('Student attendance retrieved successfully', [
            'limit'      => $limit,
            'offset'     => $offset,
            'summary'    => $summary,
            'attendance' => $rows
        ]);
    }

    /**
     * GET /api/attendance/details
     */
    public function details()
    {
        $this->_check_auth([], false);

        $student_id = $this->input->get('student_id') ?? $this->input->get('studentid');
        if (empty($student_id) || !is_numeric($student_id)) {
            $this->_error('student_id is required and must be numeric', 400);
            return;
        }

        $student = $this->db->select('s.id as student_id, s.admission_no, s.roll_no, TRIM(CONCAT_WS(" ", s.firstname, s.middlename, s.lastname)) as student_name, c.class, sec.section')
            ->from('students s')
            ->join('student_session ss', 'ss.student_id = s.id', 'left')
            ->join('classes c', 'c.id = ss.class_id', 'left')
            ->join('sections sec', 'sec.id = ss.section_id', 'left')
            ->where('s.id', (int)$student_id)
            ->limit(1)
            ->get()->row_array();

        if (empty($student)) {
            $this->_error('Student not found', 404);
            return;
        }

        $filters = [
            'student_id' => (int)$student_id,
            'month'      => $this->input->get('month'),
            'date_from'  => $this->input->get('date_from'),
            'date_to'    => $this->input->get('date_to')
        ];

        $rows    = $this->_fetch_attendance($filters);
        $summary = $this->_compute_summary($rows);

        $this->_success('Student attendance details retrieved successfully', [
            'student'    => $student,
            'summary'    => $summary,
            'attendance' => $rows
        ]);
    }
}
