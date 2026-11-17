<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'controllers/api/Base.php';

class Pickuppoint extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pickuppoint_model');
    }

    /**
     * GET /api/pickuppoint
     * Returns all pickup points from the admin Pickup Point List
     */
    public function index()
    {
        $this->_check_auth([], false);

        $route_id = $this->input->get('route_id') ?? $this->input->get('routeid');
        if (!empty($route_id)) {
            $points = $this->pickuppoint_model->getPickupPointByRouteID($route_id);
            $this->_success('Route pickup points retrieved successfully', $points);
            return;
        }

        $id = $this->input->get('id');
        if (!empty($id)) {
            $point = $this->pickuppoint_model->get($id);
            if (!$point) {
                $this->_error('Pickup point not found', 404);
                return;
            }
            $this->_success('Pickup point retrieved successfully', $point);
            return;
        }

        $points = $this->pickuppoint_model->get();
        $this->_success('Pickup points retrieved successfully', $points);
    }

    /**
     * GET /api/pickuppoint/by_route/{route_id}
     */
    public function by_route($route_id = null)
    {
        $this->_check_auth([], false);

        if (empty($route_id)) {
            $route_id = $this->input->get('route_id');
        }

        if (empty($route_id)) {
            $this->_error('Route ID is required', 400);
            return;
        }

        $points = $this->pickuppoint_model->getPickupPointByRouteID($route_id);
        $this->_success('Route pickup points retrieved successfully', $points);
    }
}

