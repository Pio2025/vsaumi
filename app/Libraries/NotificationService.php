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
}
