<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function index()
    {
        try {
            $tasks = Task::select('id', 'user_id', 'name', 'description', 'status', 'created_at')
                ->with('user:id,name')
                ->paginate(100);

            return response()->json([
                'data' => $tasks->items(),
                'current_page' => $tasks->currentPage(),
                'total' => $tasks->total(), 
                'message' => 'Tasks retrieved successfully'
            ], 200);
        } catch (Exception $e) {
            Log::error('Error retrieving tasks: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve tasks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $task = Task::select('id', 'user_id', 'name', 'description', 'status', 'created_at')
                ->with('user:id,name')
                ->findOrFail($id);

            return response()->json([
                'data' => $task,
                'message' => 'Task retrieved successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Task not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            Log::error('Error retrieving task: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve task',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), Task::rules());

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid data',
                'messages' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $task = Task::create($request->all());

            DB::commit();

            return response()->json([
                'message' => 'Task created successfully',
                'developer' => $task
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating task: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to create task',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info($request->all());
        $validator = Validator::make($request->all(), Task::rules());

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid data',
                'error' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $task = Task::find($id);
            if (!$task) {
                return response()->json([
                    'error' => 'taks not found'
                ], 404);
            }

            $task->user_id = $request->input('user_id');
            $task->name = $request->input('name');
            $task->description = $request->input('description');
            $task->status = $request->input('status');
            $task->save();

            DB::commit();
            Log::info("Taks with ID {$id} updated successfully.");
            return response()->json([
                'data' => $task,
                'message' => 'Taks updated successfully'
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error updating task: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to update taks',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $task = Task::find($id);
            if (!$task) {
                return response()->json([
                    'error' => 'Taks not found'
                ], 404);
            }
            $task->delete();

            DB::commit();

            Log::info("Task with ID {$id} deleted successfully.");
            return response()->json([
                'message' => 'Taks deleted successfully'
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();
            Log::error('Error deleting task: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete task',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
