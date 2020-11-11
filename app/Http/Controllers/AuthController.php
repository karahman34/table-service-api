<?php

namespace App\Http\Controllers;

use App\Events\RefreshTablesEvent;
use App\Helpers\Transformer;
use App\Http\Resources\UserResource;
use App\Table;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Token TTL.
     *
     * @var int
     */
    private $token_ttl = 604800; // 1 week

    /**
     * Token response structure.
     *
     * @param  string $token
     *
     * @return  array
     */
    private function respondWithToken(string $token)
    {
        return [
            'access_token' => $token,
            'type' => 'Bearer',
            'expired_in' => auth()->factory()->getTTL(),
        ];
    }

    /**
     * Login user.
     *
     * @param  Request  $request
     *
     * @return  JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|string|regex:/^[_a-z0-9]+$/',
            'password' => 'required|string',
        ]);

        try {
            $token = Auth::setTTL($this->token_ttl)->attempt($request->only(['username', 'password']));

            if (!$token) {
                return Transformer::fail('Invalid login credentials.', null, 401);
            }

            return Transformer::ok(
                'Success to authenticated user.',
                array_merge(
                    $this->respondWithToken($token),
                    ['user' => new UserResource(auth()->user())]
                ),
                200
            );
        } catch (\Throwable $th) {
            return Transformer::fail('Failed to authenticated user.');
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return Transformer::ok(
            'Success to get user details.',
            [
                'user' => new UserResource(auth()->user()),
            ],
            200
        );
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return Transformer::ok(
            'Success to refresh token.',
            $this->respondWithToken(auth()->setTTL($this->token_ttl)->refresh())
        );
    }

    /**
     * Logout user.
     *
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            Auth::logout();

            if ($request->has('number')) {
                $table = Table::where('number', $request->get('number'))->where('available', 'N')->first();
                if ($table) {
                    $table->update([
                        'available' => 'Y'
                    ]);
                }

                event(new RefreshTablesEvent);
            }

            return Transformer::ok('Success to logged out user.');
        } catch (\Throwable $th) {
            return Transformer::ok('Failed to logged out user.');
        }
    }
}
