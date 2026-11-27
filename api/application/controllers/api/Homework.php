<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Homework extends Base
{
    public $swagger_meta = [
        'tag' => 'Homework Management',
        'description' => 'Endpoints for retrieving homework assignments, student submission statuses, and daily assignments. Requires X-API-Key or Bearer token.',
        'methods' => [
            'index' => [
                'path' => '/api/homework',
                'summary' => 'Get homework list',
                'description' => 'Retrieves homework assignments with class, section, subject, and staff information. Supports filtering by student_id, class_id, section_id, homework_status (pending, submitted, evaluated), subject_id, or date.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Student ID to fetch homework for (includes student-specific status: pending, submitted, evaluated).',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'class_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by class ID.',
                        'schema' => ['type' => 'integer', 'example' => 2]
                    ],
                    [
                        'name' => 'section_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by section ID.',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'homework_status',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by homework status: pending, submitted, evaluated (when student_id is provided).',
                        'schema' => ['type' => 'string', 'enum' => ['pending', 'submitted', 'evaluated'], 'example' => 'pending']
                    ],
                    [
                        'name' => 'subject_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by subject ID or subject_group_subject_id.',
                        'schema' => ['type' => 'integer', 'example' => 3]
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by homework date (YYYY-MM-DD). Alias: homework_date',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-26']
                    ],
                    [
                        'name' => 'limit',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Maximum number of records to return (default 50, max 200).',
                        'schema' => ['type' => 'integer', 'example' => 50]
                    ],
                    [
                        'name' => 'offset',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Number of records to skip for pagination (default 0).',
                        'schema' => ['type' => 'integer', 'example' => 0]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'List of homework assignments retrieved successfully.'],
                    '401' => ['description' => 'Unauthorized - Invalid or missing authentication token / API key.']
                ]
            ],
            'details' => [
                'path' => '/api/homework/details',
                'summary' => 'Get homework details by ID',
                'description' => 'Retrieves complete information for a specific homework assignment including subject, class, teacher, instructions, document URL, and optional student submission details.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Homework ID to retrieve (alias: homework_id).',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Optional student ID to include submission & evaluation status.',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Homework details retrieved successfully.'],
                    '400' => ['description' => 'Homework ID is required.'],
                    '404' => ['description' => 'Homework assignment not found.'],
                    '401' => ['description' => 'Unauthorized.']
                ]
            ],
            'daily' => [
                'path' => '/api/homework/daily',
                'summary' => 'Get daily assignments',
                'description' => 'Retrieves daily assignments for a student or session.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Student ID to fetch daily assignments for.',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter assignments by date (YYYY-MM-DD).',
                        'schema' => ['type' => 'string', 'format' => 'date', 'example' => '2026-11-26']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Daily assignments retrieved successfully.'],
                    '400' => ['description' => 'Student ID is required.'],
                    '401' => ['description' => 'Unauthorized.']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('setting_model', 'student_model'));
        $this->load->helper('url');
    }

    /**
     * GET /api/homework
     * Query params: student_id, class_id, section_id, homework_status, subject_id, date, limit, offset
     */
    public function index()
    {
        $this->_check_auth([], false);

        $student_id      = $this->input->get('student_id');
        $class_id        = $this->input->get('class_id');
        $section_id      = $this->input->get('section_id');
        $homework_status = $this->input->get('homework_status');
        $subject_id      = $this->input->get('subject_id');
        if (empty($subject_id)) {
            $subject_id = $this->input->get('subject_group_subject_id');
        }
        $date            = $this->input->get('date');
        if (empty($date)) {
            $date = $this->input->get('homework_date');
        }

        $limit  = (int)$this->input->get('limit');
        if ($limit <= 0 || $limit > 200) {
            $limit = 50;
        }
        $offset = (int)$this->input->get('offset');
        if ($offset < 0) {
            $offset = 0;
        }

        $current_session = $this->setting_model->getCurrentSession();

        // If student_id is provided, resolve student's class and section if not passed
        $student_session_id = null;
        if (!empty($student_id) && is_numeric($student_id)) {
            $student = $this->db->select('students.id, students.firstname, students.lastname, student_session.id as student_session_id, student_session.class_id, student_session.section_id')
                ->from('students')
                ->join('student_session', 'student_session.student_id = students.id', 'inner')
                ->where('students.id', (int)$student_id)
                ->order_by('student_session.id', 'DESC')
                ->limit(1)
                ->get()
                ->row();

            if (!empty($student)) {
                $student_session_id = $student->student_session_id;
                if (empty($class_id)) {
                    $class_id = $student->class_id;
                }
                if (empty($section_id)) {
                    $section_id = $student->section_id;
                }
            }
        }

        $this->db->select("
            homework.id,
            homework.class_id,
            classes.class,
            homework.section_id,
            sections.section,
            homework.session_id,
            homework.subject_group_subject_id,
            subject_group_subjects.subject_id,
            subjects.name as subject_name,
            subjects.code as subject_code,
            subjects.type as subject_type,
            subject_groups.id as subject_group_id,
            subject_groups.name as subject_group_name,
            homework.homework_date,
            homework.submit_date,
            homework.marks as max_marks,
            homework.description,
            homework.document,
            homework.create_date,
            homework.evaluation_date,
            homework.created_by as staff_id,
            staff.name as created_by_name,
            staff.surname as created_by_surname,
            staff.employee_id as created_by_employee_id,
            (SELECT COUNT(*) FROM submit_assignment WHERE submit_assignment.homework_id = homework.id) as total_assignments_submitted,
            (SELECT COUNT(*) FROM homework_evaluation WHERE homework_evaluation.homework_id = homework.id) as total_assignments_evaluated
        ");

        if (!empty($student_id)) {
            $this->db->select("
                submit_assignment.id as submission_id,
                submit_assignment.docs as submitted_document,
                submit_assignment.message as student_message,
                homework_evaluation.marks as student_marks,
                homework_evaluation.note as evaluation_note,
                homework_evaluation.date as student_evaluation_date,
                eval_staff.name as evaluated_by_name,
                eval_staff.surname as evaluated_by_surname,
                CASE 
                    WHEN homework_evaluation.id IS NOT NULL THEN 'evaluated'
                    WHEN submit_assignment.id IS NOT NULL THEN 'submitted'
                    ELSE 'pending'
                END as status
            ");
            $this->db->join('submit_assignment', 'submit_assignment.homework_id = homework.id AND submit_assignment.student_id = ' . (int)$student_id, 'left');
            $this->db->join('homework_evaluation', 'homework_evaluation.homework_id = homework.id AND homework_evaluation.student_id = ' . (int)$student_id, 'left');
            $this->db->join('staff as eval_staff', 'eval_staff.id = homework_evaluation.evaluated_by', 'left');
        }

        $this->db->from('homework');
        $this->db->join('classes', 'classes.id = homework.class_id', 'left');
        $this->db->join('sections', 'sections.id = homework.section_id', 'left');
        $this->db->join('subject_group_subjects', 'subject_group_subjects.id = homework.subject_group_subject_id', 'left');
        $this->db->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left');
        $this->db->join('subject_groups', 'subject_groups.id = subject_group_subjects.subject_group_id', 'left');
        $this->db->join('staff', 'staff.id = homework.created_by', 'left');

        if (!empty($class_id)) {
            $this->db->where('homework.class_id', (int)$class_id);
        }
        if (!empty($section_id)) {
            $this->db->where('homework.section_id', (int)$section_id);
        }
        if (!empty($subject_id)) {
            $this->db->group_start();
            $this->db->where('subject_group_subjects.subject_id', (int)$subject_id);
            $this->db->or_where('homework.subject_group_subject_id', (int)$subject_id);
            $this->db->group_end();
        }
        if (!empty($date)) {
            $this->db->where('homework.homework_date', date('Y-m-d', strtotime($date)));
        }

        $this->db->order_by('homework.homework_date', 'DESC');
        $this->db->order_by('homework.id', 'DESC');

        $query = $this->db->get();
        $results = $query->result_array();

        $app_base_url = rtrim(base_url(), '/');
        // If base_url ends in /api, strip it for media upload links
        $site_root = preg_replace('#/api$#', '', $app_base_url);

        $filtered_list = array();
        foreach ($results as $item) {
            $doc_url = null;
            if (!empty($item['document'])) {
                $doc_url = $site_root . '/uploads/homework/' . $item['document'];
            }
            $item['document_url'] = $doc_url;

            if (!empty($student_id)) {
                $item['submitted_document_url'] = !empty($item['submitted_document']) ? $site_root . '/uploads/homework/assignment/' . $item['submitted_document'] : null;

                if (!empty($homework_status) && strtolower($item['status']) !== strtolower($homework_status)) {
                    continue;
                }
            }

            $filtered_list[] = $item;
        }

        $total_count = count($filtered_list);
        $paginated_data = array_slice($filtered_list, $offset, $limit);

        $this->_success('Homework list retrieved successfully', array(
            'total'          => $total_count,
            'limit'          => $limit,
            'offset'         => $offset,
            'student_id'     => !empty($student_id) ? (int)$student_id : null,
            'class_id'       => !empty($class_id) ? (int)$class_id : null,
            'section_id'     => !empty($section_id) ? (int)$section_id : null,
            'homework_list'  => $paginated_data
        ));
    }

    /**
     * GET /api/homework/details
     * GET /api/homework/details/{id}
     * Query params: id or homework_id, student_id (optional)
     */
    public function details($id = null)
    {
        $this->_check_auth([], false);

        if (empty($id)) {
            $id = $this->input->get('id');
        }
        if (empty($id)) {
            $id = $this->input->get('homework_id');
        }

        if (empty($id) || !is_numeric($id)) {
            $this->_error('Homework ID is required and must be numeric', 400);
            return;
        }

        $student_id = $this->input->get('student_id');

        $this->db->select("
            homework.id,
            homework.class_id,
            classes.class,
            homework.section_id,
            sections.section,
            homework.session_id,
            homework.subject_group_subject_id,
            subject_group_subjects.subject_id,
            subjects.name as subject_name,
            subjects.code as subject_code,
            subjects.type as subject_type,
            subject_groups.id as subject_group_id,
            subject_groups.name as subject_group_name,
            homework.homework_date,
            homework.submit_date,
            homework.marks as max_marks,
            homework.description,
            homework.document,
            homework.create_date,
            homework.evaluation_date,
            homework.created_by as staff_id,
            staff.name as created_by_name,
            staff.surname as created_by_surname,
            staff.employee_id as created_by_employee_id,
            (SELECT COUNT(*) FROM submit_assignment WHERE submit_assignment.homework_id = homework.id) as total_assignments_submitted,
            (SELECT COUNT(*) FROM homework_evaluation WHERE homework_evaluation.homework_id = homework.id) as total_assignments_evaluated
        ");

        if (!empty($student_id)) {
            $this->db->select("
                submit_assignment.id as submission_id,
                submit_assignment.docs as submitted_document,
                submit_assignment.message as student_message,
                homework_evaluation.marks as student_marks,
                homework_evaluation.note as evaluation_note,
                homework_evaluation.date as student_evaluation_date,
                eval_staff.name as evaluated_by_name,
                eval_staff.surname as evaluated_by_surname,
                CASE 
                    WHEN homework_evaluation.id IS NOT NULL THEN 'evaluated'
                    WHEN submit_assignment.id IS NOT NULL THEN 'submitted'
                    ELSE 'pending'
                END as status
            ");
            $this->db->join('submit_assignment', 'submit_assignment.homework_id = homework.id AND submit_assignment.student_id = ' . (int)$student_id, 'left');
            $this->db->join('homework_evaluation', 'homework_evaluation.homework_id = homework.id AND homework_evaluation.student_id = ' . (int)$student_id, 'left');
            $this->db->join('staff as eval_staff', 'eval_staff.id = homework_evaluation.evaluated_by', 'left');
        }

        $this->db->from('homework');
        $this->db->join('classes', 'classes.id = homework.class_id', 'left');
        $this->db->join('sections', 'sections.id = homework.section_id', 'left');
        $this->db->join('subject_group_subjects', 'subject_group_subjects.id = homework.subject_group_subject_id', 'left');
        $this->db->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left');
        $this->db->join('subject_groups', 'subject_groups.id = subject_group_subjects.subject_group_id', 'left');
        $this->db->join('staff', 'staff.id = homework.created_by', 'left');
        $this->db->where('homework.id', (int)$id);

        $row = $this->db->get()->row_array();

        if (empty($row)) {
            $this->_error('Homework assignment not found', 404);
            return;
        }

        $app_base_url = rtrim(base_url(), '/');
        $site_root = preg_replace('#/api$#', '', $app_base_url);

        $row['document_url'] = !empty($row['document']) ? $site_root . '/uploads/homework/' . $row['document'] : null;

        if (!empty($student_id)) {
            $row['submitted_document_url'] = !empty($row['submitted_document']) ? $site_root . '/uploads/homework/assignment/' . $row['submitted_document'] : null;
        }

        $this->_success('Homework details retrieved successfully', $row);
    }

    /**
     * GET /api/homework/daily
     * Query params: student_id (required), date (optional)
     */
    public function daily()
    {
        $this->_check_auth([], false);

        $student_id = $this->input->get('student_id');
        if (empty($student_id) || !is_numeric($student_id)) {
            $this->_error('student_id is required and must be numeric', 400);
            return;
        }

        $date = $this->input->get('date');

        $this->db->select('daily_assignment.*, subjects.name as subject_name, subjects.code as subject_code')
            ->from('daily_assignment')
            ->join('student_session', 'student_session.id = daily_assignment.student_session_id', 'left')
            ->join('subject_group_subjects', 'subject_group_subjects.id = daily_assignment.subject_group_subject_id', 'left')
            ->join('subjects', 'subjects.id = subject_group_subjects.subject_id', 'left')
            ->group_start()
                ->where('daily_assignment.student_session_id', (int)$student_id)
                ->or_where('student_session.student_id', (int)$student_id)
            ->group_end();

        if (!empty($date)) {
            $this->db->where('daily_assignment.date', date('Y-m-d', strtotime($date)));
        }

        $this->db->order_by('daily_assignment.id', 'DESC');
        $assignments = $this->db->get()->result_array();

        $app_base_url = rtrim(base_url(), '/');
        $site_root = preg_replace('#/api$#', '', $app_base_url);

        foreach ($assignments as &$item) {
            $item['attachment_url'] = !empty($item['attachment']) ? $site_root . '/uploads/homework/daily_assignment/' . $item['attachment'] : null;
        }

        $this->_success('Daily assignments retrieved successfully', array(
            'student_id'  => (int)$student_id,
            'total'       => count($assignments),
            'assignments' => $assignments
        ));
    }
}
