<?php

namespace App\Http\Controllers;

use App\Events\RefreshTablesEvent;
use App\Helpers\Transformer;
use App\Http\Filters\TableFilter;
use App\Http\Resources\OrderResource;
use App\Http\Resources\TableResource;
use App\Http\Resources\TablesCollection;
use App\Order;
use App\Table;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Json not found response.
     *
     * @return  JsonResponse
     */
    private function notFoundResponse()
    {
        return Transformer::fail('Table not found.', null, 404);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Table::query();
            $query = TableFilter::collection($request, $query);

            $limit = $request->get('limit', 20);
            $tables = (int) $limit > 1 ? $query->paginate($limit) : $query->get();
            
            return (new TablesCollection($tables))
                        ->additional(
                            Transformer::meta(true, 'Success to get tables collection.')
                        );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get tables collection.');
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
        $this->validate($request, Table::validationRules());

        try {
            $table = Table::create([
                'number' => $request->get('number'),
                'available' => 'Y'
            ]);

            return Transformer::ok(
                'Success to create table.',
                [
                    'table' => new TableResource($table)
                ],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create table.');
        }
    }

    /**
     * Get order details
     *
     * @param   int  $id
     *
     * @return  JsonResponse
     */
    public function getOrder($id)
    {
        try {
            $order = Order::select('orders.*')
                            ->join('tables', 'orders.table_id', 'tables.id')
                            ->where('tables.id', $id)
                            ->where('tables.available', 'N')
                            ->where('orders.status', 'N')
                            ->with('details')
                            ->first();

            return Transformer::ok('Success to get order details.', [
                'order' => is_null($order) ? null : new OrderResource(($order))
            ]);
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get order details.');
        }
    }

    /**
     * Check wheater user can set the table or not.
     *
     * @param   string  $username
     * @param   string  $password
     *
     * @return  array|bool
     */
    private function canSetTable(string $username, string $password)
    {
        $errors = [
            'message' => 'Username or password is invalid.',
            'status' => 401,
        ];

        $user = User::select('id', 'username', 'password')->where('username', $username)->first();
        if (!$user) {
            return $errors;
        }

        $password_match = app('hash')->check($password, $user->password);
        if (!$password_match) {
            return $errors;
        }
        
        $user_roles = $user->getPermissionsViaRoles()->pluck('name');
        $user_has_permissions = in_array('table.update', $user_roles->toArray());

        if (!$user_has_permissions) {
            $errors['message'] = 'User does not have the right permissions.';
            $errors['status'] = 403;

            return $errors;
        }

        return true;
    }

    /**
     * Set Table
     *
     * @param   Request  $request
     *
     * @return  JsonResponse
     */
    public function setTable(Request $request)
    {
        $payload = $this->validate($request, [
            'number' => 'present|nullable|regex:/^[1-9]+([0-9]+)?$/',
            'old_number' => 'present|nullable|string',
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            // Check is user can update the table.
            $errors = $this->canSetTable($payload['username'], $payload['password']);
            if ($errors !== true) {
                return Transformer::fail($errors['message'], null, $errors['status']);
            };

            if (!is_null($payload['number'])) {
                // Get table model
                $table = Table::where('number', $request->get('number'))->firstOrFail();
            
                if (strtolower($table->available) === 'n') {
                    return Transformer::fail('The table is busy.', null, 400);
                }
    
                // Update new table
                $table->update([
                    'available' => 'n',
                ]);
            }

            if (!is_null($payload['old_number'])) {
                Table::where('number', $payload['old_number'])
                        ->where('available', 'N')
                        ->update([
                            'available' => 'Y'
                        ]);
            }

            // Dispatch an action
            event(new RefreshTablesEvent());

            return Transformer::ok('Success to set table.', null, 200);
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to set table.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, Table::validationRules(true, $id));

        try {
            $table = Table::findOrFail($id);
            
            $table->update([
                'number' => $request->get('number'),
                'available' => strtoupper($request->get('available')),
            ]);

            return Transformer::ok(
                'Success to update table data.',
                [
                    'table' => new TableResource($table)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update table data.');
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
            $table = Table::select('id')->whereId($id)->firstOrFail();
            
            $table->delete();

            return Transformer::ok(
                'Success to delete table data.'
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete table data.');
        }
    }
}
