<?php
// This file is loaded via AJAX for the edit modal body.
// Pass data needed by the shared _form partial.
$this->load->view('admin/maintenance/_form', array(
    'listVehicle' => $listVehicle,
    'rec'         => $editmaintenance,
));
