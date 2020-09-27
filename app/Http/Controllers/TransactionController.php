<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Filters\TransactionFilter;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionsCollection;
use App\Order;
use App\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TransactionController extends Controller
{
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
            $query = Transaction::query();
            $query = TransactionFilter::collection($request, $query);

            $limit = (int) $request->get('limit', 15);
            $transactions = $limit > 1 ? $query->paginate($limit) : $query->get();

            return (new TransactionsCollection($transactions))
                        ->additional(
                            Transformer::meta(true, 'Success to get transactions collection.')
                        );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get transactions collection.');
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
            '_order_id' => 'required|regex:/^\d+$/',
        ]);

        try {
            // Get order
            $order = Order::whereId($request->get('_order_id'))->where('status', 'N')->latest()->first();
            if (!$order) {
                return Transformer::fail('Order not found.', null, 404);
            }

            // Get order details
            $details = $order->details()->with('food:id,price,discount')->get();

            // Calculate total price
            $total_price = $details->reduce(function ($carry, $detail) {
                $food = $detail->food;

                $price = (float) $food->price;
                if ((float) $food->discount > 0) {
                    $discount_price = $food->discount / 100 * $food->price;
                    $price = $food->price - $discount_price;
                }

                return $carry + ($price * $detail->qty);
            }, 0);

            $transaction = Transaction::create([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'total_price' => $total_price,
            ]);

            if ($transaction) {
                $order->update([
                    'status' => 'Y'
                ]);

                $order->table->update([
                    'available' => 'Y',
                ]);
            }

            return Transformer::ok(
                'Success to make a transaction.',
                ['transaction' => new TransactionResource($transaction)],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to make a transaction.');
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
            $transaction = Transaction::findOrFail($id);

            $transaction->delete();

            return Transformer::ok('Success to delete transaction.');
        } catch (ModelNotFoundException $th) {
            return Transformer::fail('Transaction not found.', null, 404);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete transaction.');
        }
    }
}
