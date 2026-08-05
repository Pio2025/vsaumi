<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class Auth extends BaseController
{
    public function loginForm()
    {
        if (session()->get('admin_id')) {
            return redirect()->to('/admin');
        }

        return view('admin/login', ['pageTitle' => 'Admin Log In']);
    }

    public function login()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $admins = model(AdminModel::class);
        $admin  = $admins->findByEmail((string) $email);

        if ($admin === null || ! $admins->verifyPassword($admin, (string) $password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        session()->set([
            'admin_id'   => $admin['id'],
            'admin_name' => $admin['name'],
        ]);

        return redirect()->to('/admin');
    }

    public function logout()
    {
        session()->remove(['admin_id', 'admin_name']);

        return redirect()->to('/admin/login')->with('success', 'You have been logged out.');
    }
}
