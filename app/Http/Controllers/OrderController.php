<?php

namespace App\Http\Controllers;

use App\DetailOrder;
use App\Events\NewOrderEvent;
use App\Helpers\Transformer;
use App\Http\Filters\OrderFilter;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrdersCollection;
use App\Order;
use App\Table;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Order not found response.
     *
     * @return  JsonResponse
     */
    private function notFoundResponse()
    {
        return Transformer::fail('Order not found.', null, 404);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Order::query();
            $query = OrderFilter::collection($request, $query);

            $limit = $request->get('limit', 15);
            $orders = (int) $limit > 0 ? $query->paginate($limit) : $query->get();

            return (new OrdersCollection($orders))
                    ->additional(
                        Transformer::meta(true, 'Success to get orders collection.')
                    );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get orders collection.');
        }
    }

    /**
    * Get new order.
    *
    * @return \Illuminate\Http\Response
    */
    public function newOrder()
    {
        try {
            $details_orders = DetailOrder::join('orders', 'detail_orders.order_id', 'orders.id')
                                            ->where('orders.status', 'N')
                                            ->whereNull('served_at')
                                            ->get();

            return Transformer::ok('Success to get orders collection.', $details_orders);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get orders collection.');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'table_number' => 'required|regex:/^\d+$/',
            'details' => 'required|array',
            'details.*.food_id' => 'required|regex:/^\d+$/',
            'details.*.qty' => 'required|regex:/^[1-9]+([0-9]+)?$/',
            'details.*.tips' => 'present|nullable|string|max:255',
        ]);

        try {
            $table = Table::where('number', $request->get('table_number'))->where('available', 'n')->first();
            if (!$table) {
                return Transformer::fail('Table is not found or not ready.', null, 400);
            }

            $order = Order::select('orders.*')
                            ->where('status', 'N')
                            ->join('tables', 'orders.table_id', 'tables.id')
                            ->where('tables.id', $table->id)
                            ->where('tables.available', 'N')
                            ->first();
            
            if (!$order) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'table_id' => $table->id,
                    'status' => 'N'
                ]);
            } else {
                $order->update([
                    'details_complete' => 'N',
                ]);
            }

            foreach ($request->get('details') as $detail) {
                $order->details()->create([
                    'food_id' => $detail['food_id'],
                    'qty' => $detail['qty'],
                    'tips' => $detail['tips'],
                ]);
            }

            event(new NewOrderEvent);

            return Transformer::ok(
                'Success to make an order.',
                ['order' => new OrderResource($order)],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to make an order.');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $order = Order::with('details')
                            ->whereId($id)
                            ->firstOrFail();

            return Transformer::ok(
                'Success to get order details.',
                ['order' => new OrderResource($order)]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get order details.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $order = Order::select('id')->whereId($id)->firstOrFail();

            $order->delete();

            return Transformer::ok('Success to delete order data.', );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete order data.');
        }
    }

    /**
     * Update served column at detail table
     *
     * @param   int  $id
     * @param   int  $detailId
     *
     * @return  JsonResponse
     */
    public function foodServed($id, $detailId)
    {
        try {
            $detailOrder = DetailOrder::select('id')
                                        ->where('order_id', $id)
                                        ->whereId($detailId)
                                        ->firstOrFail();

            $detailOrder->update([
                'served_at' => Carbon::now(),
            ]);

            // Check is order complete
            $non_served_foods = DetailOrder::where('order_id', $id)
                                            ->whereNull('served_at')
                                            ->join('orders', 'detail_orders.order_id', 'orders.id')
                                            ->where('orders.status', 'N')
                                            ->count();

            if ($non_served_foods == 0) {
                Order::whereId($id)->update([
                    'details_complete' => 'Y',
                ]);
            }

            return Transformer::ok('Success to update detail order data.', [
                'order' => new OrderResource(Order::findOrFail($id))
            ]);
        } catch (ModelNotFoundException $th) {
            return Transformer::fail('Detail order not found.', null, 404);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update detail order data.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyDetail($id, $detailId)
    {
        try {
            $detailOrder = DetailOrder::select('id')
                                        ->where('order_id', $id)
                                        ->whereId($detailId)
                                        ->firstOrFail();

            $detailOrder->delete();

            return Transformer::ok('Success to delete detail order data.', );
        } catch (ModelNotFoundException $th) {
            return Transformer::fail('Detail order not found.', null, 404);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete detail order data.');
        }
    }
}
