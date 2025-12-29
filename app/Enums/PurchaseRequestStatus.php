<?php

namespace App\Enums;

/**
 * Purchase request status allowed values.
 */
enum PurchaseRequestStatus: string
{
    case draft = 'draft';
    case submitted = 'submitted';
    case approved = 'approved';
    case rejected = 'rejected';
}
