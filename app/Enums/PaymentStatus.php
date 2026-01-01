<?php

namespace App\Enums;

/**
 * Payment status allowed values.
 */
enum PaymentStatus: string
{
    case pending = 'pending';
    case paid = 'paid';
    case canceled = 'canceled';
    case overdue = 'overdue';
}

