<?php

namespace Codenteq\Iyzico\Enums;

enum PaymentIntervalEnum: string
{
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case YEARLY = 'YEARLY';
}
