class FeedingManager {
    constructor() {
        console.log('Inicializando FeedingManager');
        this.isFeeding = false;
        this.startTime = null;
        this.timer = null;
        this.duration = 0;
        this.seconds = 0;
        this.initializeUI();
    }

    initializeUI() {
        console.log('Inicializando UI');
        // Inicializa os elementos da UI
        this.timerDisplay = document.getElementById('timer');
        this.startButton = document.getElementById('btnStart');
        this.stopButton = document.getElementById('btnStop');
        this.inputSection = document.getElementById('inputSection');
        this.mlInput = document.getElementById('ml');
        this.registrosContainer = document.getElementById('registros');
        this.saveButton = document.getElementById('btnSave');
        this.babySelector = document.getElementById('baby-selector');

        // Log dos elementos encontrados
        console.log('Elementos encontrados:', {
            timerDisplay: this.timerDisplay,
            startButton: this.startButton,
            stopButton: this.stopButton,
            inputSection: this.inputSection,
            mlInput: this.mlInput,
            registrosContainer: this.registrosContainer,
            saveButton: this.saveButton,
            babySelector: this.babySelector
        });

        if (!this.saveButton) {
            console.error('Botão de salvar não encontrado!');
            return;
        }

        // Remover event listeners existentes
        const oldStartHandler = this.start.bind(this);
        const oldStopHandler = this.stop.bind(this);
        const oldSaveHandler = this.registrar.bind(this);

        this.startButton.removeEventListener('click', oldStartHandler);
        this.stopButton.removeEventListener('click', oldStopHandler);
        this.saveButton.removeEventListener('click', oldSaveHandler);

        // Adiciona event listeners
        this.startButton.addEventListener('click', () => {
            console.log('Botão iniciar clicado');
            this.start();
        });
        
        this.stopButton.addEventListener('click', () => {
            console.log('Botão parar clicado');
            this.stop();
        });
        
        this.saveButton.addEventListener('click', () => {
            console.log('Botão salvar clicado');
            this.registrar();
        });

        // Inicializa a exibição
        this.updateDisplay();
    }

    updateDisplay() {
        const min = String(Math.floor(this.seconds / 60)).padStart(2, '0');
        const sec = String(this.seconds % 60).padStart(2, '0');
        this.timerDisplay.textContent = `${min}:${sec}`;
    }

    start() {
        console.log('Método start chamado');
        if (this.isFeeding) {
            console.log('Já está amamentando, retornando');
            return;
        }
        
        if (!confirm('Deseja iniciar a amamentação?')) {
            console.log('Usuário cancelou início da amamentação');
            return;
        }
        
        console.log('Iniciando amamentação');
        this.isFeeding = true;
        this.startTime = new Date();
        this.seconds = 0;
        
        this.timer = setInterval(() => {
            this.seconds++;
            this.updateDisplay();
        }, 1000);

        this.startButton.disabled = true;
        this.stopButton.disabled = false;
        this.inputSection.style.display = 'none';
    }

    stop() {
        console.log('Método stop chamado');
        if (!this.isFeeding) {
            console.log('Não está amamentando, retornando');
            return;
        }
        
        if (!confirm('Deseja parar a amamentação?')) {
            console.log('Usuário cancelou parada da amamentação');
            return;
        }
        
        console.log('Parando amamentação');
        clearInterval(this.timer);
        this.isFeeding = false;
        this.startButton.disabled = false;
        this.stopButton.disabled = true;
        this.inputSection.style.display = 'block';
    }

