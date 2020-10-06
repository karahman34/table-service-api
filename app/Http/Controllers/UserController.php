<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\Helpers\Transformer;
use App\Http\Filters\UserFilter;
use App\Http\Resources\UserResource;
use App\Http\Resources\UsersCollection;
use App\Imports\UsersImport;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * User not found response.
     *
     * @return  JsonResponse
     */
    private function notFoundResponse()
    {
        return Transformer::fail('User not found.', null, 404);
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
            $query = User::query();
            $query = UserFilter::collection($request, $query);

            $limit = $request->get('limit', 15);
            $users = (int) $limit > 0 ? $query->paginate($limit) : $query->get();
            
            return (new UsersCollection($users))
                        ->additional(
                            Transformer::meta(true, 'Success to get users collection.')
                        );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get users collection.');
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
            return Excel::download(new UsersExport, "users.{$request->get('type')}");
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to export users collection.');
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
            Excel::import(new UsersImport, $request->file('file'));

            return Transformer::ok('Success to import users data.');
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to import users data.');
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
        $this->validate($request, User::validationRules());

        try {
            $user = new User;
            $user->username = $request->get('username');
            $user->password = app('hash')->make($request->get('password'));
            $user->save();

            return Transformer::ok(
                'Success to create the user.',
                [
                    'user' => new UserResource($user)
                ],
                201
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to create new user.');
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
            $user = User::findOrFail($id);

            return Transformer::ok(
                'Success to get user details.',
                [
                    'user' => new UserResource($user)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to get user details.');
        }
    }

    public function syncRoles(Request $request, $id)
    {
        $this->validate($request, [
            'roles_ids' => 'present|array',
            'roles_ids.*' => 'regex:/^\d+$/'
        ]);

        try {
            $roles_ids = $request->get('roles_ids');
            if (is_null($roles_ids)) {
                $roles_ids = [];
            }

            $user = User::findOrFail($id);
            $user->syncRoles($roles_ids);

            return Transformer::ok(
                'Success to synchronize user\' roles.',
                [
                    'user' => new UserResource($user)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to synchronize user\'s roles.');
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
        $this->validate($request, User::validationRules(true, $id));
        
        try {
            $user = User::findOrFail($id);
            $user->username = $request->get('username');

            if ($request->has('password')) {
                $user->password = app('hash')->make($request->get('password'));
            }

            $user->save();

            return Transformer::ok(
                'Success to update user data.',
                [
                    'user' => new UserResource($user)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to update user data.');
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
            $user = User::findOrFail($id);

            $user->delete();

            return Transformer::ok(
                'Success to delete user data.',
                [
                    'user' => new UserResource($user)
                ]
            );
        } catch (ModelNotFoundException $th) {
            return $this->notFoundResponse();
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to delete user data.');
        }
    }
}
