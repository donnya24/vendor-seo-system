<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AreasModel;

class Areas extends BaseController
{
    public function index()
    {
        $items = (new AreasModel())->orderBy('id','DESC')->findAll();
        return view('admin/areas/index', ['page'=>'Areas','items'=>$items]);
    }
}
