<?php

namespace App\Http\Controllers;

use App\Models\Baby;
use App\Models\Feeding;
use App\Models\Tip;
use App\Models\User;
use App\Models\Alarm;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\NotificationController;
use Closure;
use App\Models\Notification;

class DashboardController extends Controller
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }

    public function index()
    {
        $user = auth()->user();
        $selectedBaby = session('selected_baby_id') ? Baby::find(session('selected_baby_id')) : $user->babies->first();
        
        if (!$selectedBaby) {
            return redirect()->route('babies.create')->with('warning', 'Por favor, cadastre um bebê primeiro.');
        }

        $notifications = $selectedBaby->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.index', [
            'user' => $user,
            'selectedBaby' => $selectedBaby,
            'notifications' => $notifications
        ]);
    }

    public function storeFeeding(Request $request)
    {
        try {
            Log::info('Iniciando registro de amamentação', ['request' => $request->all()]);

            if (!Auth::check()) {
                Log::warning('Tentativa de registro sem autenticação');
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não autenticado. Por favor, faça login novamente.'
                ], 401);
            }

            $baby = Baby::find($request->baby_id);
            Log::info('Bebê encontrado', ['baby' => $baby]);

            if (!$baby || $baby->user_id !== Auth::id()) {
                Log::warning('Bebê não encontrado ou não pertence ao usuário', [
                    'baby_id' => $request->baby_id,
                    'user_id' => Auth::id()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Bebê não encontrado ou não pertence ao usuário.'
                ], 403);
            }

            $validated = $request->validate([
                'baby_id' => 'required|exists:babies,id',
                'started_at' => 'required|date',
                'ended_at' => 'nullable|date|after:started_at',
                'duration' => 'required|integer|min:0',
                'quantity' => 'nullable|integer|min:0'
            ]);

            Log::info('Dados validados', ['validated' => $validated]);

            // Usar o horário atual de Brasília
            $now = Carbon::now('America/Sao_Paulo');
            
            // Criar o registro com o horário atual
            $feeding = Feeding::create([
                'baby_id' => $validated['baby_id'],
                'started_at' => $now,
                'ended_at' => $now,
                'duration' => $validated['duration'],
                'quantity' => $validated['quantity']
            ]);

            Log::info('Registro criado com sucesso', ['feeding' => $feeding]);

            // Buscar os 3 registros mais recentes
            $recentFeedings = $baby->feedings()
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Amamentação registrada com sucesso!',
                'feeding' => $feeding,
                'recent_feedings' => $recentFeedings
            ]);

        } catch (ValidationException $e) {
            Log::warning('Erro de validação', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos. Por favor, verifique os campos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar amamentação:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro ao registrar a amamentação. Por favor, tente novamente.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function storeBaby(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'birth_date' => [
                    'required',
                    'date',
                    'before_or_equal:today'
                ]
            ]);

            // Verificar limite de bebês
            /** @var User $user */
            $user = Auth::user();
            if ($user->babies->count() >= 5) {
                return redirect()->back()
                    ->with('error', 'Limite máximo de 5 bebês por usuário atingido.')
                    ->withInput();
            }

            DB::beginTransaction();
            
            $baby = $user->babies()->create($validated);

            DB::commit();

            // Limpar o cache do dashboard para o usuário
            Cache::forget('dashboard_data_' . Auth::id());

            return redirect()->route('dashboard')
                ->with('success', 'Bebê cadastrado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cadastrar bebê:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erro ao cadastrar bebê: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function getFeedingStatistics($babyId)
    {
        $statistics = [
            'daily_average' => Feeding::where('baby_id', $babyId)
                ->whereDate('started_at', '>=', now()->subDays(7))
                ->avg('duration'),
            'total_feedings' => Feeding::where('baby_id', $babyId)
                ->whereDate('started_at', '>=', now()->subDays(7))
                ->count(),
            // Adicionar mais estatísticas conforme necessário
        ];
        
        return response()->json($statistics);
    }

    public function getRecentFeedings(Request $request)
    {
        try {
            $babyId = $request->query('baby_id');
            
            if (!$babyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID do bebê não fornecido'
                ], 400);
            }

            $baby = Baby::where('user_id', Auth::id())
                ->findOrFail($babyId);

            // Buscar os 3 registros mais recentes
            $feedings = $baby->feedings()
                ->orderBy('created_at', 'desc')
                ->take(3)
                ->get();

            Log::info('Registros encontrados:', [
                'count' => $feedings->count(),
                'feedings' => $feedings->toArray()
            ]);

            return response()->json([
                'success' => true,
                'feedings' => $feedings
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar registros recentes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar registros recentes'
            ], 500);
        }
    }
}

class TipsController extends Controller
{
    protected function getBabyAgeRange()
    {
        // Implementar lógica para determinar a faixa etária do bebê
        return '0-6';
    }

    public function getDailyTip()
    {
        $tip = Tip::where('age_range', $this->getBabyAgeRange())
            ->inRandomOrder()
            ->first();
            
        return response()->json($tip);
    }
}

class LgpdMiddleware
{
    public function handle($request, Closure $next)
    {
        if (!session()->has('lgpd_consent')) {
            return redirect()->route('lgpd.consent');
        }
        
        return $next($request);
    }
} 