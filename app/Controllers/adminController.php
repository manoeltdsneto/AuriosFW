<?php

use Core\authMiddleware;

class adminController extends \Core\controller
{
    public function __construct()
    {
        authMiddleware::requireRole('admin');
    }

    public function dashboard(): void
    {
        $this->render('admin/dashboard');
    }
}
