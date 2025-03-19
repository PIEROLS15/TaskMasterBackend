<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="API Tasks", 
 *     version="1.0",
 *     description="Listado de URI´S de la API Tasks"
 * )
 *
 * @OA\Server(url="http://taskmasterbackend.test")
 */

 /**
     * @OA\Schema(
     *     schema="Task",
     *     title="Task",
     *     description="Modelo de una tarea",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="user_id", type="integer", example=1), 
     *     @OA\Property(property="title", type="string", example="Nueva tarea"),
     *     @OA\Property(property="description", type="string", example="Nueva tarea por hacer"),
     *     @OA\Property(property="due_date", type="string", format="date", example="2025-03-30"),
     *     @OA\Property(property="status", type="string", example="pending"),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-03-19T15:43:11.000000Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-03-19T16:56:50.000000Z")
     * )
     */
class TaskController extends Controller
{
    

    /**
     * Lista todas las tareas del usuario autenticado
     * 
     * @OA\Get(
     *     path="/api/tasks",
     *     tags={"Tareas"},
     *     summary="Obtener todas las tareas del usuario autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tareas obtenida correctamente",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
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

    /**
     * Crear una nueva tarea
     *
     * @OA\Post(
     *     path="/api/tasks",
     *     tags={"Tareas"},
     *     summary="Crear una nueva tarea",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "due_date"},
     *             @OA\Property(property="title", type="string", example="Nueva tarea"),
     *             @OA\Property(property="description", type="string", example="Descripción de la tarea"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-03-30"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tarea creada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(response=400, description="Solicitud inválida"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'due_date' => 'required|date|after_or_equal:today',
                'status' => 'sometimes|in:pending,completed'
            ]);

            $data = $request->all();
            $data['status'] = $data['status'] ?? 'pending';

            $task = Auth::user()->tasks()->create($data);

            return response()->json($task, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener una tarea específica
     *
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     tags={"Tareas"},
     *     summary="Obtener una tarea por ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Tarea obtenida exitosamente", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Tarea no encontrada")
     * )
     */
    public function show(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($task);
    }

    /**
     * Actualizar una tarea existente
     *
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     tags={"Tareas"},
     *     summary="Actualizar una tarea por ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "due_date"},
     *             @OA\Property(property="title", type="string", example="Nueva tarea actualizada"),
     *             @OA\Property(property="description", type="string", example="Descripción actualizada de la tarea"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-04-01"),
     *             @OA\Property(property="status", type="string", example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarea actualizada exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(response=400, description="Solicitud inválida"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Tarea no encontrada"),
     *     @OA\Response(response=500, description="Error en el servidor")
     * )
     */

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


    /**
     * Eliminar una tarea
     *
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     tags={"Tareas"},
     *     summary="Eliminar una tarea por ID",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la tarea a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Tarea eliminada correctamente"),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Tarea no encontrada")
     * )
     */
    public function destroy(Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();
        return response()->json(['message' => 'Tarea eliminada correctamente']);
    }
}