<?php

namespace App\Controllers;

use App\Libraries\NotificationService;
use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use App\Models\MerchantPayoutAccountModel;
use App\Models\PayoutProviderModel;

class Auth extends BaseController
{
    public function signupForm()
    {
        if (session()->get('merchant_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/signup', [
            'pageTitle' => 'Sign Up',
            'banks'     => model(PayoutProviderModel::class)->banks(),
            'wallets'   => model(PayoutProviderModel::class)->digitalWallets(),
        ]);
    }

    public function signup()
    {
        $rules = [
            'business_name'    => 'required|min_length[2]|max_length[150]|is_unique[merchants.business_name]',
            'contact_email'    => 'required|valid_email|is_unique[merchants.contact_email]',
            'contact_phone'    => 'permit_empty|max_length[30]|is_unique[merchants.contact_phone]',
            'business_address' => 'required|min_length[5]|max_length[500]',
            'password'         => 'required|min_length[8]',
            'app_name'         => 'required|min_length[2]|max_length[150]',
            'website_url'      => 'permit_empty|valid_url_strict|max_length[255]',
            'payout_provider_id' => 'required|is_natural_no_zero',
            'account_number'     => 'required|max_length[50]',
            'account_name'       => 'required|max_length[150]',
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
            'payout_provider_id' => [
                'required' => 'Please choose how you want to receive your payments.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $payoutProvider = model(PayoutProviderModel::class)->find((int) $this->request->getPost('payout_provider_id'));

        if ($payoutProvider === null) {
            return redirect()->back()->withInput()->with('error', 'Please choose a valid payout method.');
        }

        if ($payoutProvider['type'] === 'bank' && ! in_array($this->request->getPost('account_type'), ['savings', 'checking'], true)) {
            return redirect()->back()->withInput()->with('error', 'Please choose an account type for your bank account.');
        }

        $merchants    = model(MerchantModel::class);
        $applications = model(ApplicationModel::class);
        $payoutAccounts = model(MerchantPayoutAccountModel::class);
        $plainSecret  = SecurityHelper::generateSecret();
        $apiKey       = SecurityHelper::generateApiKey();

        $db = db_connect();
        $db->transStart();

        $merchantId = $merchants->insert([
            'business_name'    => $this->request->getPost('business_name'),
            'contact_email'    => $this->request->getPost('contact_email'),
            'contact_phone'    => $this->request->getPost('contact_phone') ?: null,
            'business_address' => $this->request->getPost('business_address'),
            'password_hash'    => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'status'           => 'pending',
        ], true);

        $payoutAccounts->insert([
            'merchant_id'        => $merchantId,
            'payout_provider_id' => $payoutProvider['id'],
            'account_number'     => $this->request->getPost('account_number'),
            'account_name'       => $this->request->getPost('account_name'),
            'account_type'       => $payoutProvider['type'] === 'bank' ? $this->request->getPost('account_type') : null,
        ]);

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

        (new NotificationService())->sendMerchantRegistrationAcknowledgement($merchant);

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
