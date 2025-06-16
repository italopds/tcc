@extends('layouts.app')

@section('styles')
<style>
    .timer {
        font-size: 2.5em;
        font-weight: bold;
        color: #2d3748;
        margin: 20px 0;
    }
    
    .input-section {
        background-color: #f8fafc;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
    }
    
    .registro {
        background-color: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .registro:hover {
        background-color: #f8fafc;
    }
    
    .registro strong {
        color: #4a5568;
    }
    
    #btnStart, #btnStop {
        min-width: 100px;
        margin: 0 5px;
    }
    
    #ml {
        border: 1px solid #e2e8f0;
        border-radius: 4px;
    }
    
    #ml:focus {
        border-color: #4299e1;
        box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
    }

    .tip-item {
        transition: opacity 0.5s ease-in-out;
        opacity: 0;
    }

    .tip-item.fade-in {
        opacity: 1;
    }

    .tip-item h6 {
        color: #2d3748;
        margin-bottom: 0.5rem;
    }

    .tip-item p {
        color: #4a5568;
        margin-bottom: 0.5rem;
    }

    .tip-item small {
        font-size: 0.8rem;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Dashboard</div>
                <div class="card-body">
                    <!-- Seletor de Bebê -->
                    <div class="mb-4">
                        <label for="baby-selector">Selecione o Bebê:</label>
                        <select id="baby-selector" class="form-select">
                            @foreach($babies as $baby)
                                <option value="{{ $baby->id }}" {{ $selectedBaby && $selectedBaby->id == $baby->id ? 'selected' : '' }}>
                                    {{ $baby->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <!-- Coluna de Amamentação -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">Amamentação</div>
                                <div class="card-body">
                                    <div class="timer text-center mb-3" id="timer">00:00</div>
                                    
                                    <div class="text-center mb-3">
                                        <button id="btnStart" class="btn btn-primary">Iniciar</button>
                                        <button id="btnStop" class="btn btn-danger" disabled>Parar</button>
                                    </div>

                                    <div class="input-section" id="inputSection" style="display: none;">
                                        <div class="form-group">
                                            <label for="ml">Quantidade de leite (mL) <small>(opcional)</small>:</label>
                                            <input type="number" class="form-control" id="ml" placeholder="Ex: 120">
                                        </div>
                                        <button type="button" id="btnSave" class="btn btn-success btn-block mt-2">Salvar Registro</button>
                                    </div>

                                    <div class="mt-4">
                                        <h5>Registros</h5>
                                        <div id="registros" class="mt-3">
                                            @forelse($feedings as $feeding)
                                                <div class="registro">
                                                    <strong>Data:</strong> {{ $feeding->started_at->format('d/m/Y H:i') }}<br>
                                                    <strong>Duração:</strong> {{ $feeding->formatted_duration }}<br>
                                                    <strong>Quantidade:</strong> {{ $feeding->quantity ? $feeding->quantity . ' mL' : 'não informado' }}
                                                </div>
                                            @empty
                                                <p class="text-muted text-center">Nenhum registro de amamentação encontrado.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna de Alarmes -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span>Alarmes</span>
                                    <button class="btn btn-sm btn-primary" onclick="alarmManager.showAlarmModal()">
                                        <i class="fas fa-plus"></i> Novo Alarme
                                    </button>
                                </div>
                                <div class="card-body" id="alarmsContainer">
                                    @include('dashboard.alarms')
                                </div>
                            </div>
                        </div>

                        <!-- Coluna de Dicas -->
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">Dicas do Dia</div>
                                <div class="card-body" id="tipsContainer">
                                    <!-- As dicas serão carregadas via JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Alarmes -->
@include('dashboard.alarm-modal')

@push('scripts')
<script src="{{ asset('js/alarm-manager.js') }}"></script>
<script src="{{ asset('js/feeding-manager.js') }}"></script>
<script src="{{ asset('js/tips-manager.js') }}"></script>
@endpush

@endsection