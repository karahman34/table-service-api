<?php

namespace App\Http\Controllers;

use App\Exports\TransactionsExport;
use App\Helpers\Transformer;
use App\Http\Filters\TransactionFilter;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\TransactionsCollection;
use App\Imports\TransactionsImport;
use App\Order;
use App\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

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
     * Export the resources.
     * 
     * @param  Request  $request
     *
     * @return  \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export(Request $request)
    {
        $this->validate($request, [
            'type' => 'required|string|in:xlsx,csv'
        ]);

        try {
            return Excel::download(new TransactionsExport, "transactions.{$request->get('type')}");
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to export transactions collection.');
        }
    }

    /**
     * Import data from file.
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file|mimes:xlsx,csv'
        ]);

        try {
            Excel::import(new TransactionsImport, $request->file('file'));

            return Transformer::ok('Success to import transactions data.');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to import transactions data.');
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
     * Get transaction details.
     *
     * @param   int  $id
     *
     * @return  JsonResponse
     */
    public function show($id)
    {
        try {
            $transaction = Transaction::with('order', 'order.details')
                                        ->whereId($id)
                                        ->firstOrFail();

            return (new TransactionResource($transaction))
                        ->additional(
                            Transformer::meta(true, 'Success to get transaction details.')
                        );
        } catch (ModelNotFoundException $th) {
            return Transformer::fail('Transaction not found.', null, 404);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get transaction details.');
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
