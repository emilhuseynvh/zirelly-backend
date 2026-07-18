<?php

namespace App\Enums;

enum PromocodeType: string
{
    case FirstOrder = 'first_order';
    case SingleUse = 'single_use';
    case Unlimited = 'unlimited';
}
