<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            // Validación de los campos
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'role' => 'required|string|max:50',
            ]);
    
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
            ]);
    
            $token = $user->createToken("auth_token")->plainTextToken;
    
            return response()->json([
                "status" => 1,
                "access_token" => $token,
                "user" => $user->only(['id', 'name', 'email', 'role']),
                "msg" => "¡Success, user created!",
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                "status" => 0,
                "errors" => $e->errors(),
                "msg" => "Validation error",
            ], 422);
    
        } catch (QueryException $e) {
            return response()->json([
                "status" => 0,
                "msg" => "Database error: " . $e->getMessage(),
            ], 500);
    
        } catch (\Exception $e) {
            return response()->json([
                "status" => 0,
                "msg" => "Unexpected error: " . $e->getMessage(),
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
    
            $user = User::where('email', $request->email)
                ->where('name', $request->name)
                ->first();
    
            if ($user && Hash::check($request->password, $user->password)) {
                $token = $user->createToken('auth_token')->plainTextToken;
    
                return response()->json([
                    'status' => 1,
                    'msg' => 'User successfully logged in!',
                    'access_token' => $token,
                    'user' => $user->only(['id', 'name', 'email', 'role']),
                ], 200);
            }
    
            return response()->json([
                'status' => 0,
                'msg' => 'Incorrect email, name or password',
            ], 401);
    
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 0,
                'errors' => $e->errors(),
                'msg' => 'Validation error',
            ], 422);
            
        } catch (QueryException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Database error: ' . $e->getMessage(),
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'Unexpected error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
