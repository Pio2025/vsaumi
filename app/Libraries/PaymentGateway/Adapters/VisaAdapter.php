<?php

namespace App\Libraries\PaymentGateway\Adapters;

class VisaAdapter extends CardAdapter
{
    protected string $cardNetwork = 'visa';
}
