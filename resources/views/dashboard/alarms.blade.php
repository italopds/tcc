@if($alarms->isEmpty())
    <div class="alert alert-info">
        Nenhum alarme configurado. Clique em "Novo Alarme" para adicionar.
    </div>
@else
    @foreach($alarms as $alarm)
        <div class="alarm-item d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="mb-0">{{ $alarm->formatted_time }}</h5>
                <small class="text-muted">{{ $alarm->day_name }}</small>
            </div>
            <div class="d-flex align-items-center">
                <div class="form-check form-switch me-3">
                    <input type="checkbox" class="form-check-input alarm-toggle" 
                        id="alarm-{{ $alarm->id }}" 
                        data-alarm-id="{{ $alarm->id }}"
                        {{ $alarm->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="alarm-{{ $alarm->id }}"></label>
                </div>
                <button class="btn btn-sm btn-outline-primary me-2" onclick="alarmManager.showAlarmModal({{ $alarm }})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="alarmManager.deleteAlarm({{ $alarm->id }})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    @endforeach
@endif 