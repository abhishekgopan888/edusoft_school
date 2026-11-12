<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Chapter_model extends MY_Model
{
    protected $current_session;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        $this->db->select('chapters.*, classes.class as class_name, sections.section as section_name, subjects.name as subject_name, subjects.code as subject_code');
        $this->db->from('chapters');
        $this->db->join('classes', 'classes.id = chapters.class_id', 'left');
        $this->db->join('sections', 'sections.id = chapters.section_id', 'left');
        $this->db->join('subjects', 'subjects.id = chapters.subject_id', 'left');
        $this->db->where('chapters.session_id', $this->current_session);

        if ($id !== null) {
            $this->db->where('chapters.id', $id);
            $query = $this->db->get();
            return $query->row();
        } else {
            $this->db->order_by('chapters.id', 'DESC');
            $query = $this->db->get();
            return $query->result();
        }
    }

    public function getByClassSectionSubject($class_id = null, $section_id = null, $subject_id = null)
    {
        $this->db->select('chapters.*, classes.class as class_name, sections.section as section_name, subjects.name as subject_name, subjects.code as subject_code');
        $this->db->from('chapters');
        $this->db->join('classes', 'classes.id = chapters.class_id', 'left');
        $this->db->join('sections', 'sections.id = chapters.section_id', 'left');
        $this->db->join('subjects', 'subjects.id = chapters.subject_id', 'left');
        $this->db->where('chapters.session_id', $this->current_session);

        if (!empty($class_id)) {
            $this->db->where('(chapters.class_id = ' . $this->db->escape($class_id) . ' OR chapters.class_id IS NULL OR chapters.class_id = 0)');
        }
        if (!empty($section_id)) {
            $this->db->where('(chapters.section_id = ' . $this->db->escape($section_id) . ' OR chapters.section_id IS NULL OR chapters.section_id = 0)');
        }
        if (!empty($subject_id)) {
            $this->db->where('chapters.subject_id', $subject_id);
        }

        $this->db->order_by('chapters.chapter_no', 'ASC');
        $query = $this->db->get();
        return $query->result();
    }

    public function add($data)
    {
        $this->db->trans_start();
        $data['session_id'] = $this->current_session;
        $this->db->insert('chapters', $data);
        $insert_id = $this->db->insert_id();
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return $insert_id;
        }
    }

    public function edit($data)
    {
        $this->db->trans_start();
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('chapters', $data);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return true;
        }
    }

    public function remove($id)
    {
        $this->db->trans_start();
        $this->db->where('id', $id);
        $this->db->delete('chapters');
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return true;
        }
    }
}
