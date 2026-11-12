<?php
defined('BASEPATH') or exit('No direct script access allowed');

class api extends CI_Controller
{

    public function login()
    {

    $post = json_decode(file_get_contents('php://input'), true);

    $username = $post['username'];
    $password = $post['password'];

    $user = $this->db
                 ->where('email', $username)
                 ->get('users')
                 ->row();

    if ($user) {

        // If password is stored as plain text
        if ($password == $user->password) {

            echo json_encode([
                'status' => 1,
                'message' => 'Login Successful',
                'data' => $user
            ]);

        } else {

            echo json_encode([
                'status' => 0,
                'message' => 'Invalid Password'
            ]);
        }

    } else {

        echo json_encode([
            'status' => 0,
            'message' => 'Invalid Username'
        ]);
    }

    }
}