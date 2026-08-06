<?php

namespace App\Controllers;

use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use App\Models\MerchantPayoutAccountModel;
use App\Models\SubscriptionModel;

class ApplicationController extends BaseController
{
    protected array $plans = [
        'starter'    => ['label' => 'Starter', 'price' => 29, 'note' => 'Up to 500 transactions/mo'],
        'growth'     => ['label' => 'Growth', 'price' => 79, 'note' => 'Up to 5,000 transactions/mo'],
        'enterprise' => ['label' => 'Enterprise', 'price' => 199, 'note' => 'Unlimited, priority support'],
    ];

    protected function currentMerchant(): array
    {
        return model(MerchantModel::class)->find(session()->get('merchant_id'));
    }

    public function index()
    {
        $merchant          = $this->currentMerchant();
        $applicationModel  = model(ApplicationModel::class);
        $subscriptionModel = model(SubscriptionModel::class);

        $applications = $applicationModel->allForMerchant($merchant['id']);

        foreach ($applications as &$application) {
            $application['latest_subscription']     = $subscriptionModel->latestForApplication($application['id']);
            $application['has_active_subscription']  = $applicationModel->isSubscriptionActive($application);
        }
        unset($application);

        return view('dashboard/applications', [
            'pageTitle'                  => 'Applications',
            'merchant'                   => $merchant,
            'applications'                => $applications,
            'plans'                      => $this->plans,
            'newApplicationCredentials'  => session()->getFlashdata('new_application_credentials'),
        ]);
    }

    public function newForm()
    {
        return view('dashboard/applications_new', [
            'pageTitle' => 'New Application',
            'merchant'  => $this->currentMerchant(),
        ]);
    }

    public function create()
    {
        $merchant = $this->currentMerchant();

        $rules = [
            'name'        => 'required|min_length[2]|max_length[150]',
            'website_url' => 'permit_empty|valid_url_strict|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $applicationModel = model(ApplicationModel::class);
        $name             = $this->request->getPost('name');

        if ($applicationModel->where('merchant_id', $merchant['id'])->where('name', $name)->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'You already have an application with that name.');
        }

        $plainSecret = SecurityHelper::generateSecret();
        $apiKey      = SecurityHelper::generateApiKey();

        $applicationId = $applicationModel->insert([
            'merchant_id'           => $merchant['id'],
            'name'                  => $name,
            'website_url'           => $this->request->getPost('website_url') ?: null,
            'status'                => 'active',
            'api_key'               => $apiKey,
            'api_secret_encrypted'  => SecurityHelper::encryptSecret($plainSecret),
        ], true);

        session()->setFlashdata('new_application_credentials', [
            'application_id' => $applicationId,
            'name'           => $name,
            'api_key'        => $apiKey,
            'api_secret'     => $plainSecret,
        ]);

        return redirect()->to('/dashboard/applications')->with('success', "Application \"{$name}\" created.");
    }

    public function subscribe(int $applicationId)
    {
        $merchant         = $this->currentMerchant();
        $applicationModel = model(ApplicationModel::class);
        $application      = $applicationModel->findForMerchant($merchant['id'], $applicationId);

        if ($application === null) {
            return redirect()->to('/dashboard/applications')->with('error', 'Application not found.');
        }

        if ($merchant['status'] === 'pending') {
            return redirect()->to('/dashboard/applications')->with('error', 'Your business must be approved by an admin before you can subscribe.');
        }

        if ($merchant['status'] !== 'active' && ! model(MerchantPayoutAccountModel::class)->hasPayoutInfo($merchant['id'])) {
            return redirect()->to('/dashboard/settings')->with('error', 'Add your payout details before you can activate your account.');
        }

        $plan = $this->request->getPost('plan');

        if (! isset($this->plans[$plan])) {
            return redirect()->to('/dashboard/applications')->with('error', 'Please choose a valid plan.');
        }

        if ($applicationModel->isSubscriptionActive($application)) {
            return redirect()->to('/dashboard/applications')->with('error', 'This application already has an active subscription.');
        }

        model(SubscriptionModel::class)->insert([
            'merchant_id'    => $merchant['id'],
            'application_id' => $application['id'],
            'plan'           => $plan,
            'amount'         => $this->plans[$plan]['price'],
            'status'         => 'active',
            'started_at'     => date('Y-m-d H:i:s'),
            'expires_at'     => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);

        if ($merchant['status'] !== 'active') {
            model(MerchantModel::class)->update($merchant['id'], ['status' => 'active']);
        }

        return redirect()->to('/dashboard/applications')->with('success', "Subscribed \"{$application['name']}\" to the {$this->plans[$plan]['label']} plan (simulated payment). Its API is now live for 30 days.");
    }
}
