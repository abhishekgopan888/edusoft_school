<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vehicle extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->library('SaasValidation');
        $this->load->model('driver_model');
        $this->load->model('route_model');
        $this->load->model('maintenance_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'vehicle/index');
        $data['title']       = 'Add Vehicle';
        $listVehicle         = $this->vehicle_model->getVehicleWithDetails();
        $data['listVehicle'] = $listVehicle;
        $data['branchlist']  = $this->vehicle_model->getBranches();
        $data['driverlist']  = $this->driver_model->get();
        $this->load->view('layout/header');
        $this->load->view('admin/vehicle/index', $data);
        $this->load->view('layout/footer');
    }

    public function validateCanUploadFile($str, $params_string)
    {
        $params_array = array_map('trim', explode(',', $params_string));
        return $this->saasvalidation->validateCanUploadFile($str, $params_array);
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('vehicle_no', $this->lang->line('vehicle_number'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('documents', $this->lang->line('documents'), 'callback_handle_upload');

        $storage_array = "documents";
        $this->form_validation->set_rules('documents', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");
    
        if ($this->form_validation->run() == false) {
            $msg = array(
                'vehicle_no' => form_error('vehicle_no'),
                'documents'  => form_error('documents'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {   

         try {
                $total_documents_failed_size = 0;
                $storage_array = ['documents'];
                $this->saasvalidation->updateStorageLimit('storage', $storage_array);
                $documents = $this->media_storage->fileupload("documents", "./uploads/vehicle_documents/");
                if (IsNullOrEmptyString($documents)) {
                    $total_documents_failed_size += $this->media_storage->getTmpFileSize('documents');
                }

                if ($total_documents_failed_size > 0) {
                    $this->saasvalidation->deleteResouceQuota('storage', $total_documents_failed_size);
                }         
            
            $data = array(
                'vehicle_no'           => $this->input->post('vehicle_no'),
                'vehicle_model'        => $this->input->post('vehicle_model'),
                'note'                 => $this->input->post('note'),
                'registration_number'  => $this->input->post('registration_number'),
                'chasis_number'        => $this->input->post('chasis_number'),
                'max_seating_capacity' => $this->input->post('max_seating_capacity'),
                'manufacture_year'     => $this->input->post('manufacture_year'),
                'status'               => $this->input->post('status'),
                'documents'            => $documents,
            );
           
            $this->vehicle_model->add($data);

            $msg   = $this->lang->line('success_message');
            $array = array('status' => 'success', 'error' => '', 'message' => $msg);

        } catch (Exception $e) {
                $array = array('status' => 'fail', 'error' => $e->getMessage(), 'message' => '');
        }  
        }
        echo json_encode($array);
    }

    public function getsinglevehicledata()
    {
        $vehicleid           = $this->input->post('vehicleid');
        $data['editvehicle'] = $this->vehicle_model->get($vehicleid);
        $page                = $this->load->view('admin/vehicle/edit', $data, true);
        echo json_encode(array('page' => $page));
    }

    public function edit()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_edit')) {
            access_denied();
        }
        
        $this->form_validation->set_rules('vehicle_no', $this->lang->line('vehicle_number'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('documents', $this->lang->line('documents'), 'callback_handle_upload');
        $storage_array = "documents";
        $this->form_validation->set_rules('documents', $this->lang->line('storage'), "callback_validateCanUploadFile[$storage_array]");

        $id        =    $this->input->post('id');
        $vehicle   =    $this->vehicle_model->get($id);       
        
        if ($this->form_validation->run() == false) {
            $msg = array(
                'vehicle_no' => form_error('vehicle_no'),
                'documents'  => form_error('documents'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {    

        try {
            $prev_file_size = 0;
            $total_image_upload_size = 0;       

            $data = array(
                'id'                   => $this->input->post('id'),
                'vehicle_no'           => $this->input->post('vehicle_no'),
                'vehicle_model'        => $this->input->post('vehicle_model'),
                'note'                 => $this->input->post('note'),
                'registration_number'  => $this->input->post('registration_number'),
                'chasis_number'        => $this->input->post('chasis_number'),
                'max_seating_capacity' => $this->input->post('max_seating_capacity'),
                'manufacture_year'     => $this->input->post('manufacture_year'),        
                'status'               => $this->input->post('status'),
            );            
            
            if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                $prev_file_size = $this->media_storage->getUploadedFileSize($vehicle->documents, 'uploads/vehicle_documents');
                $doc_name       = $this->media_storage->fileupload("documents", "./uploads/vehicle_documents/");
                 if (!IsNullOrEmptyString($doc_name)) {
                    $total_image_upload_size += $this->media_storage->getTmpFileSize('documents');
                }
            } else {
                $doc_name = $vehicle->documents;
            }

            $data['documents'] = $doc_name;
            if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                if ($vehicle->documents != '') {
                    $this->media_storage->filedelete($vehicle->documents, "uploads/vehicle_documents");
                }
            }

            if ($prev_file_size > $total_image_upload_size) {
                $size_difference = $prev_file_size - $total_image_upload_size;
                $this->saasvalidation->deleteResouceQuota('storage', $size_difference);
            } elseif ($prev_file_size < $total_image_upload_size) {
                $size_difference = $total_image_upload_size - $prev_file_size;
                $this->saasvalidation->updateResouceQuota('storage', $size_difference);
            }

            $this->vehicle_model->add($data);
            $msg   = $this->lang->line('success_message');
            $array = array('status' => 'success', 'error' => '', 'message' => $msg);
             } catch (Exception $e) {
                $array = array('status' => 'fail', 'error' => $e->getMessage() , 'message' => '');
            } 
        }
        echo json_encode($array);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_delete')) {
            access_denied();
        }
        $row = $this->vehicle_model->get($id);       

        if ($row->documents != '') {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->documents, 'uploads/vehicle_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);                
            $this->media_storage->filedelete($row->documents, "uploads/vehicle_documents/");
        }
        $this->vehicle_model->remove($id);
        redirect('admin/vehicle/index');
    }

    public function download($id)
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_view')) {
            access_denied();
        }
        $row = $this->vehicle_model->get($id);
        if ($row && !empty($row->documents)) {
            $this->media_storage->filedownload($row->documents, "uploads/vehicle_documents");
        }
    }
    
    public function vehicledetails()
    {
        $vehicleid           = $this->input->post('vehicleid');
        $data['editvehicle'] = $this->vehicle_model->getVehicleWithDetails($vehicleid);

        $session_id = $this->setting_model->getCurrentSession();
        $this->db->select('students.id, students.admission_no, students.firstname, students.middlename, students.lastname, students.mobileno, students.guardian_phone, classes.class, sections.section, transport_route.route_title, pickup_point.name as pickup_point_name');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('vehicle_routes', 'vehicle_routes.id = student_session.vehroute_id');
        $this->db->join('transport_route', 'transport_route.id = vehicle_routes.route_id', 'left');
        $this->db->join('route_pickup_point', 'route_pickup_point.id = student_session.route_pickup_point_id', 'left');
        $this->db->join('pickup_point', 'pickup_point.id = route_pickup_point.pickup_point_id', 'left');
        $this->db->where('vehicle_routes.vehicle_id', $vehicleid);
        $this->db->where('student_session.session_id', $session_id);
        $this->db->where('students.is_active', 'yes');
        $this->db->order_by('students.firstname', 'asc');
        $data['student_list'] = $this->db->get()->result_array();

        // Fetch maintenance records for this vehicle
        $data['maintenance_list'] = $this->maintenance_model->getByVehicleId($vehicleid);

        $page                = $this->load->view('admin/vehicle/_vehicledetails', $data, true);
        echo json_encode(array('page' => $page));
    }
    
    public function handle_upload()
    {
        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["documents"]) && !empty($_FILES['documents']['name'])) {

            $file_type = $_FILES["documents"]['type'];
            $file_size = $_FILES["documents"]["size"];
            $file_name = $_FILES["documents"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES["documents"]['tmp_name'])) {

                if (!in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                    return false;
                }
                
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($result->image_size / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_extension_error_uploading_image'));
                return false;
            }

            return true;
        }
        return true;
    }

    public function gettransferdetails()
    {
        $vehicleid = $this->input->post('vehicleid');
        $vehicle   = $this->vehicle_model->getVehicleWithDetails($vehicleid);
        $branches  = $this->vehicle_model->getBranches();
        $routes    = $this->route_model->get();
        $drivers   = $this->driver_model->get();
        echo json_encode(array(
            'status'   => 'success',
            'vehicle'  => $vehicle,
            'branches' => $branches,
            'routes'   => $routes,
            'drivers'  => $drivers
        ));
    }

    public function transfer()
    {
        if (!$this->rbac->hasPrivilege('vehicle', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_rules('vehicle_id', 'Vehicle', 'trim|required|xss_clean');
        $this->form_validation->set_rules('transfer_branch', 'Transfer To Branch', 'trim|required|xss_clean');
        $this->form_validation->set_rules('assign_driver_id', 'Assign Driver', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'transfer_branch'  => form_error('transfer_branch'),
                'assign_driver_id' => form_error('assign_driver_id'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $vehicle_id = $this->input->post('vehicle_id');
            $branch     = $this->input->post('transfer_branch');
            $driver_id  = $this->input->post('assign_driver_id');

            $this->vehicle_model->transferVehicle($vehicle_id, $branch, $driver_id);

            // Sync with vehicle_routes if branch matches a route
            $all_routes = $this->route_model->get();
            $route_id   = null;
            if (!empty($all_routes)) {
                foreach ($all_routes as $r) {
                    if ($r['route_title'] == $branch || $r['id'] == $branch) {
                        $route_id = $r['id'];
                        break;
                    }
                }
            }

            if ($route_id) {
                $vr = $this->db->get_where('vehicle_routes', array('vehicle_id' => $vehicle_id))->row();
                if ($vr) {
                    $this->db->where('vehicle_id', $vehicle_id);
                    $this->db->update('vehicle_routes', array(
                        'route_id'  => $route_id,
                        'driver_id' => $driver_id,
                    ));
                } else {
                    $this->db->insert('vehicle_routes', array(
                        'route_id'   => $route_id,
                        'vehicle_id' => $vehicle_id,
                        'driver_id'  => $driver_id,
                    ));
                }
            }

            $array = array('status' => 'success', 'error' => '', 'message' => 'Vehicle transferred successfully');
        }
        echo json_encode($array);
    }

}
