<?php

namespace App\Enums;

/**
 * Expense status allowed values.
 */
enum ExpenseStatus: string
{
    case draft = 'draft';
    case approved = 'approved';
}

