<?php

namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\Baby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class AlarmController extends Controller
{
    public function index(Baby $baby)
    {
        $alarms = $baby->alarms()->orderBy('time')->get();
        $html = View::make('dashboard.alarms', compact('alarms'))->render();
        
        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }

    public function toggle(Alarm $alarm, Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $alarm->update([
                'is_active' => $request->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alarme atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o alarme'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'baby_id' => 'required|exists:babies,id',
                'time' => 'required|date_format:H:i',
                'day_name' => 'required|string|in:Segunda,Terça,Quarta,Quinta,Sexta,Sábado,Domingo'
            ]);

            // Verificar se o bebê pertence ao usuário atual
            $baby = Baby::where('user_id', Auth::id())
                ->findOrFail($validated['baby_id']);

            $alarm = Alarm::create([
                'baby_id' => $validated['baby_id'],
                'time' => $validated['time'],
                'day_name' => $validated['day_name'],
                'is_active' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alarme criado com sucesso',
                'alarm' => $alarm
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao criar alarme:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar o alarme: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Alarm $alarm)
    {
        $request->validate([
            'time' => 'required|date_format:H:i',
            'day_name' => 'required|string|in:Segunda,Terça,Quarta,Quinta,Sexta,Sábado,Domingo',
            'is_active' => 'required|boolean'
        ]);

        try {
            $alarm->update([
                'time' => $request->time,
                'day_name' => $request->day_name,
                'is_active' => $request->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alarme atualizado com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar o alarme'
            ], 500);
        }
    }

    public function destroy(Alarm $alarm)
    {
        try {
            $alarm->delete();

            return response()->json([
                'success' => true,
                'message' => 'Alarme excluído com sucesso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir o alarme'
            ], 500);
        }
    }
} 