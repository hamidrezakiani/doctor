<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Order\Actions\CreateOrderAction;
use App\Domains\Order\Actions\ListOrdersAction;
use App\Domains\Order\Actions\ShipOrderAction;
use App\Http\Requests\ListOrdersRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
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
    ): JsonResponse {
        $order = $action->execute($request->toDto());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function index(
        ListOrdersRequest $request,
        ListOrdersAction $action,
    ): JsonResponse {
        $orders = $action->execute(
            status: $request->status(),
            perPage: $request->perPage(),
        );

        return OrderResource::collection($orders)->response();
    }

    public function ship(
        Order $order,
        ShipOrderAction $action,
    ): JsonResponse {
        $order = $action->execute($order);

        return (new OrderResource($order))->response()->setStatusCode(200);
    }
}
