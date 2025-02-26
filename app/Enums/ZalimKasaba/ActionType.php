<?php

namespace App\Enums\ZalimKasaba;

enum ActionType: string
{
    case ORDER = 'order';
    case KILL = 'kill';
    case HEAL = 'heal';
    case WATCH = 'watch';
    case SHOOT = 'shoot';
    case HAUNT = 'haunt';
    case INTERROGATE = 'interrogate';
}
