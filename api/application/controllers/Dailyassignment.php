<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Dailyassignment extends Base
{
    public $swagger_meta = [
        'tag' => 'Daily Assignment',
        'description' => 'Endpoints for retrieving student daily assignments and evaluation details.',
        'methods' => [
            'index' => [
                'path' => '/api/daily_assignment',
                'summary' => 'Get daily assignments list',
                'description' => 'Retrieves daily assignments with student, class, section, subject, attachment URL, and evaluation details. Supports filtering by student_id, class_id, section_id, subject_id, and date.',
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
                        'name' => 'subject_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Subject ID or Subject Group Subject ID',
                        'schema' => ['type' => 'integer', 'example' => 3]
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by assignment date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-27']
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
                    '200' => ['description' => 'Daily assignments retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'details' => [
                'path' => '/api/daily_assignment/details',
                'summary' => 'Get daily assignment details by ID',
                'description' => 'Retrieves full details for a single daily assignment record including student, subject, attachment URL, and evaluation remark.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Daily Assignment ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Daily assignment details retrieved successfully'],
                    '400' => ['description' => 'id is required'],
                    '404' => ['description' => 'Daily assignment not found']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * GET /api/daily_assignment
     */
    public function index()
    {
        $this->_check_auth([], false);

        if (!$this->db->table_exists('daily_assignment')) {
            $this->_success('Daily assignments retrieved successfully', [
                'total'       => 0,
                'assignments' => []
            ]);
            return;
        }

        $student_id = $this->input->get('student_id') ?? $this->input->get('studentid');
        $class_id   = $this->input->get('class_id');
        $section_id = $this->input->get('section_id');
        $subject_id = $this->input->get('subject_id');
        $date       = $this->input->get('date');
        $limit      = min(200, max(1, (int)($this->input->get('limit') ?: 50)));
        $offset     = max(0, (int)($this->input->get('offset') ?: 0));

        $this->db->select('
            daily_assignment.*,
            students.id as student_id,
            students.admission_no,
            TRIM(CONCAT_WS(" ", students.firstname, students.middlename, students.lastname)) as student_name,
            classes.id as class_id,
            classes.class,
            sections.id as section_id,
            sections.section,
            subjects.id as subject_id,
            subjects.name as subject_name,
            subjects.code as subject_code,
            staff.name as evaluated_by_name,
            staff.surname as evaluated_by_surname
        ')
        ->from('daily_assignment')
        ->join('student_session', 'student_session.id = daily_assignment.student_session_id', 'left')
        ->join('students', 'students.id = student_session.student_id', 'left')
        ->join('classes', 'classes.id = student_session.class_id', 'left')
        ->join('sections', 'sections.id = student_session.section_id', 'left')
        ->join('subject_group_subjects', 'subject_group_subjects.id = daily_assignment.subject_group_subject_id', 'left')
        ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
        ->join('staff', 'staff.id = daily_assignment.evaluated_by', 'left');

        if (!empty($student_id)) {
            $this->db->where('students.id', (int)$student_id);
        }
        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', (int)$class_id);
        }
        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', (int)$section_id);
        }
        if (!empty($subject_id)) {
            $this->db->group_start()
                ->where('subject_group_subjects.subject_id', (int)$subject_id)
                ->or_where('daily_assignment.subject_group_subject_id', (int)$subject_id)
                ->group_end();
        }
        if (!empty($date)) {
            $this->db->where('daily_assignment.date', date('Y-m-d', strtotime($date)));
        }

        $this->db->order_by('daily_assignment.id', 'DESC');
        $this->db->limit($limit, $offset);
        $assignments = $this->db->get()->result_array();

        $app_base_url = rtrim(base_url(), '/');
        $site_root = preg_replace('#/api$#', '', $app_base_url);

        foreach ($assignments as &$item) {
            $item['attachment_url'] = !empty($item['attachment']) ? $site_root . '/uploads/homework/daily_assignment/' . $item['attachment'] : null;
        }

        $this->_success('Daily assignments retrieved successfully', [
            'total'       => count($assignments),
            'limit'       => $limit,
            'offset'      => $offset,
            'assignments' => $assignments
        ]);
    }

    /**
     * GET /api/daily_assignment/details
     */
    public function details($id_param = null)
    {
        $this->_check_auth([], false);

        $id = $id_param ?? $this->input->get('id');
        if (empty($id) || !is_numeric($id)) {
            $this->_error('id is required and must be numeric', 400);
            return;
        }

        if (!$this->db->table_exists('daily_assignment')) {
            $this->_error('Daily assignment not found', 404);
            return;
        }

        $this->db->select('
            daily_assignment.*,
            students.id as student_id,
            students.admission_no,
            TRIM(CONCAT_WS(" ", students.firstname, students.middlename, students.lastname)) as student_name,
            classes.id as class_id,
            classes.class,
            sections.id as section_id,
            sections.section,
            subjects.id as subject_id,
            subjects.name as subject_name,
            subjects.code as subject_code,
            staff.name as evaluated_by_name,
            staff.surname as evaluated_by_surname
        ')
        ->from('daily_assignment')
        ->join('student_session', 'student_session.id = daily_assignment.student_session_id', 'left')
        ->join('students', 'students.id = student_session.student_id', 'left')
        ->join('classes', 'classes.id = student_session.class_id', 'left')
        ->join('sections', 'sections.id = student_session.section_id', 'left')
        ->join('subject_group_subjects', 'subject_group_subjects.id = daily_assignment.subject_group_subject_id', 'left')
        ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
        ->join('staff', 'staff.id = daily_assignment.evaluated_by', 'left')
        ->where('daily_assignment.id', (int)$id);

        $row = $this->db->get()->row_array();
        if (empty($row)) {
            $this->_error('Daily assignment not found', 404);
            return;
        }

        $app_base_url = rtrim(base_url(), '/');
        $site_root = preg_replace('#/api$#', '', $app_base_url);
        $row['attachment_url'] = !empty($row['attachment']) ? $site_root . '/uploads/homework/daily_assignment/' . $row['attachment'] : null;

        $this->_success('Daily assignment details retrieved successfully', $row);
    }
}
