<?php

namespace App\Http\Controllers;

use App\Helpers\Transformer;
use App\Http\Resources\TableResource;
use App\Http\Resources\TablesCollection;
use App\Table;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

            $limit = $request->get('limit', 20);
            $tables = $limit > 0 ? $query->paginate($limit) : $query->get();
            
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
                'available' => strtoupper($request->get('available'))
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