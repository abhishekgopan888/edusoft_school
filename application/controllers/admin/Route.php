<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Route extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array("classteacher_model", "vehicle_model", "vehroute_model"));
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('routes', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'route/index');
        $listroute                  = $this->route_model->listroute();
        $data['listroute']          = $listroute;
        $data['vehiclelist']        = $this->vehicle_model->get();
        $data['pickup_point_list']  = $this->route_model->listpickup_point();
        $this->load->view('layout/header');
        $this->load->view('admin/route/createroute', $data);
        $this->load->view('layout/footer');
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('routes', 'can_add')) {
            access_denied();
        }
        $data['title'] = 'Add Route';
        $this->form_validation->set_rules('route_title', $this->lang->line('route_title'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $listroute                  = $this->route_model->listroute();
            $data['listroute']          = $listroute;
            $data['vehiclelist']        = $this->vehicle_model->get();
            $data['pickup_point_list']  = $this->route_model->listpickup_point();
            $this->load->view('layout/header');
            $this->load->view('admin/route/createroute', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'route_title'   => $this->input->post('route_title'),
                'no_of_vehicle' => $this->input->post('no_of_vehicle'),
            );
            $insert_id = $this->route_model->add($data);

            $vehicle = $this->input->post('vehicle');
            if (!empty($vehicle) && !empty($insert_id)) {
                $vehicle_batch_array = array();
                foreach ((array)$vehicle as $vec_key => $vec_value) {
                    if (!empty($vec_value)) {
                        $vehicle_batch_array[] = array(
                            'route_id'   => $insert_id,
                            'vehicle_id' => $vec_value,
                        );
                    }
                }
                if (!empty($vehicle_batch_array)) {
                    $this->vehroute_model->add($vehicle_batch_array);
                }
            }

            $pickup_point = $this->input->post('pickup_point');
            if (!empty($pickup_point) && !empty($insert_id)) {
                $current_session = $this->setting_model->getCurrentSession();
                foreach ((array)$pickup_point as $pp_val) {
                    if (!empty($pp_val)) {
                        $this->db->insert('route_pickup_point', array(
                            'transport_route_id' => $insert_id,
                            'pickup_point_id'    => $pp_val,
                            'session_id'         => $current_session,
                        ));
                    }
                }
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/route/index');
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('routes', 'can_edit')) {
            access_denied();
        }
        $data['title']     = 'Add Route';
        $data['id']        = $id;
        $editroute         = $this->route_model->get($id);
        $data['editroute'] = $editroute;

        $assigned_vehicles = $this->vehroute_model->getVechileByRoute($id);
        $selected_vehicles = array();
        if (!empty($assigned_vehicles)) {
            foreach ($assigned_vehicles as $av) {
                $selected_vehicles[] = $av->id;
            }
        }
        $data['selected_vehicles'] = $selected_vehicles;

        $assigned_pickup_points = $this->db->select('pickup_point_id')->where('transport_route_id', $id)->get('route_pickup_point')->result_array();
        $selected_pickup_points = array();
        if (!empty($assigned_pickup_points)) {
            foreach ($assigned_pickup_points as $app) {
                $selected_pickup_points[] = $app['pickup_point_id'];
            }
        }
        $data['selected_pickup_points'] = $selected_pickup_points;
        $data['pickup_point_list']      = $this->route_model->listpickup_point();

        $this->form_validation->set_rules('route_title', $this->lang->line('route_title'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $listroute                  = $this->route_model->listroute();
            $data['listroute']          = $listroute;
            $data['vehiclelist']        = $this->vehicle_model->get();
            $data['pickup_point_list']  = $this->route_model->listpickup_point();
            $this->load->view('layout/header');
            $this->load->view('admin/route/editroute', $data);
            $this->load->view('layout/footer');
        } else {
            $data = array(
                'id'            => $this->input->post('id'),
                'route_title'   => $this->input->post('route_title'),
                'no_of_vehicle' => $this->input->post('no_of_vehicle'),
            );
            $this->route_model->add($data);

            $vehicle = $this->input->post('vehicle');
            $this->vehroute_model->removeByroute($id);
            if (!empty($vehicle)) {
                $vehicle_batch_array = array();
                foreach ((array)$vehicle as $vec_key => $vec_value) {
                    if (!empty($vec_value)) {
                        $vehicle_batch_array[] = array(
                            'route_id'   => $id,
                            'vehicle_id' => $vec_value,
                        );
                    }
                }
                if (!empty($vehicle_batch_array)) {
                    $this->vehroute_model->add($vehicle_batch_array);
                }
            }

            $pickup_point = $this->input->post('pickup_point');
            if ($pickup_point !== null) {
                $this->db->where('transport_route_id', $id)->delete('route_pickup_point');
                if (!empty($pickup_point)) {
                    $current_session = $this->setting_model->getCurrentSession();
                    foreach ((array)$pickup_point as $pp_val) {
                        if (!empty($pp_val)) {
                            $this->db->insert('route_pickup_point', array(
                                'transport_route_id' => $id,
                                'pickup_point_id'    => $pp_val,
                                'session_id'         => $current_session,
                            ));
                        }
                    }
                }
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/route/index');
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('routes', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->db->where('transport_route_id', $id)->delete('route_pickup_point');
        $this->route_model->remove($id);
        redirect('admin/route/index');
    }

    public function studenttransportdetails()
    {
        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'reports/studenttransportdetails');
        $data['title']     = 'Student Hostel Details';
        $class             = $this->class_model->get();
        $data['classlist'] = $class;
        $userdata          = $this->customlib->getUserData();
        $carray            = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $vehroute_result      = $this->route_model->get();
        $data['vehroutelist'] = $vehroute_result;
        $section_id           = $this->input->post("section_id");
        $class_id             = $this->input->post("class_id");
        $transport_route_id   = $this->input->post("transport_route_id");
        $pickup_point_id      = $this->input->post("pickup_point_id");
        $vehicle_id           = $this->input->post("vehicle_id");
 
        if (isset($_POST["search"])) {
            $details            = $this->route_model->searchTransportDetails($section_id, $class_id, $transport_route_id, $pickup_point_id, $vehicle_id);
            $data["resultlist"] = $details;
        }

        $data['sch_setting'] = $this->sch_setting_detail;
        $this->load->view("layout/header", $data);
        $this->load->view("admin/route/studentroutedetails", $data);
        $this->load->view("layout/footer", $data);
    }

    public function pickup_point()
    {
        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'route/index');
        $listpickup_point         = $this->route_model->listpickup_point();
        $data['listpickup_point'] = $listpickup_point;
        $this->load->view('layout/header');
        $this->load->view('admin/route/pickup_point', $data);
        $this->load->view('layout/footer');
    }

}
