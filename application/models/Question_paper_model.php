<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Question_paper_model extends MY_Model
{
    protected $current_session;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function get($id = null)
    {
        $this->db->select('generated_question_papers.*, classes.class as class_name, sections.section as section_name, subjects.name as subject_name, subjects.code as subject_code');
        $this->db->from('generated_question_papers');
        $this->db->join('classes', 'classes.id = generated_question_papers.class_id', 'left');
        $this->db->join('sections', 'sections.id = generated_question_papers.section_id', 'left');
        $this->db->join('subjects', 'subjects.id = generated_question_papers.subject_id', 'left');
        if ($id !== null) {
            $this->db->where('generated_question_papers.id', $id);
            $query = $this->db->get();
            $paper = $query->row();
            if ($paper) {
                $paper->questions = $this->get_items($paper->id);
            }
            return $paper;
        } else {
            $this->db->where('generated_question_papers.session_id', $this->current_session);
            $this->db->order_by('generated_question_papers.id', 'DESC');
            $query = $this->db->get();
            $result = $query->result();
            return $result;
        }
    }

    public function get_items($question_paper_id)
    {
        $this->db->where('question_paper_id', $question_paper_id);
        $this->db->order_by('question_no', 'ASC');
        $query = $this->db->get('generated_question_paper_items');
        return $query->result();
    }

    public function add($paper_data, $questions_array = array())
    {
        $this->db->trans_start();
        $paper_data['session_id'] = $this->current_session;
        if (!isset($paper_data['created_by'])) {
            $admin = $this->session->userdata('admin');
            $paper_data['created_by'] = isset($admin['id']) ? $admin['id'] : 1;
        }

        $this->db->insert('generated_question_papers', $paper_data);
        $question_paper_id = $this->db->insert_id();

        if (!empty($questions_array)) {
            $insert_items = array();
            $q_no = 1;
            foreach ($questions_array as $q) {
                $options_json = null;
                if (!empty($q['options'])) {
                    $options_json = is_array($q['options']) ? json_encode($q['options']) : $q['options'];
                }

                $step_marking_json = null;
                if (!empty($q['step_marking'])) {
                    $step_marking_json = is_array($q['step_marking']) ? json_encode($q['step_marking']) : $q['step_marking'];
                }

                $insert_items[] = array(
                    'question_paper_id' => $question_paper_id,
                    'question_no' => isset($q['question_no']) ? $q['question_no'] : (isset($q['id']) ? $q['id'] : $q_no++),
                    'chapter_name' => isset($q['chapter']) ? $q['chapter'] : (isset($q['chapter_name']) ? $q['chapter_name'] : null),
                    'question_type' => isset($q['question_type']) ? $q['question_type'] : (isset($q['type']) ? $q['type'] : 'Descriptive'),
                    'question_text' => isset($q['question_text']) ? $q['question_text'] : (isset($q['question']) ? $q['question'] : ''),
                    'options_json' => $options_json,
                    'marks' => isset($q['marks']) ? floatval($q['marks']) : 1.00,
                    'answer_key' => isset($q['answer_key']) ? $q['answer_key'] : (isset($q['correct_answer']) ? $q['correct_answer'] : ''),
                    'explanation' => isset($q['explanation']) ? $q['explanation'] : null,
                    'step_marking_json' => $step_marking_json,
                    'difficulty' => isset($q['difficulty']) ? $q['difficulty'] : null
                );
            }
            $this->db->insert_batch('generated_question_paper_items', $insert_items);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return $question_paper_id;
        }
    }

    public function delete($id)
    {
        $this->db->trans_start();
        $this->db->where('question_paper_id', $id);
        $this->db->delete('generated_question_paper_items');

        $this->db->where('id', $id);
        $this->db->delete('generated_question_papers');
        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        } else {
            return true;
        }
    }
}
