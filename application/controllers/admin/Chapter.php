<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Chapter extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('chapter_model', 'class_model', 'section_model', 'subject_model', 'subjectgroup_model'));
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'chapters');
        $data['title'] = $this->lang->line('chapters');

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('chapter_name', $this->lang->line('chapter_name'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['classlist'] = $this->class_model->get();
            $data['subjectlist'] = $this->subject_model->get();
            $data['chapterList'] = $this->chapter_model->get();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/chapter/chapterList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $subject_id = $this->input->post('subject_id');
            $chapter_no = $this->input->post('chapter_no');
            $chapter_name = $this->input->post('chapter_name');
            $description = $this->input->post('description');

            $data_insert = array(
                'class_id' => $class_id,
                'section_id' => !empty($section_id) ? $section_id : null,
                'subject_id' => $subject_id,
                'chapter_no' => $chapter_no,
                'chapter_name' => $chapter_name,
                'description' => $description,
                'is_active' => 'yes'
            );

            $this->chapter_model->add($data_insert);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/chapter');
        }
    }

    public function edit($id)
    {
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'chapters');
        $data['title'] = $this->lang->line('edit_chapter');
        $data['id'] = $id;

        $chapter = $this->chapter_model->get($id);
        if (!$chapter) {
            redirect('admin/chapter');
        }
        $data['chapter'] = $chapter;

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('subject_id', $this->lang->line('subject'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('chapter_name', $this->lang->line('chapter_name'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['classlist'] = $this->class_model->get();
            $data['subjectlist'] = $this->subject_model->get();
            $data['chapterList'] = $this->chapter_model->get();
            $this->load->view('layout/header', $data);
            $this->load->view('admin/chapter/chapterEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');
            $subject_id = $this->input->post('subject_id');
            $chapter_no = $this->input->post('chapter_no');
            $chapter_name = $this->input->post('chapter_name');
            $description = $this->input->post('description');

            $data_update = array(
                'id' => $id,
                'class_id' => $class_id,
                'section_id' => !empty($section_id) ? $section_id : null,
                'subject_id' => $subject_id,
                'chapter_no' => $chapter_no,
                'chapter_name' => $chapter_name,
                'description' => $description
            );

            $this->chapter_model->edit($data_update);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/chapter');
        }
    }

    public function delete($id)
    {
        $this->chapter_model->remove($id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('delete_message') . '</div>');
        redirect('admin/chapter');
    }

    public function getChaptersBySubject()
    {
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $subject_id = $this->input->post('subject_id');

        $chapters = $this->chapter_model->getByClassSectionSubject($class_id, $section_id, $subject_id);
        echo json_encode($chapters);
    }
}
