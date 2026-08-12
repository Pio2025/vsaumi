<?php

namespace App\Libraries;

use TCPDF;

/**
 * Renders a disbursement batch as a pay-clerk run sheet: grouped first by
 * payment mode (bank vs digital wallet), then by merchant within each mode,
 * showing each merchant's items, their subtotal, and the account details
 * needed to actually move the money.
 */
class DisbursementReportPdfGenerator
{
    private const MODE_LABELS = [
        'bank'           => 'Bank Transfer',
        'digital_wallet' => 'Digital Wallet',
    ];

    public static function generate(array $batch, array $items): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('VSaumi');
        $pdf->SetAuthor('VSaumi');
        $pdf->SetTitle($batch['reference']);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(18, 18, 18);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        $grandTotal = array_sum(array_column($items, 'amount'));

        $html = '
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="50%"><h1 style="font-size:20px;margin:0;">VSaumi</h1><span style="color:#666;">Disbursement Run Sheet</span></td>
                    <td width="50%" style="text-align:right;">
                        <h2 style="font-size:16px;margin:0;">' . esc($batch['reference']) . '</h2>
                        <span style="color:#666;">Generated ' . esc(date('Y-m-d H:i')) . '</span><br>
                        <span style="color:#666;">' . count($items) . ' item(s), total $' . esc(number_format((float) $grandTotal, 2)) . '</span>
                    </td>
                </tr>
            </table>
            <br>
        ';

        foreach (self::groupByModeThenMerchant($items) as $mode => $merchantGroups) {
            $html .= '<h3 style="font-size:13px;background-color:#f1f1f1;padding:5px 8px;margin:14px 0 6px;">' . esc(self::MODE_LABELS[$mode] ?? ucfirst($mode)) . '</h3>';

            foreach ($merchantGroups as $merchantItems) {
                $first          = $merchantItems[0];
                $merchantTotal  = array_sum(array_column($merchantItems, 'amount'));
                $bsb            = $first['bsb_code'] ? ' &nbsp; BSB ' . esc($first['bsb_code']) : '';

                $html .= '
                    <table cellpadding="4" cellspacing="0" width="100%" border="0" style="margin-bottom:2px;">
                        <tr>
                            <td width="55%">
                                <strong>' . esc($first['business_name']) . '</strong><br>
                                <span style="color:#666;font-size:8px;">' . esc($first['contact_email']) . '</span>
                            </td>
                            <td width="45%" style="text-align:right;">
                                <span style="color:#666;font-size:8px;">' . esc($first['provider_name']) . $bsb . '</span><br>
                                ' . esc($first['account_name']) . ' &mdash; ' . esc($first['account_number']) . '
                            </td>
                        </tr>
                    </table>
                    <table cellpadding="4" cellspacing="0" width="100%" border="0">
                        <tr style="background-color:#f8f8f8;">
                            <th style="text-align:left;border-bottom:1px solid #ddd;font-size:8px;color:#666;">Payout</th>
                            <th style="text-align:left;border-bottom:1px solid #ddd;font-size:8px;color:#666;">Payout date</th>
                            <th style="text-align:right;border-bottom:1px solid #ddd;font-size:8px;color:#666;">Amount</th>
                        </tr>
                ';

                foreach ($merchantItems as $item) {
                    $html .= '
                        <tr>
                            <td style="border-bottom:1px solid #eee;">Payout #' . esc($item['payout_id']) . '</td>
                            <td style="border-bottom:1px solid #eee;">' . esc($item['payout_date']) . '</td>
                            <td style="text-align:right;border-bottom:1px solid #eee;">$' . esc(number_format((float) $item['amount'], 2)) . '</td>
                        </tr>
                    ';
                }

                $html .= '
                        <tr>
                            <td colspan="2" style="text-align:right;"><strong>Merchant total</strong></td>
                            <td style="text-align:right;"><strong>$' . esc(number_format((float) $merchantTotal, 2)) . '</strong></td>
                        </tr>
                    </table>
                    <br>
                ';
            }
        }

        $html .= '
            <table cellpadding="6" cellspacing="0" width="100%" border="0" style="margin-top:8px;">
                <tr style="background-color:#f1f1f1;">
                    <td style="text-align:right;"><strong>Batch total</strong></td>
                    <td width="25%" style="text-align:right;"><strong>$' . esc(number_format((float) $grandTotal, 2)) . '</strong></td>
                </tr>
            </table>
            <br><br>
            <span style="color:#999;font-size:8px;">This run sheet is generated in a demo environment. No real funds transfer is triggered by this document.</span>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output($batch['reference'] . '.pdf', 'S');
    }

    /**
     * @return array<string, array<string, list<array<string, mixed>>>>
     */
    private static function groupByModeThenMerchant(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $grouped[$item['payment_type']][$item['business_name']][] = $item;
        }

        return $grouped;
    }
}
