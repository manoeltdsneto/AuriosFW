<?php

namespace App\Controllers;

use App\Models\authUsers;
use Core\controller;
use Core\authMiddleware;

class authController extends controller
{
    public function loginForm(): void
    {
        $this->render('auth/login');
    }

    public function login(): void
    {
        session_start();
        $user = new authUsers();

        $dados = $user->findByUsername($_POST['username']);

        if ($dados && password_verify($_POST['password'], $dados['password'])) {
            session_regenerate_id(true);
            $_SESSION['auth_user'] = $dados['id'];
            $this->redirect('user/index');
        } else {
            echo "Usuário ou senha incorretos.";
        }
    }

    public function logout(): void
    {
        authMiddleware::logout();
        $this->redirect('auth/loginForm');
    }

    public function registerForm(): void
    {
        $this->render('auth/register');
    }

    public function register(): void
    {
        $user = new authUsers();

        if (empty($_POST['username']) || empty($_POST['password'])) {
            echo "Preencha todos os campos.";
            return;
        }

        if ($user->findByUsername($_POST['username'])) {
            echo "Usuário já existe.";
            return;
        }

        $role = $_POST['role'] ?? 'user'; // Aceita role via POST (opcional)
        $user->register($_POST['username'], $_POST['password'], $role);
        $this->redirect('auth/loginForm');
    }
}
