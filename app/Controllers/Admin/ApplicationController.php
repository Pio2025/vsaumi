<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
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

    public function index()
    {
        $applicationModel  = model(ApplicationModel::class);
        $subscriptionModel = model(SubscriptionModel::class);

        $applications = $applicationModel->allWithMerchant();

        foreach ($applications as &$application) {
            $application['latest_subscription']    = $subscriptionModel->latestForApplication($application['id']);
            $application['has_active_subscription'] = $applicationModel->isSubscriptionActive($application);
        }
        unset($application);

        return view('admin/applications', [
            'pageTitle'    => 'Applications',
            'applications' => $applications,
            'plans'        => $this->plans,
        ]);
    }

    public function view(int $id)
    {
        $applicationModel = model(ApplicationModel::class);
        $application       = $applicationModel->find($id);

        if ($application === null) {
            return redirect()->to('admin/applications')->with('error', 'Application not found.');
        }

        $merchant = model(MerchantModel::class)->find($application['merchant_id']);

        $subscriptionModel = model(SubscriptionModel::class);
        $subscriptions      = $subscriptionModel->allForApplication($id);
        $hasActive           = $applicationModel->isSubscriptionActive($application);

        return view('admin/application_view', [
            'pageTitle'               => $application['name'],
            'application'             => $application,
            'merchant'                => $merchant,
            'subscriptions'           => $subscriptions,
            'hasActive'               => $hasActive,
            'plans'                   => $this->plans,
            'regeneratedCredentials'  => session()->getFlashdata('regenerated_credentials'),
        ]);
    }

    public function editForm(int $id)
    {
        $applicationModel = model(ApplicationModel::class);
        $application       = $applicationModel->find($id);

        if ($application === null) {
            return redirect()->to('admin/applications')->with('error', 'Application not found.');
        }

        $merchant = model(MerchantModel::class)->find($application['merchant_id']);

        return view('admin/application_edit', [
            'pageTitle'   => 'Edit Application',
            'application' => $application,
            'merchant'    => $merchant,
        ]);
    }

    public function update(int $id)
    {
        $applicationModel = model(ApplicationModel::class);
        $application       = $applicationModel->find($id);

        if ($application === null) {
            return redirect()->to('admin/applications')->with('error', 'Application not found.');
        }

        $rules = [
            'name'        => 'required|min_length[2]|max_length[150]',
            'website_url' => 'permit_empty|valid_url_strict|max_length[255]',
            'status'      => 'required|in_list[active,suspended]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $name = $this->request->getPost('name');

        if ($applicationModel->where('merchant_id', $application['merchant_id'])
            ->where('name', $name)
            ->where('id !=', $id)
            ->countAllResults() > 0) {
            return redirect()->back()->withInput()->with('error', 'This merchant already has another application with that name.');
        }

        $updateData = [
            'name'        => $name,
            'website_url' => $this->request->getPost('website_url') ?: null,
            'status'      => $this->request->getPost('status'),
        ];

        $successMessage = 'Application updated.';

        if ($this->request->getPost('regenerate_api_key')) {
            $plainSecret = SecurityHelper::generateSecret();
            $apiKey      = SecurityHelper::generateApiKey();

            $updateData['api_key']              = $apiKey;
            $updateData['api_secret_encrypted'] = SecurityHelper::encryptSecret($plainSecret);

            session()->setFlashdata('regenerated_credentials', [
                'api_key'    => $apiKey,
                'api_secret' => $plainSecret,
            ]);

            $successMessage = 'Application updated and API key regenerated — the old key no longer works.';
        }

        $applicationModel->update($id, $updateData);

        return redirect()->to('admin/applications/' . $id)->with('success', $successMessage);
    }

    public function activatePlan(int $id)
    {
        $applicationModel = model(ApplicationModel::class);
        $application       = $applicationModel->find($id);

        if ($application === null) {
            return redirect()->to('admin/applications')->with('error', 'Application not found.');
        }

        if ($applicationModel->isSubscriptionActive($application)) {
            return redirect()->to('admin/applications/' . $id)->with('error', 'This application already has an active subscription.');
        }

        $plan = $this->request->getPost('plan');

        if (! isset($this->plans[$plan])) {
            return redirect()->to('admin/applications/' . $id)->with('error', 'Please choose a valid plan.');
        }

        $merchant = model(MerchantModel::class)->find($application['merchant_id']);

        if ($merchant['status'] !== 'active' && ! model(MerchantPayoutAccountModel::class)->hasPayoutInfo($merchant['id'])) {
            return redirect()->to('admin/applications/' . $id)->with('error', 'This merchant has not provided payout details yet — cannot activate a plan.');
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

        return redirect()->to('admin/applications/' . $id)->with('success', "Activated the {$this->plans[$plan]['label']} plan for \"{$application['name']}\".");
    }

    public function cancelSubscription(int $id)
    {
        $applicationModel = model(ApplicationModel::class);
        $application       = $applicationModel->find($id);

        if ($application === null) {
            return redirect()->to('admin/applications')->with('error', 'Application not found.');
        }

        $subscriptionModel = model(SubscriptionModel::class);
        $latest             = $subscriptionModel->latestForApplication($id);

        if ($latest === null || ! $applicationModel->isSubscriptionActive($application)) {
            return redirect()->to('admin/applications/' . $id)->with('error', 'This application has no active subscription to cancel.');
        }

        $subscriptionModel->update($latest['id'], ['status' => 'canceled']);

        return redirect()->to('admin/applications/' . $id)->with('success', "Subscription canceled — \"{$application['name']}\"'s API access is now on hold.");
    }
}
