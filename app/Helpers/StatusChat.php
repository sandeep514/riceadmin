<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;
use App\ChatStatus;

class StatusChat
{
    public static function getStatus()
    {
        return ChatStatus::first()->status;
    }
}