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
        $this->form_validation->set_rules('route_title', $this->lang->line('route_title'), 'trim|required|xss_clean|callback_check_route_exists');
        if ($this->form_validation->run() == false) {
            $listroute                  = $this->route_model->listroute();
            $data['listroute']          = $listroute;
            $data['vehiclelist']        = $this->vehicle_model->get();
            $data['pickup_point_list']  = $this->route_model->listpickup_point();
            $this->load->view('layout/header');
            $this->load->view('admin/route/createroute', $data);
            $this->load->view('layout/footer');
        } else {
            $start_pickup_point_id = $this->input->post('start_pickup_point_id');
            $end_drop_point_id     = $this->input->post('end_drop_point_id');
            $between_stops         = $this->input->post('between_stops');
            $legacy_pickup_point   = $this->input->post('pickup_point');

            $all_stops = [];
            if (!empty($start_pickup_point_id)) {
                $all_stops[] = ['id' => (int)$start_pickup_point_id, 'type' => 'pickup'];
            }
            if (!empty($between_stops) && is_array($between_stops)) {
                foreach ($between_stops as $bs_id) {
                    if (!empty($bs_id)) {
                        $all_stops[] = ['id' => (int)$bs_id, 'type' => 'stop'];
                    }
                }
            }
            if (!empty($end_drop_point_id)) {
                $all_stops[] = ['id' => (int)$end_drop_point_id, 'type' => 'drop'];
            }

            if (empty($all_stops) && !empty($legacy_pickup_point) && is_array($legacy_pickup_point)) {
                $count_leg = count($legacy_pickup_point);
                foreach ($legacy_pickup_point as $idx => $leg_id) {
                    if (!empty($leg_id)) {
                        $type = ($idx === 0) ? 'pickup' : (($idx === $count_leg - 1 && $count_leg > 1) ? 'drop' : 'stop');
                        $all_stops[] = ['id' => (int)$leg_id, 'type' => $type];
                    }
                }
            }

            $resolved_pickup_id = !empty($start_pickup_point_id) ? (int)$start_pickup_point_id : (!empty($all_stops[0]) ? (int)$all_stops[0]['id'] : null);
            $resolved_drop_id   = !empty($end_drop_point_id) ? (int)$end_drop_point_id : (!empty($all_stops) && count($all_stops) > 1 ? (int)$all_stops[count($all_stops) - 1]['id'] : null);

            $data = array(
                'route_title'   => $this->input->post('route_title'),
                'no_of_vehicle' => $this->input->post('no_of_vehicle'),
            );
            if ($this->db->field_exists('pickup_point_id', 'transport_route')) {
                $data['pickup_point_id'] = $resolved_pickup_id;
            }
            if ($this->db->field_exists('drop_point_id', 'transport_route')) {
                $data['drop_point_id'] = $resolved_drop_id;
            }
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

            if (!empty($all_stops) && !empty($insert_id)) {
                $current_session = $this->setting_model->getCurrentSession();
                $order_num = 1;
                $has_order = $this->db->field_exists('order_number', 'route_pickup_point');
                $has_type  = $this->db->field_exists('point_type', 'route_pickup_point');

                foreach ($all_stops as $stop_item) {
                    $insert_stop = array(
                        'transport_route_id' => $insert_id,
                        'pickup_point_id'    => $stop_item['id'],
                        'session_id'         => $current_session,
                    );
                    if ($has_order) {
                        $insert_stop['order_number'] = $order_num++;
                    }
                    if ($has_type) {
                        $insert_stop['point_type'] = $stop_item['type'];
                    }
                    $this->db->insert('route_pickup_point', $insert_stop);
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

        $has_order = $this->db->field_exists('order_number', 'route_pickup_point');
        $has_type  = $this->db->field_exists('point_type', 'route_pickup_point');

        $select_fields = 'pickup_point_id';
        if ($has_type) {
            $select_fields .= ', point_type';
        }
        if ($has_order) {
            $select_fields .= ', order_number';
        }

        $this->db->select($select_fields)->where('transport_route_id', $id);
        if ($has_order) {
            $this->db->order_by('order_number', 'ASC');
        }
        $assigned_stops = $this->db->order_by('id', 'ASC')->get('route_pickup_point')->result_array();

        $selected_start_pickup = !empty($editroute['pickup_point_id']) ? $editroute['pickup_point_id'] : null;
        $selected_end_drop     = !empty($editroute['drop_point_id']) ? $editroute['drop_point_id'] : null;
        $selected_between_stops = [];
        $selected_pickup_points = [];

        if (!empty($assigned_stops)) {
            $total_stops = count($assigned_stops);
            if (empty($selected_start_pickup) && isset($assigned_stops[0])) {
                $selected_start_pickup = $assigned_stops[0]['pickup_point_id'];
            }
            if (empty($selected_end_drop) && $total_stops > 1) {
                $selected_end_drop = $assigned_stops[$total_stops - 1]['pickup_point_id'];
            }
            foreach ($assigned_stops as $idx => $st) {
                $selected_pickup_points[] = $st['pickup_point_id'];
                $is_stop = (isset($st['point_type']) && $st['point_type'] === 'stop');
                $is_mid  = ($total_stops > 2 && $idx > 0 && $idx < $total_stops - 1);
                if ($is_stop || ($is_mid && !isset($st['point_type']))) {
                    $selected_between_stops[] = $st['pickup_point_id'];
                }
            }
        }

        $data['selected_start_pickup']  = $selected_start_pickup;
        $data['selected_end_drop']      = $selected_end_drop;
        $data['selected_between_stops'] = $selected_between_stops;
        $data['selected_pickup_points'] = $selected_pickup_points;
        $data['pickup_point_list']      = $this->route_model->listpickup_point();

        $this->form_validation->set_rules('route_title', $this->lang->line('route_title'), 'trim|required|xss_clean|callback_check_route_exists[' . $id . ']');
        if ($this->form_validation->run() == false) {
            $listroute                  = $this->route_model->listroute();
            $data['listroute']          = $listroute;
            $data['vehiclelist']        = $this->vehicle_model->get();
            $this->load->view('layout/header');
            $this->load->view('admin/route/editroute', $data);
            $this->load->view('layout/footer');
        } else {
            $start_pickup_point_id = $this->input->post('start_pickup_point_id');
            $end_drop_point_id     = $this->input->post('end_drop_point_id');
            $between_stops         = $this->input->post('between_stops');
            $legacy_pickup_point   = $this->input->post('pickup_point');

            $all_stops = [];
            if (!empty($start_pickup_point_id)) {
                $all_stops[] = ['id' => (int)$start_pickup_point_id, 'type' => 'pickup'];
            }
            if (!empty($between_stops) && is_array($between_stops)) {
                foreach ($between_stops as $bs_id) {
                    if (!empty($bs_id)) {
                        $all_stops[] = ['id' => (int)$bs_id, 'type' => 'stop'];
                    }
                }
            }
            if (!empty($end_drop_point_id)) {
                $all_stops[] = ['id' => (int)$end_drop_point_id, 'type' => 'drop'];
            }

            if (empty($all_stops) && !empty($legacy_pickup_point) && is_array($legacy_pickup_point)) {
                $count_leg = count($legacy_pickup_point);
                foreach ($legacy_pickup_point as $idx => $leg_id) {
                    if (!empty($leg_id)) {
                        $type = ($idx === 0) ? 'pickup' : (($idx === $count_leg - 1 && $count_leg > 1) ? 'drop' : 'stop');
                        $all_stops[] = ['id' => (int)$leg_id, 'type' => $type];
                    }
                }
            }

            $resolved_pickup_id = !empty($start_pickup_point_id) ? (int)$start_pickup_point_id : (!empty($all_stops[0]) ? (int)$all_stops[0]['id'] : null);
            $resolved_drop_id   = !empty($end_drop_point_id) ? (int)$end_drop_point_id : (!empty($all_stops) && count($all_stops) > 1 ? (int)$all_stops[count($all_stops) - 1]['id'] : null);

            $data = array(
                'id'            => $this->input->post('id'),
                'route_title'   => $this->input->post('route_title'),
                'no_of_vehicle' => $this->input->post('no_of_vehicle'),
            );
            if ($this->db->field_exists('pickup_point_id', 'transport_route')) {
                $data['pickup_point_id'] = $resolved_pickup_id;
            }
            if ($this->db->field_exists('drop_point_id', 'transport_route')) {
                $data['drop_point_id'] = $resolved_drop_id;
            }
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

            $this->db->where('transport_route_id', $id)->delete('route_pickup_point');
            if (!empty($all_stops)) {
                $current_session = $this->setting_model->getCurrentSession();
                $order_num = 1;
                $has_order = $this->db->field_exists('order_number', 'route_pickup_point');
                $has_type  = $this->db->field_exists('point_type', 'route_pickup_point');

                foreach ($all_stops as $stop_item) {
                    $insert_stop = array(
                        'transport_route_id' => $id,
                        'pickup_point_id'    => $stop_item['id'],
                        'session_id'         => $current_session,
                    );
                    if ($has_order) {
                        $insert_stop['order_number'] = $order_num++;
                    }
                    if ($has_type) {
                        $insert_stop['point_type'] = $stop_item['type'];
                    }
                    $this->db->insert('route_pickup_point', $insert_stop);
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

    public function check_route_exists($title, $id = null)
    {
        if (empty($id)) {
            $id = $this->input->post('id');
        }
        if ($this->route_model->check_route_exists($title, $id)) {
            $this->form_validation->set_message('check_route_exists', 'The %s already exists. Please choose a unique route name.');
            return false;
        }
        return true;
    }

}
