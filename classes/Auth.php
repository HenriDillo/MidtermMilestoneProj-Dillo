<?php
// Auth - everything about "who is logged in" (uses the PHP session).

class Auth
{
    // Remember the user in the session after a successful login or sign-up.
    public static function login(array $user)
    {
        session_regenerate_id(true);   // new session id = safer against session theft
        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
    }

    public static function logout()
    {
        unset($_SESSION['user_id'], $_SESSION['username']);
        session_regenerate_id(true);
    }

    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    public static function id()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function username()
    {
        return $_SESSION['username'] ?? '';
    }

    // Put this at the top of every page that is for members only.
    public static function requireLogin()
    {
        if (!self::check()) {
            set_flash('error', 'Please log in to continue.');
            redirect('login.php');
        }
    }

    // Login and sign-up pages are not needed by people who are already logged in.
    public static function requireGuest()
    {
        if (self::check()) {
            redirect('recipes.php');
        }
    }
}
