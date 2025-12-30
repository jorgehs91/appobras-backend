<?php

namespace App\Enums;

/**
 * Purchase order status allowed values.
 */
enum PurchaseOrderStatus: string
{
    case pending = 'pending';
    case approved = 'approved';
    case completed = 'completed';
    case cancelled = 'cancelled';
}

