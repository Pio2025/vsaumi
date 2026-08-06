<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\MerchantPayoutAccountModel;
use App\Models\PayoutProviderModel;

class SettingsController extends BaseController
{
    protected function currentMerchant(): array
    {
        return model(MerchantModel::class)->find(session()->get('merchant_id'));
    }

    public function index()
    {
        $merchant = $this->currentMerchant();

        return view('dashboard/settings', [
            'pageTitle'    => 'Settings',
            'merchant'     => $merchant,
            'payoutAccount' => model(MerchantPayoutAccountModel::class)->forMerchant($merchant['id']),
            'banks'        => model(PayoutProviderModel::class)->banks(),
            'wallets'      => model(PayoutProviderModel::class)->digitalWallets(),
        ]);
    }

    public function update()
    {
        $merchant = $this->currentMerchant();

        $rules = [
            'business_address'   => 'required|min_length[5]|max_length[500]',
            'payout_provider_id' => 'required|is_natural_no_zero',
            'account_number'     => 'required|max_length[50]',
            'account_name'       => 'required|max_length[150]',
        ];

        $messages = [
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

        model(MerchantModel::class)->update($merchant['id'], [
            'business_address' => $this->request->getPost('business_address'),
        ]);

        model(MerchantPayoutAccountModel::class)->upsertForMerchant($merchant['id'], [
            'payout_provider_id' => $payoutProvider['id'],
            'account_number'     => $this->request->getPost('account_number'),
            'account_name'       => $this->request->getPost('account_name'),
            'account_type'       => $payoutProvider['type'] === 'bank' ? $this->request->getPost('account_type') : null,
        ]);

        return redirect()->to('dashboard/settings')->with('success', 'Settings updated.');
    }
}
