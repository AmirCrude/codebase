<?php
class SessionConfig {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.gc_maxlifetime', 86400);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_strict_mode', 1);
            session_start();
        }
    }
    
    public static function setUser($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    public static function getUser() {
        if (!self::isLoggedIn()) return null;
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'full_name' => $_SESSION['user_name']
        ];
    }
    
    public static function destroy() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
?>