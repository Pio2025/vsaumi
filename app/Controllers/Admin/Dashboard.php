<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\PaymentGateway\Services\PayoutService;
use App\Libraries\PaymentGateway\Services\SettlementService;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use App\Models\MerchantPayoutAccountModel;
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
            'pending_merchants' => $merchants->where('status', 'pending')->countAllResults(),
            'active_merchants'  => $merchants->where('status', 'active')->countAllResults(),
            'total_merchants'   => $merchants->countAllResults(),
            'captured_count'    => $transactions->where('status', 'captured')->countAllResults(),
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

    public function viewMerchant(int $id)
    {
        $merchant = model(MerchantModel::class)->find($id);

        if ($merchant === null) {
            return redirect()->to('admin/merchants')->with('error', 'Merchant not found.');
        }

        $applicationModel  = model(ApplicationModel::class);
        $subscriptionModel = model(SubscriptionModel::class);

        $applications = $applicationModel->allForMerchant($id);

        foreach ($applications as &$application) {
            $application['latest_subscription']     = $subscriptionModel->latestForApplication($application['id']);
            $application['has_active_subscription']  = $applicationModel->isSubscriptionActive($application);
        }
        unset($application);

        $otherSubscriptions = array_values(array_filter(
            $subscriptionModel->allForMerchant($id),
            static fn (array $sub): bool => $sub['status'] !== 'active' || strtotime($sub['expires_at']) <= time()
        ));

        return view('admin/merchant_view', [
            'pageTitle'          => $merchant['business_name'],
            'merchant'           => $merchant,
            'applications'       => $applications,
            'otherSubscriptions' => $otherSubscriptions,
            'payoutAccount'      => model(MerchantPayoutAccountModel::class)->forMerchant($id),
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

        $newStatus = $this->request->getPost('status');

        if ($newStatus === 'active' && $merchant['status'] !== 'active' && ! model(MerchantPayoutAccountModel::class)->hasPayoutInfo($id)) {
            return redirect()->back()->withInput()->with('error', 'This merchant has not provided payout details yet — cannot activate.');
        }

        $merchants->update($id, [
            'business_name' => $this->request->getPost('business_name'),
            'contact_email' => $this->request->getPost('contact_email'),
            'contact_phone' => $this->request->getPost('contact_phone') ?: null,
            'status'        => $newStatus,
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

    public function payoutsRunPreview()
    {
        $transactions   = model(TransactionModel::class);
        $merchantModel  = model(MerchantModel::class);
        $merchantIds    = $transactions->merchantIdsWithUnpaidSettlement();

        $rows = [];

        foreach ($merchantIds as $merchantId) {
            $merchantTransactions = $transactions->unpaidSettledForMerchant($merchantId);
            $totalAmount          = array_sum(array_column($merchantTransactions, 'amount'));
            $totalFees            = array_sum(array_column($merchantTransactions, 'fee_amount'));
            $merchant             = $merchantModel->find($merchantId);

            $rows[] = [
                'merchant_id'       => $merchantId,
                'business_name'     => $merchant['business_name'] ?? 'Unknown',
                'transaction_count' => count($merchantTransactions),
                'total_amount'      => $totalAmount,
                'total_fees'        => $totalFees,
                'net_amount'        => $totalAmount - $totalFees,
            ];
        }

        return view('admin/payouts_run_preview', [
            'pageTitle' => 'Run Payout Batch',
            'rows'      => $rows,
        ]);
    }

    public function runPayouts()
    {
        $result = (new PayoutService())->processPayouts();

        return redirect()->to('admin/payouts')->with('success', count($result) . ' payout batch(es) created.');
    }
}
