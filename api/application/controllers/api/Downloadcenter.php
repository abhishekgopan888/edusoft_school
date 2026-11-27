<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Downloadcenter extends Base
{
    public $swagger_meta = [
        'tag' => 'Download Center',
        'description' => 'Endpoints for retrieving shared study materials, syllabi, and downloadable content from Download Center.',
        'methods' => [
            'index' => [
                'path' => '/api/download_center',
                'summary' => 'Get download center contents',
                'description' => 'Retrieves shared download center contents including attachments, video links, content types, share dates, and creator details. Supports filtering by student_id, class_id, section_id, content_type_id, and keyword search.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter contents shared with a specific Student ID',
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
                        'name' => 'content_type_id',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by Content Type ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ],
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Search by title or description',
                        'schema' => ['type' => 'string']
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
                    '200' => ['description' => 'Download center contents retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'details' => [
                'path' => '/api/download_center/details',
                'summary' => 'Get download center content details by ID',
                'description' => 'Retrieves full details and downloadable file URLs for a single share_content record.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Share Content ID',
                        'schema' => ['type' => 'integer', 'example' => 1]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Download center content details retrieved successfully'],
                    '400' => ['description' => 'id is required'],
                    '404' => ['description' => 'Content not found']
                ]
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    private function _get_attachments($share_content_id, $site_root)
    {
        if (!$this->db->table_exists('share_upload_contents') || !$this->db->table_exists('upload_contents')) {
            return [];
        }

        $rows = $this->db->select('
            uc.id as upload_content_id,
            uc.content_type_id,
            ct.name as content_type_name,
            uc.real_name,
            uc.img_name,
            uc.dir_path,
            uc.file_type,
            uc.mime_type,
            uc.file_size,
            uc.vid_url,
            uc.vid_title
        ')
        ->from('share_upload_contents suc')
        ->join('upload_contents uc', 'uc.id = suc.upload_content_id')
        ->join('content_types ct', 'ct.id = uc.content_type_id', 'left')
        ->where('suc.share_content_id', (int)$share_content_id)
        ->get()->result_array();

        foreach ($rows as &$r) {
            $dir = !empty($r['dir_path']) ? trim($r['dir_path'], '/') . '/' : 'uploads/school_content/material/';
            $r['file_url'] = !empty($r['img_name']) ? ($site_root . '/' . $dir . $r['img_name']) : null;
        }

        return $rows;
    }

    /**
     * GET /api/download_center
     */
    public function index()
    {
        $this->_check_auth([], false);

        $student_id      = $this->input->get('student_id') ?? $this->input->get('studentid');
        $class_id        = $this->input->get('class_id');
        $section_id      = $this->input->get('section_id');
        $content_type_id = $this->input->get('content_type_id');
        $search          = $this->input->get('search');
        $limit           = min(200, max(1, (int)($this->input->get('limit') ?: 50)));
        $offset          = max(0, (int)($this->input->get('offset') ?: 0));

        $app_base_url = rtrim(base_url(), '/');
        $site_root    = preg_replace('#/api$#', '', $app_base_url);

        $content_types = [];
        if ($this->db->table_exists('content_types')) {
            $content_types = $this->db->select('id, name, description')
                ->from('content_types')
                ->order_by('name', 'ASC')
                ->get()->result_array();
        }

        $items = [];
        if ($this->db->table_exists('share_contents')) {
            $this->db->select('
                sc.id,
                sc.title,
                sc.send_to,
                sc.share_date,
                sc.valid_upto,
                sc.description,
                sc.created_at,
                staff.name as created_by_name,
                staff.surname as created_by_surname
            ')
            ->from('share_contents sc')
            ->join('staff', 'staff.id = sc.created_by', 'left');

            if (!empty($student_id) && $this->db->table_exists('share_content_for')) {
                $this->db->join('share_content_for scf', 'scf.share_content_id = sc.id', 'left');
                $this->db->group_start()
                    ->where('scf.student_id', (int)$student_id)
                    ->or_where('sc.send_to', 'public')
                    ->group_end();
            } elseif (!empty($class_id) && $this->db->table_exists('share_content_for')) {
                $this->db->join('share_content_for scf', 'scf.share_content_id = sc.id', 'left');
                $this->db->join('class_sections cs', 'cs.id = scf.class_section_id', 'left');
                $this->db->where('cs.class_id', (int)$class_id);
                if (!empty($section_id)) {
                    $this->db->where('cs.section_id', (int)$section_id);
                }
            }

            if (!empty($content_type_id) && $this->db->table_exists('share_upload_contents')) {
                $this->db->join('share_upload_contents suc_f', 'suc_f.share_content_id = sc.id', 'inner');
                $this->db->join('upload_contents uc_f', 'uc_f.id = suc_f.upload_content_id', 'inner');
                $this->db->where('uc_f.content_type_id', (int)$content_type_id);
            }

            if (!empty($search)) {
                $this->db->group_start()
                    ->like('sc.title', trim($search))
                    ->or_like('sc.description', trim($search))
                    ->group_end();
            }

            $this->db->group_by('sc.id');
            $this->db->order_by('sc.id', 'DESC');
            $this->db->limit($limit, $offset);
            $items = $this->db->get()->result_array();

            foreach ($items as &$item) {
                $item['id']          = (int)$item['id'];
                $item['created_by']  = trim(($item['created_by_name'] ?? '') . ' ' . ($item['created_by_surname'] ?? ''));
                $item['attachments'] = $this->_get_attachments($item['id'], $site_root);
            }
        }

        $this->_success('Download center contents retrieved successfully', [
            'total_records' => count($items),
            'limit'         => $limit,
            'offset'        => $offset,
            'content_types' => $content_types,
            'contents'      => $items
        ]);
    }

    /**
     * GET /api/download_center/details
     */
    public function details($id_param = null)
    {
        $this->_check_auth([], false);

        $id = $id_param ?? $this->input->get('id');
        if (empty($id) || !is_numeric($id)) {
            $this->_error('id is required and must be numeric', 400);
            return;
        }

        if (!$this->db->table_exists('share_contents')) {
            $this->_error('Content not found', 404);
            return;
        }

        $row = $this->db->select('
            sc.*,
            staff.name as created_by_name,
            staff.surname as created_by_surname
        ')
        ->from('share_contents sc')
        ->join('staff', 'staff.id = sc.created_by', 'left')
        ->where('sc.id', (int)$id)
        ->get()->row_array();

        if (empty($row)) {
            $this->_error('Content not found', 404);
            return;
        }

        $app_base_url = rtrim(base_url(), '/');
        $site_root    = preg_replace('#/api$#', '', $app_base_url);

        $row['id']          = (int)$row['id'];
        $row['created_by']  = trim(($row['created_by_name'] ?? '') . ' ' . ($row['created_by_surname'] ?? ''));
        $row['attachments'] = $this->_get_attachments($row['id'], $site_root);

        $this->_success('Download center content details retrieved successfully', $row);
    }
}
