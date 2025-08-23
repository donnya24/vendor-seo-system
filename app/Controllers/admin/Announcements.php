<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnnouncementsModel;

class Announcements extends BaseController
{
    public function index()
    {
        $rows = (new AnnouncementsModel())->orderBy('id','DESC')->findAll();
        return view('admin/announcements/index', ['page'=>'Announcements','rows'=>$rows]);
    }

    public function create()
    {
        return view('admin/announcements/create', ['page'=>'Announcements']);
    }

    public function store()
    {
        (new AnnouncementsModel())->insert([
            'title'        => $this->request->getPost('title'),
            'content'      => $this->request->getPost('content'),
            'audience'     => $this->request->getPost('audience') ?: 'all',
            'publish_date' => $this->request->getPost('publish_date'),
            'expire_date'  => $this->request->getPost('expire_date'),
            'is_pinned'    => $this->request->getPost('is_pinned') ? 1 : 0,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('admin/announcements'))->with('success','Announcement created.');
    }

    public function edit($id)
    {
        $item = (new AnnouncementsModel())->find($id);
        return view('admin/announcements/edit', ['page'=>'Announcements','item'=>$item]);
    }

    public function update($id)
    {
        (new AnnouncementsModel())->update($id, [
            'title'        => $this->request->getPost('title'),
            'content'      => $this->request->getPost('content'),
            'audience'     => $this->request->getPost('audience') ?: 'all',
            'publish_date' => $this->request->getPost('publish_date'),
            'expire_date'  => $this->request->getPost('expire_date'),
            'is_pinned'    => $this->request->getPost('is_pinned') ? 1 : 0,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        return redirect()->to(site_url('admin/announcements'))->with('success','Announcement updated.');
    }

    public function delete($id)
    {
        (new AnnouncementsModel())->delete($id);
        return redirect()->back()->with('success','Announcement deleted.');
    }
}
