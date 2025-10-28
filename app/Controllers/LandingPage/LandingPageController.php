<?php

namespace App\Controllers\LandingPage;

use App\Controllers\BaseController;

class LandingPageController extends BaseController
{
    public function index()
    {
        return view('landingpage/index');
    }
}