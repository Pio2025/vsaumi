<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\PaymentGateway\Services\PayoutService;
use App\Libraries\PaymentGateway\Services\SettlementService;
use App\Models\MerchantModel;
use App\Models\PayoutModel;
use App\Models\SubscriptionModel;
use App\Models\TransactionModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $merchants    = model(MerchantModel::class);
        $transactions = model(TransactionModel::class);

        $stats = [
            'pending_merchants' => $merchants->where('status', 'pending')->countAllResults(false),
            'active_merchants'  => $merchants->where('status', 'active')->countAllResults(false),
            'total_merchants'   => $merchants->countAllResults(),
            'captured_count'    => $transactions->where('status', 'captured')->countAllResults(false),
            'settled_unpaid'    => count($transactions->merchantIdsWithUnpaidSettlement()),
        ];

        $statusCounts = ['pending' => 0, 'approved' => 0, 'active' => 0, 'suspended' => 0];
        foreach ($merchants->select('status, COUNT(*) as count')->groupBy('status')->findAll() as $row) {
            $statusCounts[$row['status']] = (int) $row['count'];
        }

        $methodCounts = [];
        foreach ($transactions->select('payment_method, COUNT(*) as count')->groupBy('payment_method')->findAll() as $row) {
            $methodCounts[strtoupper($row['payment_method'])] = (int) $row['count'];
        }

        $volumeByDay = [];
        foreach ($transactions->select('DATE(created_at) as day, COUNT(*) as count, SUM(amount) as total')
            ->where('created_at >=', date('Y-m-d', strtotime('-13 days')))
            ->groupBy('DATE(created_at)')
            ->orderBy('day', 'ASC')
            ->findAll() as $row) {
            $volumeByDay[$row['day']] = ['count' => (int) $row['count'], 'total' => (float) $row['total']];
        }

        $dailySeries = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $dailySeries[] = [
                'day'   => date('M j', strtotime($day)),
                'count' => $volumeByDay[$day]['count'] ?? 0,
                'total' => $volumeByDay[$day]['total'] ?? 0,
            ];
        }

        return view('admin/dashboard', [
            'pageTitle'    => 'Admin Dashboard',
            'stats'        => $stats,
            'statusCounts' => $statusCounts,
            'methodCounts' => $methodCounts,
            'dailySeries'  => $dailySeries,
        ]);
    }

    public function merchants()
    {
        $merchants     = model(MerchantModel::class)->orderBy('id', 'DESC')->findAll();
        $subscriptions = model(SubscriptionModel::class);

        $latestPlans = [];
        foreach ($merchants as $merchant) {
            $latest = $subscriptions->latestForMerchant($merchant['id']);
            $latestPlans[$merchant['id']] = $latest['plan'] ?? null;
        }

        return view('admin/merchants', [
            'pageTitle'   => 'Merchants',
            'merchants'   => $merchants,
            'latestPlans' => $latestPlans,
        ]);
    }

    public function approveMerchant(int $id)
    {
        $merchants = model(MerchantModel::class);
        $merchant  = $merchants->find($id);

        if ($merchant === null || $merchant['status'] !== 'pending') {
            return redirect()->to('admin/merchants')->with('error', 'Merchant cannot be approved from its current status.');
        }

        $merchants->update($id, ['status' => 'approved']);

        return redirect()->to('admin/merchants')->with('success', "{$merchant['business_name']} approved — they can now subscribe to a plan.");
    }

    public function editMerchantForm(int $id)
    {
        $merchant = model(MerchantModel::class)->find($id);

        if ($merchant === null) {
            return redirect()->to('admin/merchants')->with('error', 'Merchant not found.');
        }

        return view('admin/merchant_edit', [
            'pageTitle' => 'Edit Merchant',
            'merchant'  => $merchant,
        ]);
    }

    public function updateMerchant(int $id)
    {
        $merchants = model(MerchantModel::class);
        $merchant  = $merchants->find($id);

        if ($merchant === null) {
            return redirect()->to('admin/merchants')->with('error', 'Merchant not found.');
        }

        $rules = [
            'business_name' => "required|min_length[2]|max_length[150]|is_unique[merchants.business_name,id,{$id}]",
            'contact_email' => "required|valid_email|is_unique[merchants.contact_email,id,{$id}]",
            'contact_phone' => "permit_empty|max_length[30]|is_unique[merchants.contact_phone,id,{$id}]",
            'status'        => 'required|in_list[pending,approved,active,suspended]',
        ];

        $messages = [
            'business_name' => [
                'is_unique' => 'This business name is already registered to another merchant.',
            ],
            'contact_email' => [
                'is_unique' => 'This email address is already registered to another merchant.',
            ],
            'contact_phone' => [
                'is_unique' => 'This phone number is already registered to another merchant.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->validator->getErrors()));
        }

        $merchants->update($id, [
            'business_name' => $this->request->getPost('business_name'),
            'contact_email' => $this->request->getPost('contact_email'),
            'contact_phone' => $this->request->getPost('contact_phone') ?: null,
            'status'        => $this->request->getPost('status'),
        ]);

        return redirect()->to('admin/merchants')->with('success', 'Merchant updated.');
    }

    public function deleteMerchant(int $id)
    {
        $merchants = model(MerchantModel::class);
        $merchant  = $merchants->find($id);

        if ($merchant === null) {
            return redirect()->to('admin/merchants')->with('error', 'Merchant not found.');
        }

        $merchants->delete($id);

        return redirect()->to('admin/merchants')->with('success', "{$merchant['business_name']} deleted, along with their transactions, payouts, and subscription history.");
    }

    public function runSettlement()
    {
        $result = (new SettlementService())->runBatch();

        return redirect()->to('admin')->with('success', "Settlement run: {$result['settled_count']} transaction(s) settled, total " . number_format($result['total_amount'], 2) . ' FJD.');
    }

    public function payouts()
    {
        $payouts = model(PayoutModel::class)->orderBy('id', 'DESC')->findAll();

        $merchantModel = model(MerchantModel::class);
        $merchantNames = [];
        foreach ($payouts as $payout) {
            if (! isset($merchantNames[$payout['merchant_id']])) {
                $merchant = $merchantModel->find($payout['merchant_id']);
                $merchantNames[$payout['merchant_id']] = $merchant['business_name'] ?? 'Unknown';
            }
        }

        return view('admin/payouts', [
            'pageTitle'     => 'Payouts',
            'payouts'       => $payouts,
            'merchantNames' => $merchantNames,
        ]);
    }

    public function runPayouts()
    {
        $result = (new PayoutService())->processPayouts();

        return redirect()->to('admin/payouts')->with('success', count($result) . ' payout batch(es) created.');
    }
}
