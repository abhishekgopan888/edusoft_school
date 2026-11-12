<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Attender extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('media_storage');
        $this->load->library('SaasValidation');
        $this->load->model('attender_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('attender', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Transport');
        $this->session->set_userdata('sub_menu', 'attender/index');
        $data['title']        = 'Attender List';
        $data['listAttender'] = $this->attender_model->get();
        $this->load->view('layout/header');
        $this->load->view('admin/attender/index', $data);
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
        if (!$this->rbac->hasPrivilege('attender', 'can_add')) {
            access_denied();
        }
        $this->form_validation->set_rules('attender_name', 'Attender Name', 'trim|required|xss_clean');
        
        $this->form_validation->set_rules('attender_image', 'Attender Image', 'callback_handle_upload[attender_image]');
        $this->form_validation->set_rules('id_proof', 'ID Proof Document', 'callback_handle_upload[id_proof]');
        $this->form_validation->set_rules('documents', 'Documents', 'callback_handle_upload[documents]');

        $storage_array = "attender_image,id_proof,documents";
        $this->form_validation->set_rules('attender_image', 'Storage', "callback_validateCanUploadFile[$storage_array]");

        if ($this->form_validation->run() == false) {
            $msg   = array(
                'attender_name' => form_error('attender_name'), 
                'attender_image' => form_error('attender_image'),
                'id_proof' => form_error('id_proof'),
                'documents' => form_error('documents')
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            try {
                $total_failed_size = 0;
                $storage_array_list = ['attender_image', 'id_proof', 'documents'];
                $this->saasvalidation->updateStorageLimit('storage', $storage_array_list);
                
                $attender_image = $this->media_storage->fileupload("attender_image", "./uploads/attender_documents/");
                if (IsNullOrEmptyString($attender_image)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('attender_image');
                }
                
                $id_proof = $this->media_storage->fileupload("id_proof", "./uploads/attender_documents/");
                if (IsNullOrEmptyString($id_proof)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('id_proof');
                }
                
                $documents = $this->media_storage->fileupload("documents", "./uploads/attender_documents/");
                if (IsNullOrEmptyString($documents)) {
                    $total_failed_size += $this->media_storage->getTmpFileSize('documents');
                }
                
                if ($total_failed_size > 0) {
                    $this->saasvalidation->deleteResouceQuota('storage', $total_failed_size);
                }

                $data = array(
                    'attender_name'  => $this->input->post('attender_name'),
                    'age'            => $this->input->post('age'),
                    'address'        => $this->input->post('address'),
                    'id_proof'       => $id_proof,
                    'attender_image' => $attender_image,
                    'note'           => $this->input->post('note'),
                    'status'         => $this->input->post('status'),
                    'documents'      => $documents,
                );

                $this->attender_model->add($data);
                $msg   = $this->lang->line('success_message');
                $array = array('status' => 'success', 'error' => '', 'message' => $msg);
            } catch (Exception $e) {
                $array = array('status' => 'fail', 'error' => $e->getMessage(), 'message' => '');
            }
        }
        echo json_encode($array);
    }

    public function getsingleattender()
    {
        if (!$this->rbac->hasPrivilege('attender', 'can_edit')) {
            access_denied();
        }
        $attenderid           = $this->input->post('attenderid');
        $data['editattender'] = $this->attender_model->get($attenderid);
        $page                 = $this->load->view('admin/attender/edit', $data, true);
        echo json_encode(array('page' => $page));
    }

    public function edit()
    {
        if (!$this->rbac->hasPrivilege('attender', 'can_edit')) {
            access_denied();
        }
        $this->form_validation->set_rules('attender_name', 'Attender Name', 'trim|required|xss_clean');
        
        $this->form_validation->set_rules('attender_image', 'Attender Image', 'callback_handle_upload[attender_image]');
        $this->form_validation->set_rules('id_proof', 'ID Proof Document', 'callback_handle_upload[id_proof]');
        $this->form_validation->set_rules('documents', 'Documents', 'callback_handle_upload[documents]');

        $storage_array = "attender_image,id_proof,documents";
        $this->form_validation->set_rules('attender_image', 'Storage', "callback_validateCanUploadFile[$storage_array]");

        $id       = $this->input->post('id');
        $attender = $this->attender_model->get($id);

        if ($this->form_validation->run() == false) {
            $msg   = array(
                'attender_name' => form_error('attender_name'),
                'attender_image' => form_error('attender_image'),
                'id_proof' => form_error('id_proof'),
                'documents' => form_error('documents')
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            try {
                $prev_file_size  = 0;
                $total_upload_size = 0;

                $data = array(
                    'id'            => $id,
                    'attender_name' => $this->input->post('attender_name'),
                    'age'           => $this->input->post('age'),
                    'address'       => $this->input->post('address'),
                    'note'          => $this->input->post('note'),
                    'status'        => $this->input->post('status'),
                );

                // Attender Image
                if (isset($_FILES["attender_image"]) && $_FILES['attender_image']['name'] != '' && (!empty($_FILES['attender_image']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($attender->attender_image, 'uploads/attender_documents');
                    $attender_image = $this->media_storage->fileupload("attender_image", "./uploads/attender_documents/");
                    if (!IsNullOrEmptyString($attender_image)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('attender_image');
                    }
                } else {
                    $attender_image = $attender->attender_image;
                }
                $data['attender_image'] = $attender_image;

                // ID Proof
                if (isset($_FILES["id_proof"]) && $_FILES['id_proof']['name'] != '' && (!empty($_FILES['id_proof']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($attender->id_proof, 'uploads/attender_documents');
                    $id_proof = $this->media_storage->fileupload("id_proof", "./uploads/attender_documents/");
                    if (!IsNullOrEmptyString($id_proof)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('id_proof');
                    }
                } else {
                    $id_proof = $attender->id_proof;
                }
                $data['id_proof'] = $id_proof;

                // Documents
                if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                    $prev_file_size += $this->media_storage->getUploadedFileSize($attender->documents, 'uploads/attender_documents');
                    $doc_name       = $this->media_storage->fileupload("documents", "./uploads/attender_documents/");
                    if (!IsNullOrEmptyString($doc_name)) {
                        $total_upload_size += $this->media_storage->getTmpFileSize('documents');
                    }
                } else {
                    $doc_name = $attender->documents;
                }
                $data['documents'] = $doc_name;

                // Delete old files from disk
                if (isset($_FILES["attender_image"]) && $_FILES['attender_image']['name'] != '' && (!empty($_FILES['attender_image']['name']))) {
                    if ($attender->attender_image != '') {
                        $this->media_storage->filedelete($attender->attender_image, "uploads/attender_documents");
                    }
                }
                if (isset($_FILES["id_proof"]) && $_FILES['id_proof']['name'] != '' && (!empty($_FILES['id_proof']['name']))) {
                    if ($attender->id_proof != '') {
                        $this->media_storage->filedelete($attender->id_proof, "uploads/attender_documents");
                    }
                }
                if (isset($_FILES["documents"]) && $_FILES['documents']['name'] != '' && (!empty($_FILES['documents']['name']))) {
                    if ($attender->documents != '') {
                        $this->media_storage->filedelete($attender->documents, "uploads/attender_documents");
                    }
                }

                if ($prev_file_size > $total_upload_size) {
                    $this->saasvalidation->deleteResouceQuota('storage', $prev_file_size - $total_upload_size);
                } elseif ($prev_file_size < $total_upload_size) {
                    $this->saasvalidation->updateResouceQuota('storage', $total_upload_size - $prev_file_size);
                }

                $this->attender_model->add($data);
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
        if (!$this->rbac->hasPrivilege('attender', 'can_delete')) {
            access_denied();
        }
        $row = $this->attender_model->get($id);
        if (!empty($row->attender_image)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->attender_image, 'uploads/attender_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->attender_image, "uploads/attender_documents/");
        }
        if (!empty($row->id_proof)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->id_proof, 'uploads/attender_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->id_proof, "uploads/attender_documents/");
        }
        if (!empty($row->documents)) {
            $delete_file_size = $this->media_storage->getUploadedFileSize($row->documents, 'uploads/attender_documents');
            $this->saasvalidation->deleteResouceQuota('storage', $delete_file_size);
            $this->media_storage->filedelete($row->documents, "uploads/attender_documents/");
        }
        $this->attender_model->remove($id);
        redirect('admin/attender/index');
    }

    public function viewfile($id, $type = 'documents')
    {
        if (!$this->rbac->hasPrivilege('attender', 'can_view')) {
            access_denied();
        }

        $row = $this->attender_model->get($id);
        if (empty($row)) {
            show_404();
        }

        $allowed_types = ['id_proof', 'documents', 'attender_image'];
        if (!in_array($type, $allowed_types)) {
            show_404();
        }

        $file_name = $row->$type;
        if (empty($file_name)) {
            show_404();
        }

        $base_folder = rtrim(FCPATH, '/\\');

        $clean_file = ltrim($file_name, './\\');
        $file_path = rtrim($base_folder, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'attender_documents' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $clean_file);

        if (!file_exists($file_path)) {
            show_404();
        }

        $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $mime_types = [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $mime = isset($mime_types[$ext]) ? $mime_types[$ext] : 'application/octet-stream';

        // Display name (strip timestamp prefix before !)
        $display_name = (strpos($file_name, '!') !== false)
            ? substr($file_name, strpos($file_name, '!') + 1)
            : basename($file_name);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $display_name . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');

        readfile($file_path);
        exit;
    }

    public function attenderdetails()
    {
        if (!$this->rbac->hasPrivilege('attender', 'can_view')) {
            access_denied();
        }
        $attenderid           = $this->input->post('attenderid');
        $data['editattender'] = $this->attender_model->get($attenderid);
        $page                 = $this->load->view('admin/attender/_attenderdetails', $data, true);
        echo json_encode(array('page' => $page));
    }

}
