<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Liveclass extends Base
{
    public $swagger_meta = [
        'tag' => 'Live Class',
        'description' => 'Endpoints for retrieving scheduled and past Zoom and Google Meet live classes.',
        'methods' => [
            'index' => [
                'path' => '/api/live_class',
                'summary' => 'Get live classes list (Zoom & Google Meet)',
                'description' => 'Retrieves live classes with title, date, duration, subject, class, section, teacher, join URL, and status. Supports filtering by student_id, class_id, section_id, staff_id, date, status, and platform.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter live classes for a specific Student ID (auto-resolves class & section)',
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
                        'name' => 'staff_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Teacher/Staff ID',
                        'schema' => ['type' => 'integer']
                    ],
                    [
                        'name' => 'date',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by class date (YYYY-MM-DD)',
                        'schema' => ['type' => 'string', 'format' => 'date']
                    ],
                    [
                        'name' => 'status',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by status (0=Awaited, 1=Cancelled, 2=Finished)',
                        'schema' => ['type' => 'integer', 'enum' => [0, 1, 2]]
                    ],
                    [
                        'name' => 'platform',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by platform (all, zoom, gmeet)',
                        'schema' => ['type' => 'string', 'enum' => ['all', 'zoom', 'gmeet'], 'example' => 'all']
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
                    '200' => ['description' => 'Live classes retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'details' => [
                'path' => '/api/live_class/details',
                'summary' => 'Get live class details by ID',
                'description' => 'Retrieves complete details for a single live class including join URL, password, teacher, class/section, and description.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Live Class ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'platform',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Platform (zoom or gmeet). Defaults to zoom.',
                        'schema' => ['type' => 'string', 'enum' => ['zoom', 'gmeet'], 'example' => 'zoom']
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Live class details retrieved successfully'],
                    '400' => ['description' => 'id is required'],
                    '404' => ['description' => 'Live class not found']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _status_label($status_code)
    {
        switch ((int)$status_code) {
            case 1:
                return 'Cancelled';
            case 2:
                return 'Finished';
            default:
                return 'Awaited';
        }
    }

    private function _fetch_zoom_classes($filters)
    {
        if (!$this->db->table_exists('conferences')) {
            return [];
        }

        $this->db->select('
            c.id,
            c.title,
            c.date,
            c.duration,
            c.password,
            c.subject,
            c.description,
            c.timezone,
            c.return_response,
            c.status,
            c.created_at,
            staff.id as staff_id,
            TRIM(CONCAT_WS(" ", staff.name, staff.surname)) as teacher_name,
            GROUP_CONCAT(DISTINCT CONCAT(cls.class, " (", sec.section, ")") SEPARATOR ", ") as class_sections
        ')
        ->from('conferences c')
        ->join('staff', 'staff.id = c.staff_id', 'left')
        ->join('conference_sections cs_map', 'cs_map.conference_id = c.id', 'left')
        ->join('class_sections cls_sec', 'cls_sec.id = cs_map.cls_section_id', 'left')
        ->join('classes cls', 'cls.id = cls_sec.class_id', 'left')
        ->join('sections sec', 'sec.id = cls_sec.section_id', 'left')
        ->where('c.purpose', 'class');

        if (!empty($filters['class_id'])) {
            $this->db->group_start()
                ->where('cls_sec.class_id', (int)$filters['class_id'])
                ->or_where('c.class_id', (int)$filters['class_id'])
                ->group_end();
        }
        if (!empty($filters['section_id'])) {
            $this->db->group_start()
                ->where('cls_sec.section_id', (int)$filters['section_id'])
                ->or_where('c.section_id', (int)$filters['section_id'])
                ->group_end();
        }
        if (!empty($filters['staff_id'])) {
            $this->db->where('c.staff_id', (int)$filters['staff_id']);
        }
        if (!empty($filters['date'])) {
            $this->db->where('DATE(c.date)', date('Y-m-d', strtotime($filters['date'])));
        }
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $this->db->where('c.status', (int)$filters['status']);
        }

        $this->db->group_by('c.id');
        $this->db->order_by('c.date', 'DESC');
        $rows = $this->db->get()->result_array();

        $result = [];
        foreach ($rows as $r) {
            $join_url = null;
            if (!empty($r['return_response'])) {
                $resp = json_decode($r['return_response'], true);
                if (is_array($resp) && !empty($resp['join_url'])) {
                    $join_url = $resp['join_url'];
                }
            }
            $result[] = [
                'id'             => (int)$r['id'],
                'platform'       => 'zoom',
                'title'          => $r['title'],
                'date'           => $r['date'],
                'duration'       => (int)$r['duration'],
                'subject'        => $r['subject'],
                'class_sections' => $r['class_sections'],
                'staff_id'       => (int)$r['staff_id'],
                'teacher_name'   => $r['teacher_name'],
                'join_url'       => $join_url,
                'password'       => $r['password'],
                'timezone'       => $r['timezone'],
                'status'         => (int)$r['status'],
                'status_label'   => $this->_status_label($r['status']),
                'description'    => $r['description']
            ];
        }

        return $result;
    }

    private function _fetch_gmeet_classes($filters)
    {
        if (!$this->db->table_exists('gmeet')) {
            return [];
        }

        $this->db->select('
            g.id,
            g.title,
            g.date,
            g.duration,
            g.subject,
            g.url as join_url,
            g.description,
            g.timezone,
            g.status,
            g.created_at,
            staff.id as staff_id,
            TRIM(CONCAT_WS(" ", staff.name, staff.surname)) as teacher_name,
            GROUP_CONCAT(DISTINCT CONCAT(cls.class, " (", sec.section, ")") SEPARATOR ", ") as class_sections
        ')
        ->from('gmeet g')
        ->join('staff', 'staff.id = g.staff_id', 'left')
        ->join('gmeet_sections gs_map', 'gs_map.gmeet_id = g.id', 'left')
        ->join('class_sections cls_sec', 'cls_sec.id = gs_map.cls_section_id', 'left')
        ->join('classes cls', 'cls.id = cls_sec.class_id', 'left')
        ->join('sections sec', 'sec.id = cls_sec.section_id', 'left')
        ->where('g.purpose', 'class');

        if (!empty($filters['class_id'])) {
            $this->db->where('cls_sec.class_id', (int)$filters['class_id']);
        }
        if (!empty($filters['section_id'])) {
            $this->db->where('cls_sec.section_id', (int)$filters['section_id']);
        }
        if (!empty($filters['staff_id'])) {
            $this->db->where('g.staff_id', (int)$filters['staff_id']);
        }
        if (!empty($filters['date'])) {
            $this->db->where('DATE(g.date)', date('Y-m-d', strtotime($filters['date'])));
        }
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $this->db->where('g.status', (int)$filters['status']);
        }

        $this->db->group_by('g.id');
        $this->db->order_by('g.date', 'DESC');
        $rows = $this->db->get()->result_array();

        $result = [];
        foreach ($rows as $r) {
            $result[] = [
                'id'             => (int)$r['id'],
                'platform'       => 'gmeet',
                'title'          => $r['title'],
                'date'           => $r['date'],
                'duration'       => (int)$r['duration'],
                'subject'        => $r['subject'],
                'class_sections' => $r['class_sections'],
                'staff_id'       => (int)$r['staff_id'],
                'teacher_name'   => $r['teacher_name'],
                'join_url'       => $r['join_url'],
                'password'       => null,
                'timezone'       => $r['timezone'],
                'status'         => (int)$r['status'],
                'status_label'   => $this->_status_label($r['status']),
                'description'    => $r['description']
            ];
        }

        return $result;
    }

    /**
     * GET /api/live_class
     */
    public function index()
    {
        $this->_check_auth([], false);

        $student_id = $this->input->get('student_id') ?? $this->input->get('studentid');
        $class_id   = $this->input->get('class_id');
        $section_id = $this->input->get('section_id');
        $staff_id   = $this->input->get('staff_id');
        $date       = $this->input->get('date');
        $status     = $this->input->get('status');
        $platform   = $this->input->get('platform') ? strtolower(trim($this->input->get('platform'))) : 'all';
        $limit      = min(200, max(1, (int)($this->input->get('limit') ?: 50)));
        $offset     = max(0, (int)($this->input->get('offset') ?: 0));

        if (!empty($student_id) && empty($class_id)) {
            $ss = $this->db->select('class_id, section_id')
                ->from('student_session')
                ->where('student_id', (int)$student_id)
                ->order_by('id', 'DESC')
                ->limit(1)
                ->get()->row_array();
            if ($ss) {
                $class_id   = $ss['class_id'];
                $section_id = $ss['section_id'];
            }
        }

        $filters = compact('class_id', 'section_id', 'staff_id', 'date', 'status');

        $classes_list = [];
        if ($platform === 'all' || $platform === 'zoom') {
            $classes_list = array_merge($classes_list, $this->_fetch_zoom_classes($filters));
        }
        if ($platform === 'all' || $platform === 'gmeet') {
            $classes_list = array_merge($classes_list, $this->_fetch_gmeet_classes($filters));
        }

        usort($classes_list, function ($a, $b) {
            return strcmp((string)$b['date'], (string)$a['date']);
        });

        $total = count($classes_list);
        $paged = array_slice($classes_list, $offset, $limit);

        $this->_success('Live classes retrieved successfully', [
            'total_records' => $total,
            'limit'         => $limit,
            'offset'        => $offset,
            'live_classes'  => $paged
        ]);
    }

    /**
     * GET /api/live_class/details
     */
    public function details()
    {
        $this->_check_auth([], false);

        $id       = $this->input->get('id');
        $platform = $this->input->get('platform') ? strtolower(trim($this->input->get('platform'))) : 'zoom';

        if (empty($id) || !is_numeric($id)) {
            $this->_error('id is required and must be numeric', 400);
            return;
        }

        $items = ($platform === 'gmeet')
            ? $this->_fetch_gmeet_classes(['class_id' => null, 'section_id' => null, 'staff_id' => null, 'date' => null, 'status' => null])
            : $this->_fetch_zoom_classes(['class_id' => null, 'section_id' => null, 'staff_id' => null, 'date' => null, 'status' => null]);

        $matched = null;
        foreach ($items as $it) {
            if ((int)$it['id'] === (int)$id) {
                $matched = $it;
                break;
            }
        }

        if (empty($matched)) {
            $this->_error('Live class not found', 404);
            return;
        }

        $this->_success('Live class details retrieved successfully', $matched);
    }
}
