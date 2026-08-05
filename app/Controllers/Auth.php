<?php

namespace App\Controllers;

use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;

class Auth extends BaseController
{
    public function signupForm()
    {
        if (session()->get('merchant_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/signup', ['pageTitle' => 'Sign Up']);
    }

    public function signup()
    {
        $rules = [
            'business_name' => 'required|min_length[2]|max_length[150]|is_unique[merchants.business_name]',
            'contact_email' => 'required|valid_email|is_unique[merchants.contact_email]',
            'contact_phone' => 'permit_empty|max_length[30]|is_unique[merchants.contact_phone]',
            'password'      => 'required|min_length[8]',
            'app_name'      => 'required|min_length[2]|max_length[150]',
            'website_url'   => 'permit_empty|valid_url_strict|max_length[255]',
        ];

        $messages = [
            'business_name' => [
                'is_unique' => 'This business name is already registered. Please choose a different name.',
            ],
            'contact_email' => [
                'is_unique' => 'This email address is already registered to a business. Please log in or use a different email.',
            ],
            'contact_phone' => [
                'is_unique' => 'This phone number is already registered to a business. Please use a different phone number.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $merchants    = model(MerchantModel::class);
        $applications = model(ApplicationModel::class);
        $plainSecret  = SecurityHelper::generateSecret();
        $apiKey       = SecurityHelper::generateApiKey();

        $db = db_connect();
        $db->transStart();

        $merchantId = $merchants->insert([
            'business_name' => $this->request->getPost('business_name'),
            'contact_email' => $this->request->getPost('contact_email'),
            'contact_phone' => $this->request->getPost('contact_phone') ?: null,
            'password_hash' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'        => 'pending',
        ], true);

        $applicationId = $applications->insert([
            'merchant_id'           => $merchantId,
            'name'                  => $this->request->getPost('app_name'),
            'website_url'           => $this->request->getPost('website_url') ?: null,
            'status'                => 'active',
            'api_key'               => $apiKey,
            'api_secret_encrypted'  => SecurityHelper::encryptSecret($plainSecret),
        ], true);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Could not create your account. Please try again.');
        }

        $merchant = $merchants->find($merchantId);

        session()->set([
            'merchant_id'   => $merchant['id'],
            'merchant_name' => $merchant['business_name'],
        ]);

        session()->setFlashdata('new_application_credentials', [
            'application_id' => $applicationId,
            'name'           => $this->request->getPost('app_name'),
            'api_key'        => $apiKey,
            'api_secret'     => $plainSecret,
        ]);

        return redirect()->to('/dashboard')->with('success', 'Account created — an admin needs to approve it before you can go live.');
    }

    public function loginForm()
    {
        if (session()->get('merchant_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login', ['pageTitle' => 'Log In']);
    }

    public function login()
    {
        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $merchants = model(MerchantModel::class);
        $merchant  = $merchants->findByEmail((string) $email);

        if ($merchant === null || ! $merchants->verifyPassword($merchant, (string) $password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        session()->set([
            'merchant_id'   => $merchant['id'],
            'merchant_name' => $merchant['business_name'],
        ]);

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        session()->remove(['merchant_id', 'merchant_name']);

        return redirect()->to('/')->with('success', 'You have been logged out.');
    }
}
