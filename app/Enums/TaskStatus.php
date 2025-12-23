<?php

namespace App\Enums;

/**
 * Task status allowed values.
 */
enum TaskStatus: string
{
    case backlog = 'backlog';
    case in_progress = 'in_progress';
    case in_review = 'in_review';
    case done = 'done';
    case canceled = 'canceled';
}

