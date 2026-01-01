<?php

namespace App\Enums;

/**
 * Contract status allowed values.
 */
enum ContractStatus: string
{
    case draft = 'draft';
    case active = 'active';
    case completed = 'completed';
    case canceled = 'canceled';
}

