<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Subject extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('file');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Academics');
        $this->session->set_userdata('sub_menu', 'Academics/subject');
        $data['title']         = 'Add subject';
        $subject_result        = $this->subject_model->get();
        $data['subjectlist']   = $subject_result;
        $data['subject_types'] = $this->customlib->subjectType();
        $this->form_validation->set_rules('name', $this->lang->line('subject_name'), 'trim|required|xss_clean|callback__check_name_exists');
        $this->form_validation->set_rules('type', $this->lang->line('type'), 'trim|required|xss_clean');
        if ($this->input->post('code')) {
            $this->form_validation->set_rules('code', $this->lang->line('code'), 'trim|required|callback__check_code_exists');
        }
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/subject/subjectList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'code' => $this->input->post('code'),
                'type' => $this->input->post('type'),
            );
            $this->subject_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/subject/index');
        }
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_view')) {
            access_denied();
        }
        $data['title']   = 'Subject List';
        $subject         = $this->subject_model->get($id);
        $data['subject'] = $subject;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/subject/subjectShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Subject List';
        $this->subject_model->remove($id);
        redirect('admin/subject/index');
    }

    public function _check_name_exists()
    {
        $data['name'] = $this->security->xss_clean($this->input->post('name'));
        if ($this->subject_model->check_data_exists($data)) {
            $this->form_validation->set_message('_check_name_exists', $this->lang->line('name_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function _check_code_exists()
    {
        $data['code'] = $this->security->xss_clean($this->input->post('code'));
        if ($this->subject_model->check_code_exists($data)) {
            $this->form_validation->set_message('_check_code_exists', $this->lang->line('code_already_exists'));
            return false;
        } else {
            return true;
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('subject', 'can_edit')) {
            access_denied();
        }
        $subject_result = $this->subject_model->get();
        $data['subjectlist'] = $subject_result;
        $data['title'] = 'Edit Subject';
        $data['id'] = $id;
        $subject = $this->subject_model->get($id);
        $data['subject'] = $subject;
        $admin = $this->session->userdata('admin');
        $centre_id = isset($admin['centre_id']) ? $admin['centre_id'] : 0;
        $data['centre_id'] = $centre_id;
        $this->form_validation->set_rules('name', 'Subject', 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/subject/subjectEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $subject_name = $this->input->post('name');
            $subject_code = $this->input->post('code');

            $update_data = array(
                'id' => $id,
                'name' => $subject_name,
                'code' => $subject_code,
                'theory' => $this->input->post('theory'),
                'viva' => $this->input->post('viva'),
                'practical' => $this->input->post('practical'),
                'lab' => $this->input->post('lab'),
                'clinical' => $this->input->post('clinical'),
                'color' => $this->input->post('color'),
                'cocurricular' => $this->input->post('cocurricular')
            );

            // Handle new PDF upload if provided
            if (isset($_FILES['syllabus_file']) && !empty($_FILES['syllabus_file']['name'])) {
                $config = array(
                    'upload_path' => "./uploads/subject_syllabus/",
                    'allowed_types' => "pdf",
                    'file_name' => time() . "_" . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['syllabus_file']['name']),
                    'overwrite' => false,
                    'max_size' => "20480"
                );
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if ($this->upload->do_upload('syllabus_file')) {
                    $upload_data = $this->upload->data();
                    $update_data['syllabus_file'] = $upload_data['file_name'];
                    $full_file_path = $upload_data['full_path'];

                    // Process with AI Developer API
                    $ai_response = $this->_process_syllabus_ai($full_file_path, $subject_name, $subject_code);
                    $update_data['syllabus_content'] = $ai_response['syllabus_content'];
                    $update_data['ai_extracted_data'] = $ai_response['ai_extracted_data'];

                    // Insert any extracted chapters if present
                    if (!empty($ai_response['chapters'])) {
                        $current_session = $this->setting_model->getCurrentSession();
                        foreach ($ai_response['chapters'] as $idx => $ch) {
                            $chapter_data = array(
                                'session_id' => $current_session,
                                'subject_id' => $id,
                                'class_id' => 0,
                                'chapter_no' => isset($ch['chapter_no']) ? $ch['chapter_no'] : ($idx + 1),
                                'chapter_name' => isset($ch['chapter_name']) ? $ch['chapter_name'] : 'Chapter ' . ($idx + 1),
                                'description' => isset($ch['description']) ? $ch['description'] : '',
                                'is_active' => 'yes'
                            );
                            $this->db->insert('chapters', $chapter_data);
                    // Also populate subject_chapters table if exists
                    if ($this->db->table_exists('subject_chapters')) {
                        $this->db->insert('subject_chapters', $chapter_data);
                    }
                        }
                    }
                }
            }

            $this->subject_model->add($update_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Subject updated successfully</div>');
            redirect('admin/subject/index');
        }
    }

    public function getSubjctByClassandSection()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $date       = $this->teachersubject_model->getSubjectByClsandSection($class_id, $section_id);
        echo json_encode($data);
    }


        private function _process_syllabus_ai($file_path, $subject_name, $subject_code)
    {
        $ai_url = 'http://43.205.138.36:8002/api/extract-syllabus';
        $result = array(
            'syllabus_content' => '',
            'ai_extracted_data' => '',
            'chapters' => array()
        );

        if (!file_exists($file_path)) {
            return $result;
        }

        // Try AI Developer Service via cURL multipart POST
        if (function_exists('curl_init')) {
            $ch = curl_init();
            $cfile = function_exists('curl_file_create') 
                ? curl_file_create($file_path, 'application/pdf', basename($file_path)) 
                : '@' . realpath($file_path);

            $post_fields = array(
                'file' => $cfile,
                'subject_name' => $subject_name,
                'subject_code' => $subject_code
            );

            curl_setopt($ch, CURLOPT_URL, $ai_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if (!empty($response)) {
                $decoded = json_decode($response, true);
                if (is_array($decoded)) {
                    if (isset($decoded['status']) && $decoded['status'] === 'success') {
                        $result['syllabus_content'] = isset($decoded['syllabus_content']) ? $decoded['syllabus_content'] : '';
                        $result['ai_extracted_data'] = $response;
                        $result['chapters'] = isset($decoded['chapters']) && is_array($decoded['chapters']) ? $decoded['chapters'] : array();
                        return $result;
                    }
                }
            }
        }

        // Fallback / standard extraction: extract text from PDF
        $file_content = @file_get_contents($file_path);
        $extracted_text = '';
        if (!empty($file_content)) {
            if (preg_match_all('/\((.*?)\)Tj/s', $file_content, $matches)) {
                $extracted_text = implode(' ', $matches[1]);
            } elseif (preg_match_all('/\[(.*?)\]TJ/s', $file_content, $matches)) {
                $extracted_text = implode(' ', $matches[1]);
            }
        }

        $result['syllabus_content'] = !empty($extracted_text) ? substr(strip_tags($extracted_text), 0, 5000) : "Syllabus uploaded for " . $subject_name;
        $result['ai_extracted_data'] = json_encode(array(
            'status' => 'uploaded',
            'file_name' => basename($file_path),
            'subject' => $subject_name,
            'subject_code' => $subject_code,
            'processed_at' => date('Y-m-d H:i:s')
        ));

        return $result;
    }

}