<?php

namespace App\Enums;

/**
 * Project status allowed values.
 */
enum ProjectStatus: string
{
    case planning = 'planning';
    case in_progress = 'in_progress';
    case on_hold = 'on_hold';
    case completed = 'completed';
    case canceled = 'canceled';
}