    async registrar() {
        try {
            if (this.saveButton.disabled) {
                console.log('Registro já em andamento, ignorando clique');
                return;
            }

            this.saveButton.disabled = true;
            
            if (this.seconds === 0) {
                alert('Não há amamentação para registrar.');
                return;
            }

            if (!this.babySelector || !this.babySelector.value) {
                alert('Por favor, selecione um bebê.');
                return;
            }

            const mlInput = this.mlInput.value;
            const ml = mlInput ? parseInt(mlInput) : null;
            const endTime = new Date();

            const requestData = {
                baby_id: this.babySelector.value,
                started_at: this.startTime.toISOString(),
                ended_at: endTime.toISOString(),
                duration: this.seconds,
                quantity: ml
            };

            console.log('Dados para envio:', requestData);

            // Obter token CSRF
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) {
                console.error('Token CSRF não encontrado');
                throw new Error('Token CSRF não encontrado');
            }

            console.log('Token CSRF encontrado:', token.content);

            // Salvar no banco de dados
            const response = await fetch('/dashboard/feedings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token.content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            console.log('Status da resposta:', response.status);
            const data = await response.json();
            console.log('Resposta do servidor:', data);

            if (!response.ok) {
                console.error('Erro na resposta:', {
                    status: response.status,
                    data: data
                });
                if (response.status === 422) {
                    const errorMessage = data.errors ? 
                        Object.values(data.errors).flat().join('\n') : 
                        data.message;
                    throw new Error(errorMessage);
                }
                throw new Error(data.message || `Erro HTTP: ${response.status}`);
            }

            // Resetar tudo
            this.seconds = 0;
            this.updateDisplay();
            this.mlInput.value = '';
            this.inputSection.style.display = 'none';
            this.startButton.disabled = false;
            this.isFeeding = false;

            // Atualizar a lista de registros
            await this.atualizarRegistros();

            alert('Registro salvo com sucesso!');

        } catch (error) {
            console.error('Erro ao salvar registro:', error);
            alert('Erro ao salvar o registro: ' + error.message);
        } finally {
            this.saveButton.disabled = false;
        }
    }

    async atualizarRegistros() {
        try {
            console.log('Atualizando registros...');
            const response = await fetch(`/dashboard/feedings/recent?baby_id=${this.babySelector.value}`);
            const data = await response.json();
            console.log('Registros recebidos:', data);

            const recordsContainer = document.getElementById('registros');
            if (!recordsContainer) {
                console.error('Container de registros não encontrado');
                return;
            }

            recordsContainer.innerHTML = '';

            if (!data.feedings || data.feedings.length === 0) {
                recordsContainer.innerHTML = '<p class="text-muted text-center">Nenhum registro de amamentação encontrado.</p>';
                return;
            }

            data.feedings.forEach(record => {
                const div = document.createElement('div');
                div.className = 'registro';
                
                // Converter para horário de Brasília
                const startedAt = new Date(record.started_at);
                const duration = record.duration;
                const hours = Math.floor(duration / 3600);
                const minutes = Math.floor((duration % 3600) / 60);
                const seconds = duration % 60;
                
                const durationFormatted = hours > 0 
                    ? `${hours}h ${minutes}min ${seconds}s`
                    : minutes > 0 
                        ? `${minutes}min ${seconds}s`
                        : `${seconds}s`;

                // Formatar data e hora no fuso de Brasília
                const dataFormatada = startedAt.toLocaleDateString('pt-BR', {
                    timeZone: 'America/Sao_Paulo'
                });
                const horaFormatada = startedAt.toLocaleTimeString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    timeZone: 'America/Sao_Paulo'
                });

                div.innerHTML = `
                    <strong>Data:</strong> ${dataFormatada} ${horaFormatada}<br>
                    <strong>Duração:</strong> ${durationFormatted}<br>
                    <strong>Quantidade:</strong> ${record.quantity ? record.quantity + ' mL' : 'não informado'}
                `;
                recordsContainer.appendChild(div);
            });
        } catch (error) {
            console.error('Erro ao atualizar registros:', error);
        }
    }
}

// Inicialização quando o DOM estiver carregado
console.log('Script feeding-manager.js carregado');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado - Iniciando FeedingManager');
    if (window.feedingManager) {
        console.log('FeedingManager já existe, não criando novo');
        return;
    }
    window.feedingManager = new FeedingManager();
}); 