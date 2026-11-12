<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Boardingpoint_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = null, $filters = array())
    {
        $this->db->select("
            boarding_point.id,
            boarding_point.studentid,
            boarding_point.busid,
            boarding_point.attenderid,
            boarding_point.status,
            boarding_point.timestamp,
            students.admission_no,
            students.roll_no,
            students.firstname,
            students.middlename,
            students.lastname,
            TRIM(CONCAT_WS(' ', students.firstname, students.middlename, students.lastname)) AS student_name,
            classes.class AS grade,
            sections.section AS section,
            TRIM(CONCAT_WS(' ', classes.class, sections.section)) AS grade_section,
            COALESCE(NULLIF(students.current_address, ''), NULLIF(students.permanent_address, ''), students.guardian_address, '') AS address,
            students.current_address,
            students.permanent_address,
            students.guardian_address,
            students.mobileno AS student_mobileno,
            students.gender,
            students.image AS student_image,
            vehicles.vehicle_no,
            vehicles.vehicle_model,
            attenders.attender_name
        ")
        ->from('boarding_point')
        ->join('students', 'students.id = boarding_point.studentid', 'left')
        ->join('student_session', 'student_session.student_id = students.id', 'left')
        ->join('classes', 'classes.id = student_session.class_id', 'left')
        ->join('sections', 'sections.id = student_session.section_id', 'left')
        ->join('vehicles', 'vehicles.id = boarding_point.busid', 'left')
        ->join('attenders', 'attenders.id = boarding_point.attenderid', 'left');

        if ($id !== null) {
            $this->db->where('boarding_point.id', $id);
        }

        if (!empty($filters['status'])) {
            $this->db->where('boarding_point.status', $filters['status']);
        }

        if (!empty($filters['busid'])) {
            $this->db->where('boarding_point.busid', $filters['busid']);
        }

        if (!empty($filters['attenderid'])) {
            $this->db->where('boarding_point.attenderid', $filters['attenderid']);
        }

        if (!empty($filters['studentid'])) {
            $this->db->where('boarding_point.studentid', $filters['studentid']);
        }

        if (!empty($filters['date'])) {
            $this->db->where('DATE(boarding_point.timestamp)', $filters['date']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $this->db->group_start();
            $this->db->like('students.firstname', $search);
            $this->db->or_like('students.lastname', $search);
            $this->db->or_like('students.admission_no', $search);
            $this->db->or_like('students.roll_no', $search);
            $this->db->or_like('classes.class', $search);
            $this->db->or_like('sections.section', $search);
            $this->db->or_like('vehicles.vehicle_no', $search);
            $this->db->or_like('attenders.attender_name', $search);
            $this->db->or_like('boarding_point.status', $search);
            $this->db->or_like('students.current_address', $search);
            $this->db->group_end();
        }

        $this->db->group_by('boarding_point.id');
        $this->db->order_by('boarding_point.id', 'DESC');

        $query = $this->db->get();

        if ($id !== null) {
            return $query->row_array();
        } else {
            return $query->result_array();
        }
    }

    public function add($data)
    {
        if (isset($data['id']) && !empty($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('boarding_point', $data);
            return $data['id'];
        } else {
            $this->db->insert('boarding_point', $data);
            return $this->db->insert_id();
        }
    }

    public function remove($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('boarding_point');
        return true;
    }
}
