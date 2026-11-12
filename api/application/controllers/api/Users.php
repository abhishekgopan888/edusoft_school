<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users extends Base
{
    public $swagger_meta = [
        'tag' => 'Users',
        'description' => 'User related endpoints for the API.',
        'methods' => [
            'list_users' => [
                'summary' => 'List users',
                'description' => 'Returns a list of users.',
                'http_method' => 'get',
            ],
            'show_user' => [
                'summary' => 'Show user details',
                'description' => 'Returns a single user by id.',
                'http_method' => 'get',
            ],
            'create_user' => [
                'summary' => 'Create a new user',
                'description' => 'Creates a new user with the provided payload.',
                'http_method' => 'post',
            ],
        ],
    ];

    public function list_users()
    {
        $this->_success('Users fetched successfully', ['users' => []]);
    }

    public function show_user($id = null)
    {
        $this->_success('User fetched successfully', ['id' => $id]);
    }

    public function create_user()
    {
        $this->_success('User created successfully', ['created' => true]);
    }
}
