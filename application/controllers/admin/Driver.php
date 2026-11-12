<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Driver extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->library('SaasValidation');
        $this->load->model('driver_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('driver', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'driver/index');
        $data['title']      = 'Driver List';
        $data['listDriver'] = $this->driver_model->get();
        $this->load->view('layout/header');
        $this->load->view('admin/driver/index', $data);
        $this->load->view('layout/footer');
    }

    public function validateCanUploadFile($str, $params_string)
    {
        $params_array = array_map('trim', explode(',', $params_string));
        return $this->saasvalidation->validateCanUploadFile($str, $params_array);
    }

    public function handle_upload($field_name)
    {
        $result = $this->filetype_model->get();
        if (isset($_FILES[$field_name]) && !empty($_FILES[$field_name]['name'])) {
            $file_type = $_FILES[$field_name]['type'];
            $file_size = $_FILES[$field_name]['size'];
            $file_name = $_FILES[$field_name]['name'];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = filesize($_FILES[$field_name]['tmp_name'])) {
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

    public function add()
    {
        if (!$this->rbac->hasPrivilege('driver', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('driver_name', 'Driver Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('age', 'Age', 'trim|required|xss_clean');
        $this->form_validation->set_rules('licence', 'Licence No', 'trim|required|xss_clean');
        $this->form_validation->set_rules('contact', 'Contact', 'trim|required|xss_clean');
        $this->form_validation->set_rules('status', 'Status', 'trim|required|xss_clean');
        $this->form_validation->set_rules('address', 'Address', 'trim|required|xss_clean');
        
        $this->form_validation->set_rules('licence_front', 'Licence Front Page', 'callback_handle_upload[licence_front]');
        $this->form_validation->set_rules('licence_back', 'Licence Back Page', 'callback_handle_upload[licence_back]');
        $this->form_validation->set_rules('driver_image', 'Driver Image', 'callback_handle_upload[driver_image]');
        $this->form_validation->set_rules('id_proof', 'ID Proof Document', 'callback_handle_upload[id_proof]');
        $this->form_validation->set_rules('documents', 'Documents', 'callback_handle_upload[documents]');

        $storage_array = "licence_front,licence_back,driver_image,id_proof,documents";
        $this->form_validation->set_rules('licence_front', 'Storage', "callback_validateCanUploadFile[$storage_array]");

        if ($this->form_validation->run() == false) {
            // Also check file fields are provided on add
            $file_errors = array();
            if (empty($_FILES['licence_front']['name'])) {
                $file_errors['licence_front'] = 'The Licence Front Page field is required.';
            }
            if (empty($_FILES['licence_back']['name'])) {
                $file_errors['licence_back'] = 'The Licence Back Page field is required.';
            }
            if (empty($_FILES['driver_image']['name'])) {
                $file_errors['driver_image'] = 'The Driver Image field is required.';
            }
            $msg = array(
                'driver_name'   => form_error('driver_name'),
                'age'           => form_error('age'),
                'licence'       => form_error('licence'),
                'contact'       => form_error('contact'),
                'status'        => form_error('status'),
                'address'       => form_error('address'),
                'licence_front' => form_error('licence_front') ?: (isset($file_errors['licence_front']) ? $file_errors['licence_front'] : ''),
                'licence_back'  => form_error('licence_back')  ?: (isset($file_errors['licence_back'])  ? $file_errors['licence_back']  : ''),
                'driver_image'  => form_error('driver_image')  ?: (isset($file_errors['driver_image'])  ? $file_errors['driver_image']  : ''),
                'id_proof'      => form_error('id_proof'),
                'documents'     => form_error('documents'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            // Check required file uploads
            $file_errors = array();
            if (empty($_FILES['licence_front']['name'])) {
                $file_errors['licence_front'] = 'The Licence Front Page field is required.';
            }
            if (empty($_FILES['licence_back']['name'])) {
                $file_errors['licence_back'] = 'The Licence Back Page field is required.';
            }
            if (empty($_FILES['driver_image']['name'])) {
                $file_errors['driver_image'] = 'The Driver Image field is required.';
            }
            if (!empty($file_errors)) {
                $array = array('status' => 'fail', 'error' => $file_errors, 'message' => '');
                echo json_encode($array);
                return;
            }

            try {
                $total_failed_size = 0;
                $storage_array_list = ['licence_front', 'licence_back', 'driver_image', 'id_proof', 'documents'];
                $this->saasvalidation->updateStorageLimit('storage', $storage_array_list);
                
                $licence_front = $this->media_storage->fileupload("licence_front", "./uploads/driver_documents/");
                if (IsNullOrEmptyString($licence_front)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('licence_front');
                }
                
                $licence_back = $this->media_storage->fileupload("licence_back", "./uploads/driver_documents/");
                if (IsNullOrEmptyString($licence_back)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('licence_back');
                }
                
                $driver_image = $this->media_storage->fileupload("driver_image", "./uploads/driver_documents/");
                if (IsNullOrEmptyString($driver_image)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('driver_image');
                }

                $id_proof = $this->media_storage->fileupload("id_proof", "./uploads/driver_documents/");
                if (IsNullOrEmptyString($id_proof)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('id_proof');
                }
                
                $documents = $this->media_storage->fileupload("documents", "./uploads/driver_documents/");
                if (IsNullOrEmptyString($documents)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('documents');
                }
                
                if ($total_failed_size > 0) {
                    $this->saasvalidation->deleteResouceQuota('storage', $total_failed_size);
                }

                $data = array(
                    'driver_name'   => $this->input->post('driver_name'),
                    'age'           => $this->input->post('age'),
                    'address'       => $this->input->post('address'),
                    'licence'       => $this->input->post('licence'),
                    'licence_front' => $licence_front,
                    'licence_back'  => $licence_back,
                    'driver_image'  => $driver_image,
                    'id_proof'      => $id_proof,
                    'contact'       => $this->input->post('contact'),
                    'note'          => $this->input->post('note'),
                    'status'        => $this->input->post('status'),
                    'documents'     => $documents,
                );

                $this->driver_model->add($data);
                $msg   = $this->lang->line('success_message');
                $array = array('status' => 'success', 'error' => '', 'message' => $msg);
            } catch (Exception $e) {
                $array = array('status' => 'fail', 'error' => $e->getMessage(), 'message' => '');
            }
        }
        echo json_encode($array);
    }

    public function getsingledriver()
    {
        if (!$this->rbac->hasPrivilege('driver', 'can_edit')) {
            access_denied();
        }
        $driverid         = $this->input->post('driverid');
        $data['editdriver'] = $this->driver_model->get($driverid);
        $page             = $this->load->view('admin/driver/edit', $data, true);
        echo json_encode(array('page' => $page));
    }

    public function edit()
    {
        if (!$this->rbac->hasPrivilege('driver', 'can_edit')) {
            access_denied();
        }
        $this->form_validation->set_rules('driver_name', 'Driver Name', 'trim|required|xss_clean');
        
        $this->form_validation->set_rules('licence_front', 'Licence Front Page', 'callback_handle_upload[licence_front]');
        $this->form_validation->set_rules('licence_back', 'Licence Back Page', 'callback_handle_upload[licence_back]');
        $this->form_validation->set_rules('driver_image', 'Driver Image', 'callback_handle_upload[driver_image]');
        $this->form_validation->set_rules('id_proof', 'ID Proof Document', 'callback_handle_upload[id_proof]');
        $this->form_validation->set_rules('documents', 'Documents', 'callback_handle_upload[documents]');

        $storage_array = "licence_front,licence_back,driver_image,id_proof,documents";
        $this->form_validation->set_rules('licence_front', 'Storage', "callback_validateCanUploadFile[$storage_array]");

        $id     = $this->input->post('id');
        $driver = $this->driver_model->get($id);

        if ($this->form_validation->run() == false) {
            $msg   = array(
                'driver_name' => form_error('driver_name'),
                'licence_front' => form_error('licence_front'),
                'licence_back' => form_error('licence_back'),
                'driver_image' => form_error('driver_image'),
                'id_proof' => form_error('id_proof'),
                'documents' => form_error('documents')
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            try {
                $prev_file_size  = 0;
                $total_upload_size = 0;

                $data = array(
                    'id'          => $id,
                    'driver_name' => $this->input->post('driver_name'),
                    'age'         => $this->input->post('age'),
                    'address'     => $this->input->post('address'),
                    'licence'     => $this->input->post('licence'),
                    'contact'     => $this->input->post('contact'),
                    'note'        => $this->input->post('note'),
                    'status'      => $this->input->post('status'),
                );

                // Licence Front
                if (isset($_FILES["licence_front"]) && $_FILES['licence_front']['name'] != '' && (!empty($_FILES['licence_front']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($driver->licence_front, 'uploads/driver_documents');
                    $licence_front = $this->media_storage->fileupload("licence_front", "./uploads/driver_documents/");
                    if (!IsNullOrEmptyString($licence_front)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('licence_front');
                    }
                } else {
                    $licence_front = $driver->licence_front;
                }
                $data['licence_front'] = $licence_front;

                // Licence Back
                if (isset($_FILES["licence_back"]) && $_FILES['licence_back']['name'] != '' && (!empty($_FILES['licence_back']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($driver->licence_back, 'uploads/driver_documents');
                    $licence_back = $this->media_storage->fileupload("licence_back", "./uploads/driver_documents/");
                    if (!IsNullOrEmptyString($licence_back)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('licence_back');
                    }
                } else {
                    $licence_back = $driver->licence_back;
                }
                $data['licence_back'] = $licence_back;

                // Driver Image
                if (isset($_FILES["driver_image"]) && $_FILES['driver_image']['name'] != '' && (!empty($_FILES['driver_image']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($driver->driver_image, 'uploads/driver_documents');
                    $driver_image = $this->media_storage->fileupload("driver_image", "./uploads/driver_documents/");
                    if (!IsNullOrEmptyString($driver_image)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('driver_image');
                    }
                } else {
                    $driver_image = $driver->driver_image;
                }
                $data['driver_image'] = $driver_image;

                // ID Proof
                if (isset($_FILES["id_proof"]) && $_FILES['id_proof']['name'] != '' && (!empty($_FILES['id_proof']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($driver->id_proof, 'uploads/driver_documents');
                    $id_proof = $this->media_storage->fileupload("id_proof", "./uploads/driver_documents/");
                    if (!IsNullOrEmptyString($id_proof)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('id_proof');
                    }
                } else {
                    $id_proof = $driver->id_proof;
                }
                $data['id_proof'] = $id_proof;

                // Documents
                if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($driver->documents, 'uploads/driver_documents');
                    $doc_name       = $this->media_storage->fileupload("documents", "./uploads/driver_documents/");
                    if (!IsNullOrEmptyString($doc_name)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('documents');
                    }
                } else {
                    $doc_name = $driver->documents;
                }
                $data['documents'] = $doc_name;

                // Delete old files from disk
                if (isset($_FILES["licence_front"]) && $_FILES['licence_front']['name'] != '' && (!empty($_FILES['licence_front']['name']))) {
                    if ($driver->licence_front != '') {
                        $this->media_storage->filedelete($driver->licence_front, "uploads/driver_documents");
                    }
                }
                if (isset($_FILES["licence_back"]) && $_FILES['licence_back']['name'] != '' && (!empty($_FILES['licence_back']['name']))) {
                    if ($driver->licence_back != '') {
                        $this->media_storage->filedelete($driver->licence_back, "uploads/driver_documents");
                    }
                }
                if (isset($_FILES["driver_image"]) && $_FILES['driver_image']['name'] != '' && (!empty($_FILES['driver_image']['name']))) {
                    if ($driver->driver_image != '') {
                        $this->media_storage->filedelete($driver->driver_image, "uploads/driver_documents");
                    }
                }
                if (isset($_FILES["id_proof"]) && $_FILES['id_proof']['name'] != '' && (!empty($_FILES['id_proof']['name']))) {
                    if ($driver->id_proof != '') {
                        $this->media_storage->filedelete($driver->id_proof, "uploads/driver_documents");
                    }
                }
                if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                    if ($driver->documents != '') {
                        $this->media_storage->filedelete($driver->documents, "uploads/driver_documents");
                    }
                }

                if ($prev_file_size > $total_upload_size) {
                    $this->saasvalidation->deleteResouceQuota('storage', $prev_file_size - $total_upload_size);
                } elseif ($prev_file_size < $total_upload_size) {
                    $this->saasvalidation->updateResouceQuota('storage', $total_upload_size - $prev_file_size);
                }

                $this->driver_model->add($data);
                $msg   = $this->lang->line('success_message');
                $array = array('status' => 'success', 'error' => '', 'message' => $msg);
            } catch (Exception $e) {
                $array = array('status' => 'fail', 'error' => $e->getMessage(), 'message' => '');
            }
        }
        echo json_encode($array);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('driver', 'can_delete')) {
            access_denied();
        }
        $row = $this->driver_model->get($id);
        if (!empty($row->licence_front)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->licence_front, 'uploads/driver_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->licence_front, "uploads/driver_documents/");
        }
        if (!empty($row->licence_back)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->licence_back, 'uploads/driver_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->licence_back, "uploads/driver_documents/");
        }
        if (!empty($row->driver_image)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->driver_image, 'uploads/driver_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->driver_image, "uploads/driver_documents/");
        }
        if (!empty($row->id_proof)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->id_proof, 'uploads/driver_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->id_proof, "uploads/driver_documents/");
        }
        if (!empty($row->documents)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->documents, 'uploads/driver_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->documents, "uploads/driver_documents/");
        }
        $this->driver_model->remove($id);
        redirect('admin/driver/index');
    }

    public function driverdetails()
    {
        if (!$this->rbac->hasPrivilege('driver', 'can_view')) {
            access_denied();
        }
        $driverid         = $this->input->post('driverid');
        $data['editdriver'] = $this->driver_model->get($driverid);
        $page             = $this->load->view('admin/driver/_driverdetails', $data, true);
        echo json_encode(array('page' => $page));
    }

    public function download($id, $type = 'documents')
    {
        if (!$this->rbac->hasPrivilege('driver', 'can_view')) {
            access_denied();
        }
        $row = $this->driver_model->get($id);
        if ($row) {
            $allowed_types = array('licence_front', 'licence_back', 'driver_image', 'id_proof', 'documents');
            if (!in_array($type, $allowed_types)) {
                $type = 'documents';
            }
            $filename = isset($row->$type) ? $row->$type : '';
            if (!empty($filename)) {
                $this->media_storage->filedownload($filename, "uploads/driver_documents");
            }
        }
    }

}
