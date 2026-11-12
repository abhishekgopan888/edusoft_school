<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Attender_model extends MY_Model
{

    protected $current_session;
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        if ($id != null) {
            if (is_string($id) && strpos($id, 'staff_') === 0) {
                $staff_id = str_replace('staff_', '', $id);
                return $this->getStaffAttenderById($staff_id);
            }

            $this->db->select()->from('attenders');
            $this->db->where('attenders.id', $id);
            $query = $this->db->get();
            $res   = $query->row();

            if (!empty($res)) {
                $res->is_staff = false;
                return $res;
            }

            return $this->getStaffAttenderById($id);
        } else {
            $this->db->select()->from('attenders');
            $this->db->order_by('attenders.id', 'desc');
            $query     = $this->db->get();
            $attenders = $query->result_array();

            foreach ($attenders as &$att) {
                $att['is_staff'] = false;
            }
            unset($att);

            // Fetch staff members with role named 'Attender' (or containing 'attender')
            $this->db->select('staff.id as staff_id, staff.name, staff.surname, staff.employee_id, staff.local_address, staff.image, staff.is_active, staff.note, roles.name as role_name');
            $this->db->from('staff');
            $this->db->join('staff_roles', 'staff_roles.staff_id = staff.id', 'inner');
            $this->db->join('roles', 'roles.id = staff_roles.role_id', 'inner');
            $this->db->where('LOWER(roles.name) LIKE', '%attender%');
            $this->db->order_by('staff.id', 'desc');
            $staff_query     = $this->db->get();
            $staff_attenders = $staff_query->result_array();

            foreach ($staff_attenders as $staff) {
                $name     = trim($staff['name'] . ' ' . $staff['surname']);
                $emp_code = !empty($staff['employee_id']) ? ' (' . $staff['employee_id'] . ')' : '';
                $attenders[] = array(
                    'id'             => 'staff_' . $staff['staff_id'],
                    'attender_name'  => $name . $emp_code,
                    'age'            => '-',
                    'address'        => !empty($staff['local_address']) ? $staff['local_address'] : '-',
                    'id_proof'       => '',
                    'attender_image' => $staff['image'],
                    'documents'      => '',
                    'note'           => 'Staff Role: ' . $staff['role_name'] . (!empty($staff['note']) ? ' - ' . $staff['note'] : ''),
                    'status'         => ($staff['is_active'] == 1) ? 'Active' : 'Inactive',
                    'is_staff'       => true,
                    'staff_id'       => $staff['staff_id']
                );
            }

            return $attenders;
        }
    }

    private function getStaffAttenderById($staff_id)
    {
        $this->db->select('staff.id as staff_id, staff.name, staff.surname, staff.employee_id, staff.local_address, staff.image, staff.is_active, staff.note, roles.name as role_name');
        $this->db->from('staff');
        $this->db->join('staff_roles', 'staff_roles.staff_id = staff.id', 'inner');
        $this->db->join('roles', 'roles.id = staff_roles.role_id', 'inner');
        $this->db->where('staff.id', $staff_id);
        $this->db->where('LOWER(roles.name) LIKE', '%attender%');
        $query = $this->db->get();
        $staff = $query->row();

        if (!empty($staff)) {
            $name     = trim($staff->name . ' ' . $staff->surname);
            $emp_code = !empty($staff->employee_id) ? ' (' . $staff->employee_id . ')' : '';

            $obj = new stdClass();
            $obj->id             = 'staff_' . $staff->staff_id;
            $obj->attender_name  = $name . $emp_code;
            $obj->age            = '-';
            $obj->address        = !empty($staff->local_address) ? $staff->local_address : '-';
            $obj->id_proof       = '';
            $obj->attender_image = $staff->image;
            $obj->documents      = '';
            $obj->note           = 'Staff Role: ' . $staff->role_name . (!empty($staff->note) ? ' - ' . $staff->note : '');
            $obj->status         = ($staff->is_active == 1) ? 'Active' : 'Inactive';
            $obj->is_staff       = true;
            $obj->staff_id       = $staff->staff_id;

            return $obj;
        }

        return null;
    }

    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id);
        $this->db->delete('attenders');
        $message   = DELETE_RECORD_CONSTANT . " On attenders id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        $this->db->trans_complete();
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }
    }

    public function add($data)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('attenders', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On attenders id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
        } else {
            $this->db->insert('attenders', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On attenders id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
            return $insert_id;
        }
    }

}
