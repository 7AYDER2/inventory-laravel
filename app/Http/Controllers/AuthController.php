<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        $token = $user->createToken('api')->plainTextToken;
        
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 422);
        }
        
        $token = $user->createToken('api')->plainTextToken;
        return response()->json([
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }
}
