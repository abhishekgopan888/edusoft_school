<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Driver_model extends MY_Model
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
            if ($id >= 100000) {
                $staff_id = (int)$id - 100000;
                $sql = "
                    SELECT 
                        (100000 + s.id) AS id,
                        TRIM(CONCAT_WS(' ', s.name, s.surname)) AS driver_name,
                        s.contact_no AS contact,
                        COALESCE(NULLIF(s.local_address, ''), NULLIF(s.permanent_address, ''), '') AS address,
                        '' AS licence,
                        '' AS age,
                        IF(s.is_active = 1, 'Active', 'Disabled') AS status,
                        s.image AS driver_image,
                        '' AS licence_front,
                        '' AS licence_back,
                        '' AS id_proof,
                        '' AS documents,
                        CONCAT('Staff Employee ID: ', s.employee_id) AS note
                    FROM staff s
                    WHERE s.id = " . $this->db->escape($staff_id);
                $query = $this->db->query($sql);
                return $query->row();
            }
            $this->db->select()->from('drivers');
            $this->db->where('drivers.id', $id);
            $query = $this->db->get();
            return $query->row();
        }

        $sql = "
            SELECT 
                d.id,
                d.driver_name,
                d.contact,
                d.address,
                d.licence,
                d.age,
                d.status,
                d.driver_image,
                d.licence_front,
                d.licence_back,
                d.id_proof,
                d.documents,
                d.note
            FROM drivers d

            UNION ALL

            SELECT 
                (100000 + s.id) AS id,
                TRIM(CONCAT_WS(' ', s.name, s.surname)) AS driver_name,
                s.contact_no AS contact,
                COALESCE(NULLIF(s.local_address, ''), NULLIF(s.permanent_address, ''), '') AS address,
                '' AS licence,
                '' AS age,
                IF(s.is_active = 1, 'Active', 'Disabled') AS status,
                s.image AS driver_image,
                '' AS licence_front,
                '' AS licence_back,
                '' AS id_proof,
                '' AS documents,
                CONCAT('Staff Employee ID: ', s.employee_id) AS note
            FROM staff s
            JOIN staff_roles sr ON sr.staff_id = s.id
            JOIN roles r ON r.id = sr.role_id
            WHERE LOWER(r.name) = 'driver'
            AND TRIM(CONCAT_WS(' ', s.name, s.surname)) NOT IN (
                SELECT driver_name FROM drivers WHERE driver_name IS NOT NULL AND driver_name != ''
            )
            ORDER BY id DESC
        ";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->trans_strict(false);
        $this->db->where('id', $id);
        $this->db->delete('drivers');
        $message   = DELETE_RECORD_CONSTANT . " On drivers id " . $id;
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
            $this->db->update('drivers', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On drivers id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            $this->db->trans_complete();
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            }
        } else {
            $this->db->insert('drivers', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On drivers id " . $insert_id;
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
