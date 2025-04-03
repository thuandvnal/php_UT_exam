<?php

namespace App\Http\Controllers;

use App\Services\OrderProcessingService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $orderProcessingService;

    public function __construct(OrderProcessingService $orderProcessingService)
    {
        $this->orderProcessingService = $orderProcessingService;
    }

    public function processOrders(Request $request)
    {
        $userId = $request->input('user_id');
        
        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User ID is required'
            ], 400);
        }

        $result = $this->orderProcessingService->processOrders($userId);

        if ($result === false) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process orders'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }
} 