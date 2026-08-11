<?php
/**
 * Standalone HTML email — no shared layout/Tailwind here, since email
 * clients don't load external stylesheets or support flex/grid. Keep
 * everything table-based with inline styles.
 */
$hasProduct = ! empty($transaction['product_name']);
$quantity   = $transaction['quantity'] !== null
    ? rtrim(rtrim(number_format((float) $transaction['quantity'], 2), '0'), '.')
    : null;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Receipt from <?= esc($merchant['business_name']) ?></title>
</head>
<body style="margin:0; padding:0; background-color:#f4f5f7; font-family:Arial, Helvetica, sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:24px 0;">
<tr>
<td align="center">
<table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; border:1px solid #e5e7eb;">
<tr>
<td style="background-color:#1f2937; padding:20px 32px;">
<span style="color:#ffffff; font-size:18px; font-weight:bold; letter-spacing:0.5px;">VSaumi</span>
</td>
</tr>
<tr>
<td style="padding:32px;">
<p style="margin:0 0 4px; font-size:13px; color:#6b7280;">Receipt from</p>
<p style="margin:0 0 20px; font-size:18px; color:#111827; font-weight:bold;"><?= esc($merchant['business_name']) ?></p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; margin-bottom:20px;">
<tr>
<td style="padding:14px 18px;">
<span style="font-size:13px; color:#15803d; font-weight:bold;">&#10003; Payment received</span>
</td>
</tr>
</table>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#111827;">
<tr>
<td style="padding:6px 0; color:#6b7280;">Reference</td>
<td style="padding:6px 0; text-align:right; font-family:monospace;"><?= esc($transaction['reference']) ?></td>
</tr>
<tr>
<td style="padding:6px 0; color:#6b7280;">Date</td>
<td style="padding:6px 0; text-align:right;"><?= esc($transaction['created_at']) ?></td>
</tr>
<tr>
<td style="padding:6px 0; color:#6b7280;">Payment method</td>
<td style="padding:6px 0; text-align:right;"><?= esc(strtoupper($transaction['payment_method'])) ?></td>
</tr>
<?php if (! empty($application['name'])): ?>
<tr>
<td style="padding:6px 0; color:#6b7280;">Application</td>
<td style="padding:6px 0; text-align:right;"><?= esc($application['name']) ?></td>
</tr>
<?php endif; ?>
</table>

<?php if ($hasProduct): ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px; color:#111827; margin-top:16px; border-top:1px solid #e5e7eb; padding-top:16px;">
<tr>
<td colspan="2" style="padding-bottom:8px; font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.5px;">Product / service</td>
</tr>
<tr>
<td style="padding:4px 0;"><?= esc($transaction['product_name']) ?><?= $quantity !== null ? ' &times; ' . esc($quantity) . ($transaction['unit_of_measure'] ? ' ' . esc($transaction['unit_of_measure']) : '') : '' ?></td>
<td style="padding:4px 0; text-align:right;"><?= $transaction['unit_price'] !== null ? esc(number_format((float) $transaction['unit_price'], 2)) . ' ' . esc($transaction['currency']) : '' ?></td>
</tr>
<?php if (! empty($transaction['product_description'])): ?>
<tr>
<td colspan="2" style="padding:4px 0; color:#6b7280; font-size:13px;"><?= esc($transaction['product_description']) ?></td>
</tr>
<?php endif; ?>
</table>
<?php endif; ?>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:20px; border-top:1px solid #e5e7eb; padding-top:16px;">
<tr>
<td style="font-size:15px; color:#111827; font-weight:bold;">Total paid</td>
<td style="font-size:20px; color:#111827; font-weight:bold; text-align:right;"><?= esc(number_format((float) $transaction['amount'], 2)) ?> <?= esc($transaction['currency']) ?></td>
</tr>
</table>
</td>
</tr>
<tr>
<td style="padding:20px 32px; background-color:#f9fafb; border-top:1px solid #e5e7eb;">
<p style="margin:0; font-size:12px; color:#9ca3af;">This is an automated receipt for a payment processed via VSaumi on behalf of <?= esc($merchant['business_name']) ?>. If you don't recognize this transaction, please contact the merchant directly.</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>
