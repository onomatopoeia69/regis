<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Helper\Links;
use App\Services\RateLimiter;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            Links::redirect('/home');
            exit;
        }

        $this->render('/login');
    }

    public function login()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        if ($username === '' || $password === '') {
            http_response_code(422);

            echo json_encode([
                'success' => false,
                'error_type' => 'validation',
                'message' => 'Username and password are required.'
            ]);

            exit;
        }

        $rate = new RateLimiter(5, 60);

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $key = "login:$username:$ip";

        if ($rate->tooManyAttempts($key)) {
            http_response_code(429);

            echo json_encode([
                'success' => false,
                'error_type' => 'rate_limit',
                'message' => 'Too many login attempts. Try again later.',
                'retry_after' => $rate->availableIn($key)
            ]);

            exit;
        }

        // AUTHENTICATE USING YOUR DATABASE
        if (!Auth::attempt($username, $password)) {
            $rate->hit($key);

            http_response_code(401);

            echo json_encode([
                'success' => false,
                'error_type' => 'invalid_input',
                'message' => 'Invalid username or password.'
            ]);

            exit;
        }

        // ADMIN ROLE CHECK
        // Replace these example IDs with your actual admin role IDs.
        $allowedRoles = [1, 2, 3];

        if (!Auth::hasRole($allowedRoles)) {
            Auth::logout();

            $rate->hit($key);

            http_response_code(403);

            echo json_encode([
                'success' => false,
                'error_type' => 'unauthorized_role',
                'message' => 'You are not authorized to access the admin portal.'
            ]);

            exit;
        }

        // SUCCESS
        $rate->clear($key);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful.',
            'redirect' => Links::goTo('/home')
        ]);

        exit;
    }

    public function logout()
    {
        Auth::logout();

        Links::redirect('/login');
        exit;
    }
}
