<?php

namespace App\Http\Controllers\Api\Internal;

use App\Models\Account\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Str;

class ApiInternalController extends ApiController
{
    /**
     * Create new user with new account and create api access token
     * Note: we accept pre-encrypted password to avoid sending plain text password over http
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function createAccount(Request $request)
    {
        $request->validate([
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'internal_user_id' => 'required|integer',
            'password' => ['required'],
        ]);

        $account = Account::createDefault(
            $request->get('first_name'),
            $request->get('last_name'),
            $request->get('email'),
            Str::random(40), // temporary password
        );

        $user                   = $account->users()->first();
        $user->internal_user_id = $request->get('internal_user_id');
        $user->password         = $request->get('password');
        $user->save();

        return response()->json([
            'user_id' => $user->id,
            'access_token' => $user->createToken('My Token')->accessToken,
        ]);
    }
}
