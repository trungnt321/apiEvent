<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserAuthController extends Controller
{
    public function register(Request $request)
    {

        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required',
            'password' => 'required',
            'phone' => 'required',
            'role' => 'required'
        ]);

        $data['password'] = bcrypt($request->password);

        $user = User::create($data);
        $user->token = $user->createToken('API Token')->accessToken;

        return response([ 'user' => $user]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            return response(['error_message' => 'Incorrect Details.
            Please try again']);
        }

        auth()->user()->token = auth()->user()->createToken('API Token')->accessToken;

        return response(['user' => auth()->user()]);

    }
}
