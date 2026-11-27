<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Fees extends Base
{
    public $swagger_meta = [
        'tag' => 'Fees Management',
        'description' => 'Endpoints for retrieving student fees, fee groups, payment statuses, and transport fees.',
        'methods' => [
            'index' => [
                'path' => '/api/fees',
                'summary' => 'Get student fees list',
                'description' => 'Retrieves assigned fee records with student, class, fee group, fee type, amount, paid, discount, fine, balance, and status details. Supports filtering by student_id, class_id, section_id, and status.',
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
                        'name' => 'status',
                        'in' => 'query',
                        'required' => false,
                        'description' => 'Filter by fee payment status (paid, partial, unpaid)',
                        'schema' => ['type' => 'string', 'enum' => ['paid', 'partial', 'unpaid']]
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
                    '200' => ['description' => 'Student fees retrieved successfully'],
                    '401' => ['description' => 'Unauthorized']
                ]
            ],
            'details' => [
                'path' => '/api/fees/details',
                'summary' => 'Get student fee details by student_id',
                'description' => 'Retrieves complete fee breakdown for a specific student including regular fee groups, payment history, transport fees, and overall balance summary.',
                'http_method' => 'get',
                'security' => [['BearerAuth' => []], ['ApiKeyAuth' => []], []],
                'parameters' => [
                    [
                        'name' => 'student_id',
                        'in' => 'query',
                        'required' => true,
                        'description' => 'Student ID',
                        'schema' => ['type' => 'integer', 'example' => 825]
                    ]
                ],
                'responses' => [
                    '200' => ['description' => 'Student fee details retrieved successfully'],
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

    private function _parse_amount_detail($amount_detail_json)
    {
        $paid = 0.0;
        $discount = 0.0;
        $fine = 0.0;
        $payments = [];

        if (!empty($amount_detail_json) && is_string($amount_detail_json)) {
            $decoded = json_decode($amount_detail_json, true);
            if (is_array($decoded)) {
                foreach ($decoded as $inv_key => $item) {
                    $a = isset($item['amount']) ? (float)$item['amount'] : 0.0;
                    $d = isset($item['amount_discount']) ? (float)$item['amount_discount'] : 0.0;
                    $f = isset($item['amount_fine']) ? (float)$item['amount_fine'] : 0.0;
                    $paid += $a;
                    $discount += $d;
                    $fine += $f;
                    $payments[] = [
                        'receipt_no'   => $item['inv_no'] ?? $inv_key,
                        'amount'       => $a,
                        'discount'     => $d,
                        'fine'         => $f,
                        'date'         => $item['date'] ?? null,
                        'payment_mode' => $item['payment_mode'] ?? null,
                        'collected_by' => $item['collected_by'] ?? null,
                        'description'  => $item['description'] ?? ''
                    ];
                }
            }
        }

        return [
            'paid'     => round($paid, 2),
            'discount' => round($discount, 2),
            'fine'     => round($fine, 2),
            'payments' => $payments
        ];
    }

    private function _fetch_student_regular_fees($student_id = null, $class_id = null, $section_id = null)
    {
        if (!$this->db->table_exists('student_fees_master') || !$this->db->table_exists('fee_groups_feetype')) {
            return [];
        }

        $this->db->select('
            sfm.id as student_fees_master_id,
            sfm.student_session_id,
            sfm.fee_session_group_id,
            s.id as student_id,
            s.admission_no,
            s.roll_no,
            TRIM(CONCAT_WS(" ", s.firstname, s.middlename, s.lastname)) as student_name,
            s.mobileno as student_phone,
            ss.class_id,
            c.class,
            ss.section_id,
            sec.section,
            fgf.id as fee_groups_feetype_id,
            fg.id as fee_group_id,
            fg.name as fee_group_name,
            ft.id as feetype_id,
            ft.type as fee_type,
            ft.code as fee_code,
            fgf.amount,
            fgf.due_date,
            fgf.fine_type,
            fgf.fine_percentage,
            fgf.fine_amount,
            sfd.id as deposit_id,
            sfd.amount_detail
        ')
        ->from('student_fees_master sfm')
        ->join('student_session ss', 'ss.id = sfm.student_session_id')
        ->join('students s', 's.id = ss.student_id')
        ->join('classes c', 'c.id = ss.class_id', 'left')
        ->join('sections sec', 'sec.id = ss.section_id', 'left')
        ->join('fee_session_groups fsg', 'fsg.id = sfm.fee_session_group_id')
        ->join('fee_groups fg', 'fg.id = fsg.fee_groups_id', 'left')
        ->join('fee_groups_feetype fgf', 'fgf.fee_session_group_id = fsg.id')
        ->join('feetype ft', 'ft.id = fgf.feetype_id', 'left')
        ->join('student_fees_deposite sfd', 'sfd.student_fees_master_id = sfm.id AND sfd.fee_groups_feetype_id = fgf.id', 'left')
        ->where('s.is_active', 'yes');

        if (!empty($student_id)) {
            $this->db->where('s.id', (int)$student_id);
        }
        if (!empty($class_id)) {
            $this->db->where('ss.class_id', (int)$class_id);
        }
        if (!empty($section_id)) {
            $this->db->where('ss.section_id', (int)$section_id);
        }

        $this->db->order_by('s.id', 'ASC');
        $this->db->order_by('fgf.due_date', 'ASC');

        $rows = $this->db->get()->result_array();
        $result = [];

        foreach ($rows as $r) {
            $amount = (float)$r['amount'];
            $parsed = $this->_parse_amount_detail($r['amount_detail']);
            $balance = max(0.0, round($amount - ($parsed['paid'] + $parsed['discount']), 2));

            if ($balance <= 0 && $amount > 0) {
                $status = 'paid';
            } elseif ($parsed['paid'] > 0) {
                $status = 'partial';
            } else {
                $status = 'unpaid';
            }

            $result[] = [
                'student_fees_master_id' => (int)$r['student_fees_master_id'],
                'fee_groups_feetype_id'  => (int)$r['fee_groups_feetype_id'],
                'student_id'             => (int)$r['student_id'],
                'admission_no'           => (string)$r['admission_no'],
                'student_name'           => $r['student_name'],
                'class'                  => (string)$r['class'],
                'section'                => (string)$r['section'],
                'fee_group_id'           => (int)$r['fee_group_id'],
                'fee_group_name'         => (string)$r['fee_group_name'],
                'feetype_id'             => (int)$r['feetype_id'],
                'fee_type'               => (string)$r['fee_type'],
                'fee_code'               => (string)$r['fee_code'],
                'due_date'               => $r['due_date'],
                'amount'                 => round($amount, 2),
                'paid_amount'            => $parsed['paid'],
                'discount_amount'        => $parsed['discount'],
                'fine_amount'            => $parsed['fine'],
                'balance_amount'         => $balance,
                'status'                 => $status,
                'payment_history'        => $parsed['payments']
            ];
        }

        return $result;
    }

    private function _fetch_student_transport_fees($student_id)
    {
        if (!$this->db->table_exists('student_transport_fees')) {
            return [];
        }

        $this->db->select('
            stf.id as student_transport_fee_id,
            stf.student_session_id,
            rpp.fees as amount,
            rpp.pickup_time,
            pp.name as pickup_point_name,
            tr.route_title,
            sfd.amount_detail
        ')
        ->from('student_transport_fees stf')
        ->join('student_session ss', 'ss.id = stf.student_session_id')
        ->join('route_pickup_point rpp', 'rpp.id = stf.route_pickup_point_id', 'left')
        ->join('pickup_point pp', 'pp.id = rpp.pickup_point_id', 'left')
        ->join('transport_route tr', 'tr.id = rpp.transport_route_id', 'left')
        ->join('student_fees_deposite sfd', 'sfd.student_transport_fee_id = stf.id', 'left')
        ->where('ss.student_id', (int)$student_id);

        $rows = $this->db->get()->result_array();
        $result = [];

        foreach ($rows as $r) {
            $amount = (float)$r['amount'];
            $parsed = $this->_parse_amount_detail($r['amount_detail']);
            $balance = max(0.0, round($amount - ($parsed['paid'] + $parsed['discount']), 2));

            if ($balance <= 0 && $amount > 0) {
                $status = 'paid';
            } elseif ($parsed['paid'] > 0) {
                $status = 'partial';
            } else {
                $status = 'unpaid';
            }

            $result[] = [
                'student_transport_fee_id' => (int)$r['student_transport_fee_id'],
                'route_title'              => (string)$r['route_title'],
                'pickup_point_name'        => (string)$r['pickup_point_name'],
                'pickup_time'              => (string)$r['pickup_time'],
                'amount'                   => round($amount, 2),
                'paid_amount'              => $parsed['paid'],
                'discount_amount'          => $parsed['discount'],
                'fine_amount'              => $parsed['fine'],
                'balance_amount'           => $balance,
                'status'                   => $status,
                'payment_history'          => $parsed['payments']
            ];
        }

        return $result;
    }

    /**
     * GET /api/fees
     */
    public function index()
    {
        $this->_check_auth([], false);

        $student_id = $this->input->get('student_id') ?? $this->input->get('studentid');
        $class_id   = $this->input->get('class_id');
        $section_id = $this->input->get('section_id');
        $status     = $this->input->get('status') ? strtolower(trim($this->input->get('status'))) : null;
        $limit      = min(200, max(1, (int)($this->input->get('limit') ?: 50)));
        $offset     = max(0, (int)($this->input->get('offset') ?: 0));

        $items = $this->_fetch_student_regular_fees($student_id, $class_id, $section_id);

        if (!empty($status) && in_array($status, ['paid', 'partial', 'unpaid'])) {
            $items = array_values(array_filter($items, function ($it) use ($status) {
                return $it['status'] === $status;
            }));
        }

        $total_amount   = 0.0;
        $total_paid     = 0.0;
        $total_discount = 0.0;
        $total_fine     = 0.0;
        $total_balance  = 0.0;

        foreach ($items as $it) {
            $total_amount   += $it['amount'];
            $total_paid     += $it['paid_amount'];
            $total_discount += $it['discount_amount'];
            $total_fine     += $it['fine_amount'];
            $total_balance  += $it['balance_amount'];
        }

        $total_records = count($items);
        $paged_items   = array_slice($items, $offset, $limit);

        $this->_success('Student fees retrieved successfully', [
            'total_records' => $total_records,
            'limit'         => $limit,
            'offset'        => $offset,
            'summary'       => [
                'total_amount'   => round($total_amount, 2),
                'total_paid'     => round($total_paid, 2),
                'total_discount' => round($total_discount, 2),
                'total_fine'     => round($total_fine, 2),
                'total_balance'  => round($total_balance, 2)
            ],
            'fees'          => $paged_items
        ]);
    }

    /**
     * GET /api/fees/details
     */
    public function details()
    {
        $this->_check_auth([], false);

        $student_id = $this->input->get('student_id') ?? $this->input->get('studentid');
        if (empty($student_id) || !is_numeric($student_id)) {
            $this->_error('student_id is required and must be numeric', 400);
            return;
        }

        $student = $this->db->select('s.id as student_id, s.admission_no, s.roll_no, TRIM(CONCAT_WS(" ", s.firstname, s.middlename, s.lastname)) as student_name, s.mobileno as student_phone, c.class, sec.section')
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

        $regular_fees   = $this->_fetch_student_regular_fees((int)$student_id);
        $transport_fees = $this->_fetch_student_transport_fees((int)$student_id);

        $total_amount   = 0.0;
        $total_paid     = 0.0;
        $total_discount = 0.0;
        $total_fine     = 0.0;
        $total_balance  = 0.0;

        foreach (array_merge($regular_fees, $transport_fees) as $it) {
            $total_amount   += $it['amount'];
            $total_paid     += $it['paid_amount'];
            $total_discount += $it['discount_amount'];
            $total_fine     += $it['fine_amount'];
            $total_balance  += $it['balance_amount'];
        }

        $this->_success('Student fee details retrieved successfully', [
            'student'        => $student,
            'summary'        => [
                'total_amount'   => round($total_amount, 2),
                'total_paid'     => round($total_paid, 2),
                'total_discount' => round($total_discount, 2),
                'total_fine'     => round($total_fine, 2),
                'total_balance'  => round($total_balance, 2)
            ],
            'fee_groups'     => $regular_fees,
            'transport_fees' => $transport_fees
        ]);
    }
}
