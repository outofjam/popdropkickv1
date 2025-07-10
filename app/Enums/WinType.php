<?php

namespace App\Enums;

enum WinType: string
{
    case PINFALL = 'pinfall';
    case SUBMISSION = 'submission';
    case DQ = 'dq';
    case COUNTOUT = 'countout';
    case FORFEIT = 'forfeit';
    case VACATED = 'vacated';
    case AWARDED = 'awarded';
    case OTHER = 'other';
}
