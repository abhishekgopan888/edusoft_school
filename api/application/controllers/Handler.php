<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Handler extends CI_Controller
{
    public function show_404()
    {
        $this->output
            ->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'error',
                'message' => 'Endpoint not found'
            ]));
    }
}
