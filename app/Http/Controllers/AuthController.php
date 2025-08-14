<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $data = $r->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|min:8'
        ]);
        $user = \App\Models\User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>bcrypt($data['password']),
        ]);
        $token = $user->createToken('api')->plainTextToken;
        return response()->json(['user'=>$user,'token'=>$token], 201);
    }

    public function login(Request $r)
    {
        $data = $r->validate(['email'=>'required|email', 'password'=>'required']);
        $user = \App\Models\User::where('email',$data['email'])->first();
        if (!$user || !\Hash::check($data['password'],$user->password)) {
            return response()->json(['message'=>'Invalid credentials'], 422);
        }
        $token = $user->createToken('api')->plainTextToken;
        return ['user'=>$user,'token'=>$token];
    }
}
