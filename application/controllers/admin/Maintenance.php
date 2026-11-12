<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Maintenance extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('maintenance_model');
        $this->load->model('vehicle_model');
        $this->load->library('media_storage');
        $this->load->library('SaasValidation');

        // Self-migration for Maintenance Alert permission category
        if ($this->db->table_exists('permission_category')) {
            $query = $this->db->get_where('permission_category', array('short_code' => 'maintenance_alert'));
            if ($query->num_rows() == 0) {
                $this->db->insert('permission_category', array(
                    'perm_group_id' => 11,
                    'name' => 'Maintenance Alert',
                    'short_code' => 'maintenance_alert',
                    'enable_view' => 1,
                    'enable_add' => 0,
                    'enable_edit' => 0,
                    'enable_delete' => 0
                ));
            }
        }
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('vehicle_maintenance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'admin/maintenance');

        $data['title']           = 'Maintenance List';
        $data['listMaintenance'] = $this->maintenance_model->get();
        $data['listVehicle']     = $this->vehicle_model->get();

        $data['maintenance_alerts'] = array();
        if ($this->rbac->hasPrivilege('maintenance_alert', 'can_view')) {
            $data['maintenance_alerts'] = $this->maintenance_model->get_expiring_maintenance_alerts();
        }

        $this->load->view('layout/header');
        $this->load->view('admin/maintenance/index', $data);
        $this->load->view('layout/footer');
    }

    public function add()
    {
        if (!$this->rbac->hasPrivilege('vehicle_maintenance', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_rules('vehicle_id', $this->lang->line('select_vehicle'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('status',     $this->lang->line('status'),         'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg   = array(
                'vehicle_id' => form_error('vehicle_id'),
                'status'     => form_error('status'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            // Handle invoice file upload
            $invoice_file = '';
            if (isset($_FILES['invoice_file']) && !empty($_FILES['invoice_file']['name'])) {
                $invoice_file = $this->media_storage->fileupload("invoice_file", "./uploads/maintenance_invoice/");
            }

            // Parts list - encode as JSON
            $parts_list   = $this->buildPartsJson('part_name', 'part_price');
            // Labour list - encode as JSON
            $labour_list  = $this->buildLabourJson('service_desc', 'service_cost');

            $parts_total  = (float) $this->input->post('parts_total_hidden');
            $labour_total = (float) $this->input->post('labour_total_hidden');
            $same_as_parts = $this->input->post('same_as_parts') ? 1 : 0;
            $total_cost    = $parts_total + ($same_as_parts ? $parts_total : $labour_total);

            $data = array(
                'vehicle_id'             => $this->input->post('vehicle_id'),
                'odometer_reading'       => $this->input->post('odometer_reading'),
                'service_type'           => $this->input->post('service_type'),
                'last_service_date'      => $this->customlib->dateFormatToYYYYMMDD($this->input->post('last_service_date')),
                'next_service_date'      => $this->customlib->dateFormatToYYYYMMDD($this->input->post('next_service_date')),
                'insurance_expired_date' => $this->customlib->dateFormatToYYYYMMDD($this->input->post('insurance_expired_date')),
                'tax_expired_date'       => $this->customlib->dateFormatToYYYYMMDD($this->input->post('tax_expired_date')),
                'status'                 => $this->input->post('status'),
                'parts_vendor_name'      => $this->input->post('parts_vendor_name'),
                'parts_vendor_details'   => $this->input->post('parts_vendor_details'),
                'parts_list'             => $parts_list,
                'parts_total'            => $parts_total,
                'labour_garage'          => $this->input->post('labour_garage'),
                'labour_vendor_details'  => $this->input->post('labour_vendor_details'),
                'labour_list'            => $labour_list,
                'labour_total'           => $labour_total,
                'same_as_parts'          => $same_as_parts,
                'total_cost'             => $total_cost,
                'invoice_reference'      => $this->input->post('invoice_reference'),
                'invoice_file'           => $invoice_file,
                'description_notes'      => $this->input->post('description_notes'),
                'next_service_due'       => $this->customlib->dateFormatToYYYYMMDD($this->input->post('next_service_due')),
            );

            $this->maintenance_model->add($data);
            $msg   = $this->lang->line('success_message');
            $array = array('status' => 'success', 'error' => '', 'message' => $msg);
        }
        echo json_encode($array);
    }

    public function getsinglemaintenancedata()
    {
        $id = $this->input->post('id');
        $data['editmaintenance'] = $this->maintenance_model->get($id);
        $data['listVehicle']     = $this->vehicle_model->get();
        $page                    = $this->load->view('admin/maintenance/edit', $data, true);
        echo json_encode(array('page' => $page));
    }

    public function getsinglemaintenanceview()
    {
        $id = $this->input->post('id');
        $data['viewmaintenance'] = $this->maintenance_model->get($id);
        $page                    = $this->load->view('admin/maintenance/_view', $data, true);
        echo json_encode(array('page' => $page));
    }

    public function edit()
    {
        if (!$this->rbac->hasPrivilege('vehicle_maintenance', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_rules('vehicle_id', $this->lang->line('select_vehicle'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('status',     $this->lang->line('status'),         'trim|required|xss_clean');

        $id   = $this->input->post('id');
        $prev = $this->maintenance_model->get($id);

        if ($this->form_validation->run() == false) {
            $msg   = array(
                'vehicle_id' => form_error('vehicle_id'),
                'status'     => form_error('status'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            // Handle invoice file upload
            if (isset($_FILES['invoice_file']) && !empty($_FILES['invoice_file']['name'])) {
                $invoice_file = $this->media_storage->fileupload("invoice_file", "./uploads/maintenance_invoice/");
                if ($prev && $prev->invoice_file) {
                    $this->media_storage->filedelete($prev->invoice_file, "uploads/maintenance_invoice");
                }
            } else {
                $invoice_file = $prev ? $prev->invoice_file : '';
            }

            $parts_list   = $this->buildPartsJson('part_name', 'part_price');
            $labour_list  = $this->buildLabourJson('service_desc', 'service_cost');

            $parts_total   = (float) $this->input->post('parts_total_hidden');
            $labour_total  = (float) $this->input->post('labour_total_hidden');
            $same_as_parts = $this->input->post('same_as_parts') ? 1 : 0;
            $total_cost    = $parts_total + ($same_as_parts ? $parts_total : $labour_total);

            $data = array(
                'id'                     => $id,
                'vehicle_id'             => $this->input->post('vehicle_id'),
                'odometer_reading'       => $this->input->post('odometer_reading'),
                'service_type'           => $this->input->post('service_type'),
                'last_service_date'      => $this->customlib->dateFormatToYYYYMMDD($this->input->post('last_service_date')),
                'next_service_date'      => $this->customlib->dateFormatToYYYYMMDD($this->input->post('next_service_date')),
                'insurance_expired_date' => $this->customlib->dateFormatToYYYYMMDD($this->input->post('insurance_expired_date')),
                'tax_expired_date'       => $this->customlib->dateFormatToYYYYMMDD($this->input->post('tax_expired_date')),
                'status'                 => $this->input->post('status'),
                'parts_vendor_name'      => $this->input->post('parts_vendor_name'),
                'parts_vendor_details'   => $this->input->post('parts_vendor_details'),
                'parts_list'             => $parts_list,
                'parts_total'            => $parts_total,
                'labour_garage'          => $this->input->post('labour_garage'),
                'labour_vendor_details'  => $this->input->post('labour_vendor_details'),
                'labour_list'            => $labour_list,
                'labour_total'           => $labour_total,
                'same_as_parts'          => $same_as_parts,
                'total_cost'             => $total_cost,
                'invoice_reference'      => $this->input->post('invoice_reference'),
                'invoice_file'           => $invoice_file,
                'description_notes'      => $this->input->post('description_notes'),
                'next_service_due'       => $this->customlib->dateFormatToYYYYMMDD($this->input->post('next_service_due')),
            );

            $this->maintenance_model->add($data);
            $msg   = $this->lang->line('success_message');
            $array = array('status' => 'success', 'error' => '', 'message' => $msg);
        }
        echo json_encode($array);
    }

    public function download($id)
    {
        if (!$this->rbac->hasPrivilege('vehicle_maintenance', 'can_view')) {
            access_denied();
        }
        $row = $this->maintenance_model->get($id);
        if ($row && !empty($row->invoice_file)) {
            $this->media_storage->filedownload($row->invoice_file, "uploads/maintenance_invoice");
        }
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('vehicle_maintenance', 'can_delete')) {
            access_denied();
        }
        $row = $this->maintenance_model->get($id);
        if ($row && $row->invoice_file) {
            $this->media_storage->filedelete($row->invoice_file, "uploads/maintenance_invoice");
        }
        $this->maintenance_model->remove($id);
        redirect('admin/maintenance/index');
    }

    // ------- helpers -------
    private function buildPartsJson($nameField, $priceField)
    {
        $names  = $this->input->post($nameField);
        $prices = $this->input->post($priceField);
        if (empty($names)) return null;
        $parts = array();
        foreach ($names as $i => $name) {
            if (trim($name) !== '') {
                $parts[] = array('name' => trim($name), 'price' => (float)($prices[$i] ?? 0));
            }
        }
        return !empty($parts) ? json_encode($parts) : null;
    }

    private function buildLabourJson($descField, $costField)
    {
        $descs = $this->input->post($descField);
        $costs = $this->input->post($costField);
        if (empty($descs)) return null;
        $services = array();
        foreach ($descs as $i => $desc) {
            if (trim($desc) !== '') {
                $services[] = array('desc' => trim($desc), 'cost' => (float)($costs[$i] ?? 0));
            }
        }
        return !empty($services) ? json_encode($services) : null;
    }
}
