<?php

namespace App\Enums;

/**
 * Phase status allowed values.
 */
enum PhaseStatus: string
{
    case draft = 'draft';
    case active = 'active';
    case archived = 'archived';
}

