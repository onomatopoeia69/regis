<?php

namespace App\Core;

use App\Models\Users;
use App\Core\Session;

class Auth
{
    public static function attempt($username, $password)
    {
        $user = Users::authenticate($username, $password);

        if (!$user) {
            return false;
        }

        session_regenerate_id(true);

        Session::set('user_id', $user['id']);
        Session::set('role_id', (int) $user['role_id']);
        Session::set('role_name', $user['role_name']);
        Session::set('authenticated', true);
        Session::set('first_name', $user['first_name']);
        Session::set('last_name', $user['last_name']);
        Session::set('email', $user['email']);
        Session::set('username', $user['username']);

        return true;
    }

    public static function check()
    {
        return Session::get('authenticated') === true
            && Session::get('user_id') !== null;
    }

    public static function id()
    {
        return Session::get('user_id');
    }

    public static function role()
    {
        return Session::get('role_name');
    }

    public static function roleId()
    {
        return Session::get('role_id');
    }

    public static function hasRole(array $roles)
    {
        return in_array(
            (int) self::roleId(),
            $roles,
            true
        );
    }

    public static function logout()
    {
        Session::destroy();
    }
}
