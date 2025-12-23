<?php

namespace App\Enums;

/**
 * Task priority allowed values.
 */
enum TaskPriority: string
{
    case low = 'low';
    case medium = 'medium';
    case high = 'high';
    case urgent = 'urgent';
}

