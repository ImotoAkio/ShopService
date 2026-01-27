<?php

namespace App\Controllers;

require_once __DIR__ . '/../Core/Database.php';

class LandingController
{
    public function index()
    {
        require __DIR__ . '/../../views/landing.php';
    }
}
