<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Boardingpoint_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get($id = null, $filters = array())
    {
        if ($id !== null) {
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
                attenders.attender_name,
                COALESCE(
                    (
                        SELECT bp_p.timestamp 
                        FROM boarding_point bp_p 
                        WHERE bp_p.studentid = boarding_point.studentid 
                          AND DATE(bp_p.timestamp) = DATE(boarding_point.timestamp) 
                          AND bp_p.status = 'pending' 
                        ORDER BY bp_p.id DESC LIMIT 1
                    ),
                    CONCAT(DATE(boarding_point.timestamp), ' 00:00:00')
                ) AS pending_timestamp,
                (
                    SELECT bp_b.timestamp 
                    FROM boarding_point bp_b 
                    WHERE bp_b.studentid = boarding_point.studentid 
                      AND DATE(bp_b.timestamp) = DATE(boarding_point.timestamp) 
                      AND bp_b.status = 'boarded' 
                    ORDER BY bp_b.id DESC LIMIT 1
                ) AS boarded_timestamp,
                (
                    SELECT bp_d.timestamp 
                    FROM boarding_point bp_d 
                    WHERE bp_d.studentid = boarding_point.studentid 
                      AND DATE(bp_d.timestamp) = DATE(boarding_point.timestamp) 
                      AND bp_d.status IN ('dropped', 'deboarded') 
                    ORDER BY bp_d.id DESC LIMIT 1
                ) AS dropped_timestamp
            ")
            ->from('boarding_point')
            ->join('students', 'students.id = boarding_point.studentid', 'left')
            ->join('student_session', 'student_session.student_id = students.id', 'left')
            ->join('classes', 'classes.id = student_session.class_id', 'left')
            ->join('sections', 'sections.id = student_session.section_id', 'left')
            ->join('vehicles', 'vehicles.id = boarding_point.busid', 'left')
            ->join('attenders', 'attenders.id = boarding_point.attenderid', 'left')
            ->where('boarding_point.id', $id)
            ->group_by('boarding_point.id');

            $query = $this->db->get();
            return $query->row_array();
        }

        $date = !empty($filters['date']) ? $this->db->escape_str($filters['date']) : date('Y-m-d');
        $escaped_date = $this->db->escape($date);

        $sql = "
        SELECT 
            cr.*,
            COALESCE(
                (
                    SELECT bp_p.timestamp 
                    FROM boarding_point bp_p 
                    WHERE bp_p.studentid = cr.studentid 
                      AND DATE(bp_p.timestamp) = $escaped_date 
                      AND bp_p.status = 'pending' 
                    ORDER BY bp_p.id DESC LIMIT 1
                ),
                CONCAT($escaped_date, ' 00:00:00')
            ) AS pending_timestamp,
            (
                SELECT bp_b.timestamp 
                FROM boarding_point bp_b 
                WHERE bp_b.studentid = cr.studentid 
                  AND DATE(bp_b.timestamp) = $escaped_date 
                  AND bp_b.status = 'boarded' 
                ORDER BY bp_b.id DESC LIMIT 1
            ) AS boarded_timestamp,
            (
                SELECT bp_d.timestamp 
                FROM boarding_point bp_d 
                WHERE bp_d.studentid = cr.studentid 
                  AND DATE(bp_d.timestamp) = $escaped_date 
                  AND bp_d.status IN ('dropped', 'deboarded') 
                ORDER BY bp_d.id DESC LIMIT 1
            ) AS dropped_timestamp
        FROM (
            SELECT 
                bp.id,
                bp.studentid,
                bp.busid,
                bp.attenderid,
                bp.status,
                bp.timestamp,
                s.admission_no,
                s.roll_no,
                s.firstname,
                s.middlename,
                s.lastname,
                TRIM(CONCAT_WS(' ', s.firstname, s.middlename, s.lastname)) AS student_name,
                c.class AS grade,
                sec.section AS section,
                TRIM(CONCAT_WS(' ', c.class, sec.section)) AS grade_section,
                COALESCE(NULLIF(s.current_address, ''), NULLIF(s.permanent_address, ''), s.guardian_address, '') AS address,
                s.current_address,
                s.permanent_address,
                s.guardian_address,
                s.mobileno AS student_mobileno,
                s.gender,
                s.image AS student_image,
                v.vehicle_no,
                v.vehicle_model,
                a.attender_name
            FROM boarding_point bp
            JOIN students s ON s.id = bp.studentid
            LEFT JOIN student_session ss ON ss.student_id = s.id
            LEFT JOIN classes c ON c.id = ss.class_id
            LEFT JOIN sections sec ON sec.id = ss.section_id
            LEFT JOIN vehicles v ON v.id = bp.busid
            LEFT JOIN attenders a ON a.id = bp.attenderid
            WHERE DATE(bp.timestamp) = $escaped_date

            UNION ALL

            SELECT 
                0 AS id,
                s.id AS studentid,
                COALESCE(vr.vehicle_id, 0) AS busid,
                COALESCE(vr.attender_id, 0) AS attenderid,
                'pending' AS status,
                CONCAT($escaped_date, ' 00:00:00') AS timestamp,
                s.admission_no,
                s.roll_no,
                s.firstname,
                s.middlename,
                s.lastname,
                TRIM(CONCAT_WS(' ', s.firstname, s.middlename, s.lastname)) AS student_name,
                c.class AS grade,
                sec.section AS section,
                TRIM(CONCAT_WS(' ', c.class, sec.section)) AS grade_section,
                COALESCE(NULLIF(s.current_address, ''), NULLIF(s.permanent_address, ''), s.guardian_address, '') AS address,
                s.current_address,
                s.permanent_address,
                s.guardian_address,
                s.mobileno AS student_mobileno,
                s.gender,
                s.image AS student_image,
                v.vehicle_no,
                v.vehicle_model,
                a.attender_name
            FROM students s
            JOIN student_session ss ON ss.student_id = s.id
            LEFT JOIN classes c ON c.id = ss.class_id
            LEFT JOIN sections sec ON sec.id = ss.section_id
            LEFT JOIN vehicle_routes vr ON vr.id = ss.vehroute_id
            LEFT JOIN vehicles v ON v.id = vr.vehicle_id
            LEFT JOIN attenders a ON a.id = vr.attender_id
            WHERE s.is_active = 'yes'
            AND s.id NOT IN (
                SELECT studentid FROM boarding_point WHERE DATE(timestamp) = $escaped_date
            )
        ) AS cr
        WHERE 1=1
        ";

        if (!empty($filters['status'])) {
            $sql .= " AND status = " . $this->db->escape($filters['status']);
        }

        if (!empty($filters['busid'])) {
            $sql .= " AND busid = " . intval($filters['busid']);
        }

        if (!empty($filters['attenderid'])) {
            $sql .= " AND attenderid = " . intval($filters['attenderid']);
        }

        if (!empty($filters['studentid'])) {
            $sql .= " AND studentid = " . intval($filters['studentid']);
        }

        if (!empty($filters['search'])) {
            $sc = $this->db->escape_like_str($filters['search']);
            $sql .= " AND (
                firstname LIKE '%$sc%' OR 
                lastname LIKE '%$sc%' OR 
                student_name LIKE '%$sc%' OR 
                admission_no LIKE '%$sc%' OR 
                roll_no LIKE '%$sc%' OR 
                grade LIKE '%$sc%' OR 
                section LIKE '%$sc%' OR 
                vehicle_no LIKE '%$sc%' OR 
                attender_name LIKE '%$sc%' OR 
                address LIKE '%$sc%'
            )";
        }

        $sql .= " GROUP BY studentid ORDER BY id DESC, student_name ASC";

        $query = $this->db->query($sql);
        return $query->result_array();
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
