<?php

use App\Services\OrderProcessingService;
use App\Interfaces\DatabaseService;
use App\Interfaces\APIClient;
use App\Models\Order;
use App\Exceptions\APIException;
use App\Exceptions\DatabaseException;

it('processes orders of type B with successful API call', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 2,
        'type' => 'B',
        'amount' => 80,
        'flag' => false,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(2, 'processed', 'low');
    $apiClientMock->shouldReceive('callAPI')->with(2)->andReturn(['status' => 'success', 'data' => 60]);

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result[0]->status)->toBe('processed');
});

it('handles API failure for orders of type B', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 3,
        'type' => 'B',
        'amount' => 50,
        'flag' => false,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(3, 'api_failure', 'low');
    $apiClientMock->shouldReceive('callAPI')->with(3)->andThrow(new APIException());

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result[0]->status)->toBe('api_failure');
});

it('processes orders of type C with flag set to true', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 4,
        'type' => 'C',
        'amount' => 100,
        'flag' => true,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(4, 'completed', 'low');

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result[0]->status)->toBe('completed');
});

it('processes orders of type C with flag set to false', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 5,
        'type' => 'C',
        'amount' => 100,
        'flag' => false,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(5, 'in_progress', 'low');

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result[0]->status)->toBe('in_progress');
});

it('handles unknown order type', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 7,
        'type' => 'X',
        'amount' => 100,
        'flag' => false,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(7, 'unknown_type', 'low');

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result[0]->status)->toBe('unknown_type');
});

it('handles database error during order retrieval', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andThrow(new DatabaseException());

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result)->toBe(false);
});

it('handles API error during order processing', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 10,
        'type' => 'B',
        'amount' => 50,
        'flag' => false,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(10, 'api_error', 'low');
    $apiClientMock->shouldReceive('callAPI')->with(10)->andReturn(['status' => 'error']);

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result[0]->status)->toBe('api_error');
});


// generate ra các case ngoại lê: biên trên, biên dưới, giá trị không hợp lệ

it('handles API exception during order processing', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 11,
        'type' => 'B',
        'amount' => 50,
        'flag' => false,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(11, 'api_error', 'low');
    $apiClientMock->shouldReceive('callAPI')->with(11)->andThrow(new APIException());

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result)->toBe(false);
});

it('handles database exception during order status update', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $apiClientMock = Mockery::mock(APIClient::class);

    $order = new Order([
        'id' => 12,
        'type' => 'B',
        'amount' => 50,
        'flag' => false,
        'status' => 'new',
        'priority' => 'low',
    ]);

    $dbServiceMock->shouldReceive('getOrdersByUser')->with(123)->andReturn([$order]);
    $dbServiceMock->shouldReceive('updateOrderStatus')->with(12, 'db_error', 'low')->andThrow(new DatabaseException());

    $service = new OrderProcessingService($dbServiceMock, $apiClientMock);
    $result = $service->processOrders(123);

    expect($result)->toBe(false);
});

