<?php

namespace App\Libraries\PaymentGateway\Adapters;

class MastercardAdapter extends CardAdapter
{
    protected string $cardNetwork = 'mastercard';
}
