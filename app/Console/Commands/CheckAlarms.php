<?php

namespace App\Console\Commands;

use App\Models\Alarm;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckAlarms extends Command
{
    protected $signature = 'alarms:check';
    protected $description = 'Verifica os alarmes ativos e envia notificações';

    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle()
    {
        try {
            $now = Carbon::now('America/Sao_Paulo');
            $currentDay = $now->locale('pt_BR')->isoFormat('dddd');
            $currentTime = $now->format('H:i');

            $this->info("Verificando alarmes para {$currentDay} às {$currentTime}");

            $alarms = Alarm::where('is_active', true)
                ->where('day_name', $currentDay)
                ->where('time', $currentTime)
                ->with(['baby.user', 'baby'])
                ->get();

            $this->info("Encontrados {$alarms->count()} alarmes para o horário atual");

            foreach ($alarms as $alarm) {
                $this->info("Processando alarme {$alarm->id} para o bebê {$alarm->baby->name}");
                
                if (!$alarm->baby->user) {
                    $this->error("Usuário não encontrado para o bebê {$alarm->baby->name}");
                    continue;
                }

                $notificationSent = $this->notificationService->sendAlarmNotification($alarm->baby, $alarm);
                
                if ($notificationSent) {
                    $this->info("Notificação enviada com sucesso para o alarme {$alarm->id}");
                } else {
                    $this->error("Falha ao enviar notificação para o alarme {$alarm->id}");
                }
            }

        } catch (\Exception $e) {
            $this->error("Erro ao verificar alarmes: " . $e->getMessage());
            Log::error('Erro ao verificar alarmes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 