<?php

namespace App\Http\Controllers;

use App\Filters\RoleFilter;
use App\Helpers\Transformer;
use App\Http\Resources\RoleResource;
use App\Http\Resources\RolesCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Response structure for not found role.
     *
     * @return  JsonResponse
     */
    private function notFoundResponse()
    {
        return Transformer::fail('Role not found.', null, 404);
    }

    /**
     * Return validation rules for role.
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
            $query = Role::query();
            $query = RoleFilter::collection($request, $query);

            $limit = $request->get('limit', 15);
            $roles = $limit > 0 ? $query->paginate($limit) : $query->all();

            return (new RolesCollection($roles))
                        ->additional(Transformer::meta(true, 'Success to get roles collection.'));
        } catch (\Throwable $th) {
            return $th;
            return Transformer::fail('Failed to get roles collection.');
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
            $role = Role::create([
                'name' => $request->get('name')
            ]);

            return Transformer::ok(
                'Success to create the new role.',
                ['role' => new RoleResource($role)],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create role.');
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
            $role = Role::findOrFail($id);

            return Transformer::ok(
                'Success to get role details.',
                [
                    'role' => new RoleResource($role),
                ],
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get role details.');
        }
    }

    /**
     * Synchronize role's permissions.
     *
     * @param   Request  $request
     * @param   int      $id
     *
     * @return  JsonResponse
     */
    public function syncPermissions(Request $request, $id)
    {
        $this->validate($request, [
            'permissions_ids' => 'nullable|array',
            'permissions_ids.*' => 'regex:/^\d+$/',
            'has_no_permissions' => 'nullable|in:y,n'
        ]);

        try {
            $permissions_ids = $request->get('permissions_ids');
            if ($request->get('has_no_permissions') == 'y') {
                $permissions_ids = [];
            }
            
            $role = Role::findOrFail($id);
            $role->permissions()->sync($permissions_ids);

            return Transformer::ok(
                'Success to synchronize role\'s permissions.',
                [
                    'role' => new RoleResource($role),
                ],
                201
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to synchronize role\'s permissions.');
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
            $role = Role::findOrFail($id);
            $role->update([
                'name' => $request->get('name')
            ]);

            return Transformer::ok(
                'Success to update role data.',
                ['role' => new RoleResource($role)]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update role data.');
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
            $role = Role::select('id')->whereId($id)->firstOrFail();

            // Delete object.
            $role->delete();

            return Transformer::ok(
                'Success to delete role data.'
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete role data.');
        }
    }
}
