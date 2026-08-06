<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use App\Models\SubscriptionModel;

class ApplicationController extends BaseController
{
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
            'pageTitle'     => $application['name'],
            'application'   => $application,
            'merchant'      => $merchant,
            'subscriptions' => $subscriptions,
            'hasActive'     => $hasActive,
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

        $applicationModel->update($id, [
            'name'        => $name,
            'website_url' => $this->request->getPost('website_url') ?: null,
            'status'      => $this->request->getPost('status'),
        ]);

        return redirect()->to('admin/applications/' . $id)->with('success', 'Application updated.');
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
