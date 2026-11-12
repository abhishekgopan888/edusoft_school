<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Certificate extends Student_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Certificate_model');
        $this->load->model('Generatecertificate_model');
        $this->load->model('student_model');
        $this->load->library('media_storage');
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'certificate');
        $this->session->set_userdata('sub_menu', 'user/certificate');
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_id            = $this->customlib->getStudentSessionUserID();
        $student               = $this->student_model->get($student_id);

        $data['student']         = $student;
        $data['certificateList'] = $this->Certificate_model->getstudentcertificate();

        $this->load->view('layout/student/header', $data);
        $this->load->view('user/certificate/index', $data);
        $this->load->view('layout/student/footer', $data);
    }

    public function generate()
    {
        $this->session->set_userdata('top_menu', 'certificate');
        $this->session->set_userdata('sub_menu', 'user/certificate/generate');
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_id            = $this->customlib->getStudentSessionUserID();

        $student_detail = null;
        if (!empty($student_current_class) && isset($student_current_class->student_session_id)) {
            $student_detail = $this->student_model->getByStudentSession($student_current_class->student_session_id);
        }
        if (empty($student_detail)) {
            $student_detail = $this->student_model->get($student_id);
        }

        $data['student']         = (object) $student_detail;
        $data['certificateList'] = $this->Certificate_model->getstudentcertificate();

        $this->load->view('layout/student/header', $data);
        $this->load->view('user/certificate/generate', $data);
        $this->load->view('layout/student/footer', $data);
    }

    public function printcertificate()
    {
        $certificate_id        = $this->input->post('certificate_id');
        $student_current_class = $this->customlib->getStudentCurrentClsSection();
        $student_id            = $this->customlib->getStudentSessionUserID();

        $certificate               = $this->Certificate_model->get($certificate_id);
        $data['certificate']       = $certificate;
        $certificateResult         = $this->Generatecertificate_model->getcertificatebyid($certificate_id);
        $data['certificateResult'] = $certificateResult;

        $student_details = array();
        if (!empty($student_current_class) && isset($student_current_class->student_session_id)) {
            $std_data = $this->student_model->getByStudentSession($student_current_class->student_session_id);
            if (!empty($std_data)) {
                $std_obj = (object) $std_data;
                $std_obj->name = trim(($std_obj->firstname ?? '') . ' ' . ($std_obj->middlename ?? '') . ' ' . ($std_obj->lastname ?? ''));
                $student_details[] = $std_obj;
            }
        }

        if (empty($student_details)) {
            $std_data = $this->student_model->get($student_id);
            if (!empty($std_data)) {
                $std_obj = (object) $std_data;
                $std_obj->name = trim(($std_obj->firstname ?? '') . ' ' . ($std_obj->middlename ?? '') . ' ' . ($std_obj->lastname ?? ''));
                $student_details[] = $std_obj;
            }
        }

        $data['students'] = $student_details;

        $certificates = $this->load->view('admin/certificate/printcertificate', $data, true);
        echo $certificates;
    }

    public function update_student_details()
    {
        $student_id = $this->customlib->getStudentSessionUserID();
        if (empty($student_id)) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
            return;
        }

        $dob_val = $this->input->post('dob');
        $dob_formatted = null;
        if (!empty($dob_val)) {
            $dob_formatted = date('Y-m-d', strtotime($dob_val));
        }

        $update_data = [
            'firstname'         => trim($this->input->post('firstname')),
            'middlename'        => trim($this->input->post('middlename')),
            'lastname'          => trim($this->input->post('lastname')),
            'gender'            => $this->input->post('gender'),
            'blood_group'       => $this->input->post('blood_group'),
            'religion'          => trim($this->input->post('religion')),
            'cast'              => trim($this->input->post('cast')),
            'adhar_no'          => trim($this->input->post('adhar_no')),
            'mobileno'          => trim($this->input->post('mobileno')),
            'email'             => trim($this->input->post('email')),
            'father_name'       => trim($this->input->post('father_name')),
            'father_phone'      => trim($this->input->post('father_phone')),
            'mother_name'       => trim($this->input->post('mother_name')),
            'mother_phone'      => trim($this->input->post('mother_phone')),
            'guardian_name'     => trim($this->input->post('guardian_name')),
            'guardian_phone'    => trim($this->input->post('guardian_phone')),
            'guardian_relation' => trim($this->input->post('guardian_relation')),
            'current_address'   => trim($this->input->post('current_address')),
            'permanent_address' => trim($this->input->post('permanent_address')),
        ];

        if ($dob_formatted) {
            $update_data['dob'] = $dob_formatted;
        }

        // Clean out nulls or unposted fields
        $update_data = array_filter($update_data, function ($v) {
            return $v !== null && $v !== '';
        });

        if (!empty($update_data)) {
            $this->db->where('id', $student_id);
            $this->db->update('students', $update_data);
        }

        $this->output->set_content_type('application/json')
            ->set_output(json_encode(['status' => 'success', 'message' => 'Student details updated successfully']));
    }
}
