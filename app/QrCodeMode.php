<?php

namespace App;

enum QrCodeMode: string
{
    case Static = 'static';
    case Dynamic = 'dynamic';
}
