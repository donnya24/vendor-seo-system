<?php 
namespace App\Controllers\Landpage;

use App\Controllers\BaseController;

class Landpage extends BaseController
{
    public function index()
    {
        return view('Landpage/Landpage'); // Menampilkan view di view/Landpage/Landpage.php
    }
}