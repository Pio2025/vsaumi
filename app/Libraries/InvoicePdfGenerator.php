<?php

namespace App\Libraries;

use TCPDF;

class InvoicePdfGenerator
{
    public static function generate(array $merchant, array $application, array $subscription): string
    {
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        $pdf->SetCreator('VSaumi');
        $pdf->SetAuthor('VSaumi');
        $invoiceNumber = 'INV-' . str_pad((string) $subscription['id'], 6, '0', STR_PAD_LEFT);
        $pdf->SetTitle($invoiceNumber);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(18, 18, 18);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        $statusLabel = ucfirst($subscription['status']);
        $amount      = number_format((float) $subscription['amount'], 2);

        $logoPath   = FCPATH . 'assets/logo/logo_small.png';
        $logoWidth  = 32;
        $logoHeight = $logoWidth * (80 / 406);
        $logoCell   = '&nbsp;';

        if (is_file($logoPath)) {
            $pdf->Image($logoPath, 18, 15, $logoWidth, $logoHeight, 'PNG', '', 'T', false, 300);

            $pdf->SetXY(18, 15 + $logoHeight + 2);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(102, 102, 102);
            $pdf->Cell(0, 4, 'Payment Gateway Services', 0, 0, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetXY(18, 18);
        } else {
            $logoCell = '<h1 style="font-size:20px;margin:0;">VSaumi</h1><span style="color:#666;">Payment Gateway Services</span>';
        }

        $html = '
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="50%">' . $logoCell . '</td>
                    <td width="50%" style="text-align:right;">
                        <h2 style="font-size:16px;margin:0;">INVOICE</h2>
                        <span style="color:#666;">' . esc($invoiceNumber) . '</span></td>
                </tr>
            </table>
            <br>
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="50%">
                        <span style="color:#666;">Billed to</span><br>
                        <strong>' . esc($merchant['business_name']) . '</strong><br>
                        ' . esc($merchant['contact_email']) . '<br>
                        ' . ($merchant['business_address'] ? nl2br(esc($merchant['business_address'])) . '<br>' : '') . '
                    </td>
                    <td width="50%" style="text-align:right;">
                        <span style="color:#666;">Application</span><br>
                        <strong>' . esc($application['name']) . '</strong><br>
                        <span style="color:#666;">Issued</span> ' . esc($subscription['started_at']) . '<br>
                        <span style="color:#666;">Status</span> ' . esc($statusLabel) . '
                    </td>
                </tr>
            </table>
            <br><br>
            <table cellpadding="6" cellspacing="0" width="100%" border="0">
                <tr style="background-color:#f1f1f1;">
                    <th style="text-align:left;border-bottom:1px solid #ddd;">Description</th>
                    <th style="text-align:left;border-bottom:1px solid #ddd;">Period</th>
                    <th style="text-align:right;border-bottom:1px solid #ddd;">Amount</th>
                </tr>
                <tr>
                    <td style="border-bottom:1px solid #eee;">' . esc(ucfirst($subscription['plan'])) . ' plan subscription</td>
                    <td style="border-bottom:1px solid #eee;">' . esc($subscription['started_at']) . ' - ' . esc($subscription['expires_at']) . '</td>
                    <td style="text-align:right;border-bottom:1px solid #eee;">$' . esc($amount) . '</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:right;"><strong>Total</strong></td>
                    <td style="text-align:right;"><strong>$' . esc($amount) . '</strong></td>
                </tr>
            </table>
            <br><br>
            <span style="color:#999;font-size:8px;">This is a simulated invoice generated in a demo environment. No real payment was processed.</span>
        ';

        $pdf->writeHTML($html, true, false, true, false, '');

        return $pdf->Output($invoiceNumber . '.pdf', 'S');
    }
}
