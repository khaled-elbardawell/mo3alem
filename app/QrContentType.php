<?php

namespace App;

enum QrContentType: string
{
    case Url = 'url';
    case Text = 'text';
    case Wifi = 'wifi';
}
