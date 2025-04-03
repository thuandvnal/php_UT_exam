<?php

namespace App\Interfaces;

use App\Models\Order;

interface APIClient
{
    public function callAPI($orderId): array;
} 