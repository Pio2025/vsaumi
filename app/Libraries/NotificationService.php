<?php

namespace App\Libraries;

class NotificationService
{
    /**
     * Notifies a merchant that their withdrawal request has been approved and
     * is being processed. Never throws — a broken mail transport must not
     * block the admin approval flow.
     */
    public function sendWithdrawalProcessing(array $merchant, array $withdrawalRequest): bool
    {
        try {
            $email = service('email');

            $email->setTo($merchant['contact_email']);
            $email->setSubject('Your VSaumi withdrawal request is being processed');
            $email->setMessage(
                "Hi {$merchant['business_name']},\n\n" .
                'Your withdrawal request for FJD ' . number_format((float) $withdrawalRequest['amount'], 2) . " has been approved and is now being processed.\n" .
                "You should receive the payment within a maximum of 3 working days.\n\n" .
                "— VSaumi"
            );

            return $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to send withdrawal-processing email: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Notifies a merchant that their withdrawal request was rejected, why,
     * and what happened to the underlying funds. Never throws — a broken
     * mail transport must not block the admin rejection flow.
     */
    public function sendWithdrawalRejected(array $merchant, array $withdrawalRequest, string $reason, string $disposition): bool
    {
        try {
            $email = service('email');

            $fundsNote = $disposition === 'forfeited'
                ? 'The funds behind this request have been removed and will not appear in your available balance.'
                : 'The funds behind this request have been returned to your available balance — you can request a withdrawal again at any time.';

            $email->setTo($merchant['contact_email']);
            $email->setSubject('Your VSaumi withdrawal request was rejected');
            $email->setMessage(
                "Hi {$merchant['business_name']},\n\n" .
                'Your withdrawal request for FJD ' . number_format((float) $withdrawalRequest['amount'], 2) . " was rejected.\n\n" .
                "Reason: {$reason}\n\n" .
                "{$fundsNote}\n\n" .
                "— VSaumi"
            );

            return $email->send();
        } catch (\Throwable $e) {
            log_message('error', 'Failed to send withdrawal-rejected email: ' . $e->getMessage());

            return false;
        }
    }
}
