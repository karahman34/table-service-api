<?php

namespace App\Http\Controllers;

use App\Exports\PermissionsExport;
use App\Helpers\Transformer;
use App\Http\Filters\PermissionFilter;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\PermissionsCollection;
use App\Imports\PermissionsImport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Response structure for not found permission.
     *
     * @return  JsonResponse
     */
    private function notFoundResponse()
    {
        return Transformer::fail('Permission not found.', null, 404);
    }

    /**
     * Return validation rules for permission.
     *
     * @param   bool   $edit
     * @param   false  $id
     *
     * @return  array  $rules
     */
    private function validationRules(bool $edit = false, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255|unique:roles,name',
        ];

        if ($edit) {
            $rules['name'] .= ',' . $id;
        }

        return $rules;
    }

    /**
     * Display a listing of the resource.
     *
     * @param   Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Permission::query();
            $query = PermissionFilter::collection($request, $query);

            $limit = $request->get('limit', 15);
            $permissions = $limit > 1 ? $query->paginate($limit) : $query->get();

            return (new PermissionsCollection($permissions))
                        ->additional(Transformer::meta(true, 'Success to get permissions collection.'));
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get permissions collection.');
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
            return Excel::download(new PermissionsExport, "permissions.{$request->get('type')}");
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to export permissions collection.');
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
            Excel::import(new PermissionsImport, $request->file('file'));

            return Transformer::ok('Success to import permissions data.');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to import permissions data.');
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
        $this->validate($request, $this->validationRules());

        try {
            $permission = Permission::create([
                'name' => $request->get('name')
            ]);

            return Transformer::ok(
                'Success to create the new permission.',
                ['permission' => new PermissionResource($permission)],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create permission.');
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
            $permission = Permission::findOrFail($id);

            return Transformer::ok(
                'Success to get permission details.',
                [
                    'permission' => new PermissionResource($permission),
                ],
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get permission details.');
        }
    }

    /**
     * Synchronize permission's roles.
     *
     * @param   Request  $request
     * @param   int      $id
     *
     * @return  JsonResponse
     */
    public function syncRoles(Request $request, $id)
    {
        $this->validate($request, [
            'roles_ids' => 'present|array',
            'roles_ids.*' => 'regex:/^\d+$/',
        ]);

        try {
            $roles_ids = $request->get('roles_ids');
            if (is_null($roles_ids)) {
                $roles_ids = [];
            }
            
            $permission = Permission::findOrFail($id);
            $permission->syncRoles($roles_ids);
            
            return Transformer::ok(
                'Success to synchronize permission\' roles.',
                [
                    'permission' => new PermissionResource($permission),
                ],
                201
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to synchronize permission\' roles.');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validationRules(true, $id));

        try {
            $permission = Permission::findOrFail($id);
            $permission->update([
                'name' => $request->get('name')
            ]);

            return Transformer::ok(
                'Success to update permission data.',
                ['permission' => new PermissionResource($permission)]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update permission data.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::select('id')->whereId($id)->firstOrFail();

            // Delete object.
            $permission->delete();

            return Transformer::ok(
                'Success to delete permission data.'
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete permission data.');
        }
    }
}
