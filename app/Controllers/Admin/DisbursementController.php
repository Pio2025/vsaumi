<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\DisbursementReportPdfGenerator;
use App\Models\DisbursementBatchModel;
use App\Models\DisbursementItemModel;
use App\Models\PayoutModel;

class DisbursementController extends BaseController
{
    public function index()
    {
        $batches = model(DisbursementBatchModel::class)->orderBy('id', 'DESC')->findAll();

        return view('admin/disbursements', [
            'pageTitle' => 'Disbursement Listing',
            'batches'   => $batches,
        ]);
    }

    public function runForm()
    {
        $eligible = model(PayoutModel::class)->eligibleForDisbursement();

        $groups = ['bank' => [], 'digital_wallet' => []];

        foreach ($eligible as $payout) {
            $type = $payout['provider_type'] ?? 'bank';
            $groups[$type][] = $payout;
        }

        return view('admin/disbursements_run', [
            'pageTitle' => 'Run Disbursement Batch',
            'groups'    => $groups,
        ]);
    }

    public function run()
    {
        $selectedIds = array_map('intval', (array) $this->request->getPost('payout_ids'));

        if ($selectedIds === []) {
            return redirect()->to('admin/disbursements/run')->with('error', 'Select at least one payout to include in the disbursement batch.');
        }

        $eligibleById = [];

        foreach (model(PayoutModel::class)->eligibleForDisbursement() as $payout) {
            $eligibleById[(int) $payout['id']] = $payout;
        }

        $selected = array_values(array_intersect_key($eligibleById, array_flip($selectedIds)));

        if ($selected === []) {
            return redirect()->to('admin/disbursements/run')->with('error', 'The selected payouts are no longer eligible — they may already be in another disbursement batch.');
        }

        $batchModel = model(DisbursementBatchModel::class);
        $itemModel  = model(DisbursementItemModel::class);

        $batchId = $batchModel->insert([
            'reference'    => 'PENDING',
            'total_amount' => array_sum(array_column($selected, 'net_amount')),
            'item_count'   => count($selected),
        ], true);

        $reference = 'DSB-' . str_pad((string) $batchId, 6, '0', STR_PAD_LEFT);
        $batchModel->update($batchId, ['reference' => $reference]);

        foreach ($selected as $payout) {
            $itemModel->insert([
                'disbursement_batch_id' => $batchId,
                'payout_id'             => $payout['id'],
                'merchant_id'           => $payout['merchant_id'],
                'payment_type'          => $payout['provider_type'] ?? 'bank',
                'provider_name'         => $payout['provider_name'] ?? 'Not on file',
                'account_name'          => $payout['account_name'] ?? $payout['business_name'],
                'account_number'        => $payout['account_number'] ?? '—',
                'bsb_code'              => $payout['provider_bsb_code'] ?? null,
                'amount'                => $payout['net_amount'],
                'created_at'            => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('admin/disbursements')->with('success', count($selected) . " payout(s) placed into disbursement batch {$reference}.");
    }

    public function report(int $id)
    {
        $batch = model(DisbursementBatchModel::class)->find($id);

        if ($batch === null) {
            return redirect()->to('admin/disbursements')->with('error', 'Disbursement batch not found.');
        }

        $items = model(DisbursementItemModel::class)->forBatch($id);
        $pdf   = DisbursementReportPdfGenerator::generate($batch, $items);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $batch['reference'] . '.pdf"')
            ->setBody($pdf);
    }
}
