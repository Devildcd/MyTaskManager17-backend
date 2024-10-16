<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::select('id', 'name', 'role', 'created_at')
                ->paginate(100);

            return response()->json([
                'data' => $users->items(),
                'current_page' => $users->currentPage(),
                'total' => $users->total(), 
                'message' => 'User retrieved successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Error retrieving users: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve users',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::select('id', 'name', 'email', 'role', 'created_at')
                ->findOrFail($id);

            return response()->json([
                'data' => $user,
                'message' => 'User retrieved successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'User not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            Log::error('Error retrieving user: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve tuserask',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
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

            return response()->json([
                'status' => 1,
                'msg' => 'User created successfully!',
                'user' => $user->only(['id', 'name', 'email', 'role']),
            ], 201);
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

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                'role' => 'sometimes|string|max:50',
            ]);


            $user = User::findOrFail($id);

            $user->update([
                'name' => $request->input('name', $user->name),
                'email' => $request->input('email', $user->email),
                'password' => $request->input('password') ? Hash::make($request->input('password')) : $user->password,
                'role' => $request->input('role', $user->role),
            ]);

            return response()->json([
                'status' => 1,
                'msg' => 'User updated successfully!',
                'user' => $user->only(['id', 'name', 'email', 'role']),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 0,
                'errors' => $e->errors(),
                'msg' => 'Validation error',
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'User not found',
            ], 404);
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

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'status' => 1,
                'msg' => 'User deleted successfully!',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 0,
                'msg' => 'User not found',
            ], 404);
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

    public function getUserTasks($id)
    {
        $user = User::with('tasks')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user->tasks);
    }
    
}
