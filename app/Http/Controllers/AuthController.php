<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;

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

        if (auth()->attempt($data)) {
            $user = auth()->user();
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
