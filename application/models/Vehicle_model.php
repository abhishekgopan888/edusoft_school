<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vehicle_model extends MY_Model
{

    protected $current_session;
    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        $this->db->select()->from('vehicles');
        if ($id != null) {
            $this->db->where('vehicles.id', $id);
        } else {
            $this->db->order_by('vehicles.id','desc');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result_array();
        }
    }

    public function remove($id)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $this->db->where('id', $id);
        $this->db->delete('vehicles');
        
        $this->db->where('vehicle_id', $id);
        $this->db->delete('vehicle_routes');
        
        $message   = DELETE_RECORD_CONSTANT . " On vehicles id " . $id;
        $action    = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function add($data)
    {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('vehicles', $data);
            $message   = UPDATE_RECORD_CONSTANT . " On  vehicles id " . $data['id'];
            $action    = "Update";
            $record_id = $data['id'];
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
        } else {
            $this->db->insert('vehicles', $data);
            $insert_id = $this->db->insert_id();
            $message   = INSERT_RECORD_CONSTANT . " On vehicles id " . $insert_id;
            $action    = "Insert";
            $record_id = $insert_id;
            $this->log($message, $record_id, $action);
            //======================Code End==============================

            $this->db->trans_complete(); # Completing transaction
            /* Optional */

            if ($this->db->trans_status() === false) {
                # Something went wrong.
                $this->db->trans_rollback();
                return false;
            } else {
                //return $return_value;
            }
            return $insert_id;
        }
    }

    public function vehicleListByarray($array)
    {
        $this->db->select('*');
        $this->db->from('vehicles');
        $this->db->where_in('vehicles.id', $array);
        $query = $this->db->get();
        return $query->result();
    }

    public function getVehicleWithDetails($id = null)
    {
        $session_id = $this->db->escape($this->current_session);
        $subquery = "(SELECT COUNT(DISTINCT student_session.student_id) FROM student_session JOIN students ON students.id = student_session.student_id JOIN vehicle_routes ON vehicle_routes.id = student_session.vehroute_id WHERE vehicle_routes.vehicle_id = vehicles.id AND student_session.session_id = {$session_id} AND students.is_active = 'yes') as occupied_seats";

        $this->db->select('vehicles.*, drivers.driver_name, drivers.contact as driver_contact, transport_route.route_title as route_name, vehicle_routes.driver_id as vr_driver_id, ' . $subquery)->from('vehicles');
        $this->db->join('drivers', 'drivers.id = vehicles.driver_id', 'left');
        $this->db->join('vehicle_routes', 'vehicle_routes.vehicle_id = vehicles.id', 'left');
        $this->db->join('transport_route', 'transport_route.id = vehicle_routes.route_id', 'left');
        if ($id != null) {
            $this->db->where('vehicles.id', $id);
            $query = $this->db->get();
            $row = $query->row();
            if ($row && empty($row->driver_name) && !empty($row->vr_driver_id)) {
                $driver_info = $this->db->get_where('drivers', array('id' => $row->vr_driver_id))->row();
                if ($driver_info) {
                    $row->driver_name = $driver_info->driver_name;
                }
            }
            return $row;
        } else {
            $this->db->group_by('vehicles.id');
            $this->db->order_by('vehicles.id', 'desc');
            $query = $this->db->get();
            return $query->result_array();
        }
    }

    public function transferVehicle($vehicle_id, $branch, $driver_id)
    {
        $this->db->where('id', $vehicle_id);
        return $this->db->update('vehicles', array(
            'branch'    => $branch,
            'driver_id' => $driver_id,
        ));
    }

    public function getBranches()
    {
        $query = $this->db->get('branches');
        return $query->result_array();
    }

}

