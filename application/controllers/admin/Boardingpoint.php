<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Boardingpoint extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("boardingpoint_model");
        $this->load->model("student_model");
        $this->load->model("vehicle_model");
        $this->load->model("attender_model");
    }

    public function index()
    {
        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'boardingpoint/index');

        $data['boarding_list'] = $this->boardingpoint_model->get();
        $data['students']      = $this->student_model->get();
        $data['vehicles']      = $this->vehicle_model->get();
        
        if ($this->db->table_exists('attenders')) {
            $data['attenders'] = $this->db->get('attenders')->result_array();
        } else {
            $data['attenders'] = array();
        }

        $this->load->view('layout/header');
        $this->load->view('admin/boardingpoint/index', $data);
        $this->load->view('layout/footer');
    }

    public function add()
    {
        $this->form_validation->set_rules('studentid', 'Student', 'trim|required|xss_clean');
        $this->form_validation->set_rules('busid', 'Bus/Vehicle', 'trim|required|xss_clean');
        $this->form_validation->set_rules('attenderid', 'Attender', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $data['boarding_list'] = $this->boardingpoint_model->get();
            $data['students']      = $this->student_model->get();
            $data['vehicles']      = $this->vehicle_model->get();
            if ($this->db->table_exists('attenders')) {
                $data['attenders'] = $this->db->get('attenders')->result_array();
            } else {
                $data['attenders'] = array();
            }

            $this->load->view('layout/header');
            $this->load->view('admin/boardingpoint/index', $data);
            $this->load->view('layout/footer');
        } else {
            $id = $this->input->post('id');
            $data = array(
                'studentid'  => $this->input->post('studentid'),
                'busid'      => $this->input->post('busid'),
                'attenderid' => $this->input->post('attenderid'),
                'status'     => $this->input->post('status') ? $this->input->post('status') : 'boarded'
            );

            if (!empty($id)) {
                $data['id'] = $id;
            }

            $this->boardingpoint_model->add($data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Boarding record saved successfully</div>');
            redirect('admin/boardingpoint/index');
        }
    }

    public function update_status()
    {
        $id     = $this->input->post('id');
        $status = $this->input->post('status');

        if (!empty($id) && !empty($status)) {
            $data = array(
                'id'     => $id,
                'status' => $status
            );
            $this->boardingpoint_model->add($data);
            echo json_encode(array('status' => 'success', 'message' => 'Status updated successfully'));
        } else {
            echo json_encode(array('status' => 'fail', 'message' => 'Invalid parameters'));
        }
    }

    public function delete($id)
    {
        if (!empty($id)) {
            $this->boardingpoint_model->remove($id);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Boarding record deleted successfully</div>');
        }
        redirect('admin/boardingpoint/index');
    }
}
