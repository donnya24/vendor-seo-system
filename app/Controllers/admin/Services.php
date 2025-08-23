<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ServicesModel;

class Services extends BaseController
{
    public function index()
    {
        $items = (new ServicesModel())->orderBy('id','DESC')->findAll();
        return view('admin/services/index', ['page'=>'Services','items'=>$items]);
    }
}
