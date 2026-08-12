<?php

if (! function_exists('status_badge_class')) {
    function status_badge_class(string $status): string
    {
        $map = [
            'pending'    => 'kt-badge-warning',
            'approved'   => 'kt-badge-info',
            'active'     => 'kt-badge-success',
            'suspended'  => 'kt-badge-destructive',
            'captured'   => 'kt-badge-info',
            'settled'    => 'kt-badge-primary',
            'failed'     => 'kt-badge-destructive',
            'declined'   => 'kt-badge-destructive',
            'paid'       => 'kt-badge-success',
            'created'    => 'kt-badge-secondary',
            'processing' => 'kt-badge-secondary',
            'expired'    => 'kt-badge-destructive',
            'processed'  => 'kt-badge-success',
            'rejected'   => 'kt-badge-destructive',
            'voided'     => 'kt-badge-secondary',
        ];

        return $map[$status] ?? 'kt-badge-secondary';
    }
}
