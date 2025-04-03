<?php

namespace App\Interfaces;

use App\Models\Order;

interface DatabaseService
{
    public function getOrdersByUser($userId): array;
    public function updateOrderStatus($orderId, $status, $priority): bool;
} 