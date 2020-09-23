<?php

namespace App\Http\Controllers;

use App\DetailOrder;
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
     * Check if the table is valid or not.
     *
     * @param   int|string  $table_id
     *
     * @return  bool
     */
    private function validateTable($table_id)
    {
        return Table::whereId($table_id)->where('available', 'N')->count() > 0
            ? true
            : false;
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
            'table_id' => 'required|regex:/^\d+$/',
            'details' => 'required|array',
            'details.*.food_id' => 'required|regex:/^\d+$/',
            'details.*.qty' => 'required|regex:/^[1-9]+([0-9]+)?$/',
        ]);

        try {
            if (!$this->validateTable($request->get('table_id'))) {
                return Transformer::fail('Table is not valid.', null, 400);
            }

            $order = Order::select('orders.*')
                            ->where('status', 'N')
                            ->join('tables', 'orders.table_id', 'tables.id')
                            ->where('tables.id', $request->get('table_id'))
                            ->where('tables.available', 'N')
                            ->first();
            
            if (!$order) {
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'table_id' => $request->get('table_id'),
                    'status' => 'N'
                ]);
            }

            foreach ($request->get('details') as $detail) {
                $order->details()->create([
                    'food_id' => $detail['food_id'],
                    'qty' => $detail['qty'],
                ]);
            }

            return Transformer::ok(
                'Success to make an order.',
                ['order' => new OrderResource($order)],
                201
            );
        } catch (\Throwable $th) {
            return $th;
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
     * Update serve column at detail order
     *
     * @param   Request  $request
     * @param   int   $id
     *
     * @return  JsonResponse
     */
    public function serveFood(Request $request, $id)
    {
        $this->validate($request, [
            '_detail_id' => 'required|regex:/^\d+$/',
        ]);

        try {
            $detail_order = DetailOrder::where('order_id', $id)
                                        ->whereId($request->get('_detail_id'))
                                        ->firstOrFail();

            $detail_order->update([
                'served_at' => Carbon::now()
            ]);

            return Transformer::ok(
                'Success to update data.',
                null
            );
        } catch (ModelNotFoundException $th) {
            return Transformer::fail('Order or detail order not found.', null, 404);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update data.');
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
}
