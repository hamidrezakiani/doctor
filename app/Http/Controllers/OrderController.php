<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Order\Actions\CreateOrderAction;
use App\Domains\Order\Exceptions\InsufficientStockException;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;
use Throwable;

final class OrderController extends Controller
{
    /**
     * @throws Throwable
     */
    public function store(
        StoreOrderRequest $request,
        CreateOrderAction $action,
    ): JsonResponse
    {
        $order = $action->execute($request->toDto());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }
}
