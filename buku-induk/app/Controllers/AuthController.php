<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function loginForm(): void
    {
        if (!empty($_SESSION['user_id'])) {
            redirect('/');
        }
        view('auth/login', [
            'error' => flash('error'),
        ]);
    }

    public function login(): void
    {
        verify_csrf();

        // Rate limit sederhana
        $now = time();
        $attempts = isset($_SESSION['login_attempts']) ? (int) $_SESSION['login_attempts'] : 0;
        $last = isset($_SESSION['login_last']) ? (int) $_SESSION['login_last'] : 0;
        if ($attempts >= 8 && ($now - $last) < 300) {
            flash('error', 'Terlalu banyak percobaan. Coba lagi dalam beberapa menit.');
            redirect('/login');
        }
        if (($now - $last) > 300) {
            $attempts = 0;
        }

        $username = isset($_POST['username']) ? trim((string) $_POST['username']) : '';
        $password = isset($_POST['password']) ? (string) $_POST['password'] : '';

        $user = User::attempt($username, $password);
        if (!$user) {
            $_SESSION['login_attempts'] = $attempts + 1;
            $_SESSION['login_last'] = $now;
            flash('error', 'Username atau password salah.');
            redirect('/login');
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['login_attempts'] = 0;
        session_regenerate_id(true);
        redirect('/');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
        redirect('/login');
    }
}
