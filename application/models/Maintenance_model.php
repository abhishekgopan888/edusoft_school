<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Maintenance_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = null)
    {
        $this->db->select('vehicle_maintenance.*, vehicles.vehicle_no, vehicles.vehicle_model')->from('vehicle_maintenance');
        $this->db->join('vehicles', 'vehicles.id = vehicle_maintenance.vehicle_id', 'left');
        if ($id != null) {
            $this->db->where('vehicle_maintenance.id', $id);
        } else {
            $this->db->order_by('vehicle_maintenance.id', 'desc');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result_array();
        }
    }

    public function getByVehicleId($vehicle_id)
    {
        if (!$this->db->table_exists('vehicle_maintenance')) {
            return array();
        }
        $this->db->select('vehicle_maintenance.*, vehicles.vehicle_no, vehicles.vehicle_model')->from('vehicle_maintenance');
        $this->db->join('vehicles', 'vehicles.id = vehicle_maintenance.vehicle_id', 'left');
        $this->db->where('vehicle_maintenance.vehicle_id', $vehicle_id);
        $this->db->order_by('vehicle_maintenance.id', 'desc');
        $query = $this->db->get();
        return $query->result_array();
    }

    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false);
        
        $this->db->where('id', $id);
        $this->db->delete('vehicle_maintenance');
        
        $message   = DELETE_RECORD_CONSTANT . " On vehicle_maintenance id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        
        $this->db->trans_complete(); # Completing transaction
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        } else {
            return true;
        }
    }

    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false);
        
        if (isset($data['id']) && $data['id'] > 0) {
            $this->db->where('id', $data['id']);
            $this->db->update('vehicle_maintenance', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On vehicle_maintenance id " . $data['id'];
            $action = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            
            $this->db->trans_complete(); # Completing transaction
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            } else {
                return true;
            }
        } else {
            $this->db->insert('vehicle_maintenance', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On vehicle_maintenance id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            
            $this->db->trans_complete(); # Completing transaction
            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                return false;
            } else {
                return $insert_id;
            }
        }
    }

    public function get_expiring_maintenance_alerts()
    {
        $today = date('Y-m-d');

        // Query the latest maintenance record for each vehicle
        $sql = "SELECT m.*, v.vehicle_no, v.vehicle_model 
                FROM vehicle_maintenance m
                JOIN (
                    SELECT vehicle_id, MAX(id) as max_id
                    FROM vehicle_maintenance
                    GROUP BY vehicle_id
                ) latest ON m.id = latest.max_id
                JOIN vehicles v ON m.vehicle_id = v.id";
        
        $query = $this->db->query($sql);
        $records = $query->result_array();

        $alerts = array();
        foreach ($records as $row) {
            $v_name = $row['vehicle_no'] . " (" . $row['vehicle_model'] . ")";
            
            // Check next service date
            if (!empty($row['next_service_date'])) {
                $days = (strtotime($row['next_service_date']) - strtotime($today)) / 86400;
                if ($days <= 10) {
                    $alerts[] = array(
                        'vehicle_id' => $row['vehicle_id'],
                        'vehicle_name' => $v_name,
                        'type' => 'Next Service Date',
                        'date' => $row['next_service_date'],
                        'days' => (int)round($days)
                    );
                }
            }

            // Check insurance expired date
            if (!empty($row['insurance_expired_date'])) {
                $days = (strtotime($row['insurance_expired_date']) - strtotime($today)) / 86400;
                if ($days <= 10) {
                    $alerts[] = array(
                        'vehicle_id' => $row['vehicle_id'],
                        'vehicle_name' => $v_name,
                        'type' => 'Insurance Expired Date',
                        'date' => $row['insurance_expired_date'],
                        'days' => (int)round($days)
                    );
                }
            }

            // Check tax expired date
            if (!empty($row['tax_expired_date'])) {
                $days = (strtotime($row['tax_expired_date']) - strtotime($today)) / 86400;
                if ($days <= 10) {
                    $alerts[] = array(
                        'vehicle_id' => $row['vehicle_id'],
                        'vehicle_name' => $v_name,
                        'type' => 'Tax Expired Date',
                        'date' => $row['tax_expired_date'],
                        'days' => (int)round($days)
                    );
                }
            }
        }

        return $alerts;
    }
}

