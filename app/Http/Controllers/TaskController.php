<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index()
    {
        try {
            $tasks = Auth::user()->tasks;
            return response()->json($tasks, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error al obtener las tareas.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) {
                        if (strtotime($value) < strtotime(now()->format('Y-m-d'))) {
                            $fail('La fecha de vencimiento no puede ser anterior al día actual.');
                        }
                    },
                ]
            ],[
                'title.required' => 'El titulo de la tarea es obligatoria',
                'due_date.required' => 'La fecha de vencimiento es obligatoria.',
                'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            ]);

            $data = $request->all();
            $data['status'] = $data['status'] ?? 'pending';

            $task = Auth::user()->tasks()->create($data);

            return response()->json($task, 201);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function show(Task $task)
    {
        try {
            if ($task->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($task);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, Task $task)
    {
        try {
            if ($task->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
    
            $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'due_date' => [
                    'sometimes',
                    'date',
                        function ($attribute, $value, $fail) {
                        if (strtotime($value) < strtotime(now()->format('Y-m-d'))) {
                            $fail('La fecha de vencimiento no puede ser anterior al día actual.');
                        }
                    },
                ],
                'status' => 'sometimes|in:pending,completed',
            ],[
                'due_date.date' => 'La fecha de vencimiento debe ser una fecha válida.',
            ]);
    
            $task->update($request->all());
    
            return response()->json($task);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Task $task)
    {
        try {
            if ($task->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
    
            $task->delete();
    
            return response()->json(['message' => 'Tarea eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}