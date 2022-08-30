<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;

class AuthController extends Controller
{
    /**
     * Authenticates the customer and generates the token
     * for the authorization
     *
     * @param LoginRequest $request Holds the validator instance
     *
     * @return JsonResponse json
     */
    public function login(LoginRequest $request) : JsonResponse
    {
        $data = $request->validated();

        if (auth()->guard('admin')->attempt($data)) {
            $user = auth()->guard('admin')->user();
            $token = $user->createToken('ASPIRE')->plainTextToken;

            return response()
                ->json(
                    ['message' => 'Login successful'],
                    200,
                    ['Authorization' => $token]
                );
        }

        return response()
            ->json(['message' => 'Incorrect username/password'], 400);
    }
}
