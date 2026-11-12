<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Health extends Base
{
    public $swagger_meta = [
        'tag' => 'Health',
        'description' => 'Simple health and availability endpoints.',
        'methods' => [
            'ping' => [
                'summary' => 'Ping the API',
                'description' => 'Returns a simple success response to confirm the API is reachable.',
                'http_method' => 'get',
            ],
            'status' => [
                'summary' => 'Get API status',
                'description' => 'Returns the current API status and environment details.',
                'http_method' => 'get',
            ],
        ],
    ];

    public function ping()
    {
        $this->_success('API is reachable', ['service' => 'school-api']);
    }

    public function status()
    {
        $this->_success('API is healthy', ['environment' => ENVIRONMENT, 'timestamp' => date('c')]);
    }
}
