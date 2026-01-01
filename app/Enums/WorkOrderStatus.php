<?php

namespace App\Enums;

/**
 * Work order status allowed values.
 */
enum WorkOrderStatus: string
{
    case draft = 'draft';
    case approved = 'approved';
    case completed = 'completed';
    case canceled = 'canceled';
}

