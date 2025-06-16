class AlarmManager {
    constructor() {
        this.initializeEventListeners();
        this.modal = null;
    }

    initializeEventListeners() {
        // Event listener para o seletor de bebê
        document.getElementById('baby-selector').addEventListener('change', (e) => {
            this.loadAlarms(e.target.value);
        });

        // Event listener para os toggles de alarme
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('alarm-toggle')) {
                this.toggleAlarm(e.target);
            }
        });

        // Event listener para o botão de salvar no modal
        document.getElementById('saveAlarmBtn').addEventListener('click', () => {
            this.saveAlarm();
        });
    }

    async loadAlarms(babyId) {
        try {
            const response = await fetch(`/babies/${babyId}/alarms`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await response.json();
            
            const container = document.getElementById('alarmsContainer');
            container.innerHTML = data.html;
        } catch (error) {
            console.error('Erro ao carregar alarmes:', error);
            alert('Erro ao carregar os alarmes. Por favor, tente novamente.');
        }
    }

    async toggleAlarm(toggleElement) {
        const alarmId = toggleElement.dataset.alarmId;
        const isActive = toggleElement.checked;

        try {
            const response = await fetch(`/api/alarms/${alarmId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ is_active: isActive })
            });

            if (!response.ok) {
                throw new Error('Erro ao atualizar o alarme');
            }

            const data = await response.json();
            if (data.success) {
                // Atualiza o estado do toggle
                toggleElement.checked = isActive;
            } else {
                // Reverte o estado do toggle em caso de erro
                toggleElement.checked = !isActive;
                alert(data.message || 'Erro ao atualizar o alarme');
            }
        } catch (error) {
            console.error('Erro ao atualizar alarme:', error);
            toggleElement.checked = !isActive; // Reverte o estado do toggle
            alert('Erro ao atualizar o alarme. Por favor, tente novamente.');
        }
    }

    showAlarmModal(alarm = null) {
        const modal = document.getElementById('alarmModal');
        const modalTitle = modal.querySelector('.modal-title');
        const form = document.getElementById('alarmForm');
        const isActiveContainer = document.getElementById('isActiveContainer');
        const babyId = document.getElementById('babyId');

        // Limpa o formulário
        form.reset();
        document.getElementById('alarmId').value = '';

        if (alarm) {
            // Modo de edição
            modalTitle.textContent = 'Editar Alarme';
            document.getElementById('alarmId').value = alarm.id;
            document.getElementById('time').value = alarm.time;
            document.getElementById('day_name').value = alarm.day_name;
            document.getElementById('is_active').checked = alarm.is_active;
            isActiveContainer.style.display = 'block';
        } else {
            // Modo de criação
            modalTitle.textContent = 'Novo Alarme';
            isActiveContainer.style.display = 'none';
        }

        // Atualiza o ID do bebê
        babyId.value = document.getElementById('baby-selector').value;

        // Mostra o modal
        this.modal = new bootstrap.Modal(modal);
        this.modal.show();
    }

    async saveAlarm() {
        try {
            const form = document.getElementById('alarmForm');
            const formData = new FormData(form);
            const alarmId = formData.get('alarm_id');
            const isEdit = !!alarmId;

            // Validar dados antes de enviar
            const time = formData.get('time');
            const dayName = formData.get('day_name');
            const babyId = formData.get('baby_id');

            if (!time || !dayName || !babyId) {
                throw new Error('Por favor, preencha todos os campos obrigatórios.');
            }

            // Obter token CSRF
            const token = document.querySelector('meta[name="csrf-token"]');
            if (!token) {
                throw new Error('Token CSRF não encontrado');
            }

            const response = await fetch(`/alarms${isEdit ? `/${alarmId}` : ''}`, {
                method: isEdit ? 'PUT' : 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    baby_id: babyId,
                    time: time,
                    day_name: dayName,
                    is_active: formData.get('is_active') === 'on'
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Erro ao salvar o alarme');
            }

            if (data.success) {
                // Fecha o modal
                this.modal.hide();
                // Recarrega os alarmes
                await this.loadAlarms(babyId);
                alert('Alarme salvo com sucesso!');
            } else {
                throw new Error(data.message || 'Erro ao salvar o alarme');
            }
        } catch (error) {
            console.error('Erro ao salvar alarme:', error);
            alert(error.message || 'Erro ao salvar o alarme. Por favor, tente novamente.');
        }
    }

    async deleteAlarm(alarmId) {
        if (!confirm('Tem certeza que deseja excluir este alarme?')) {
            return;
        }

        try {
            const response = await fetch(`/api/alarms/${alarmId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) {
                throw new Error('Erro ao excluir o alarme');
            }

            const data = await response.json();
            if (data.success) {
                // Recarrega os alarmes
                this.loadAlarms(document.getElementById('baby-selector').value);
            } else {
                alert(data.message || 'Erro ao excluir o alarme');
            }
        } catch (error) {
            console.error('Erro ao excluir alarme:', error);
            alert('Erro ao excluir o alarme. Por favor, tente novamente.');
        }
    }
}

// Inicializa o gerenciador de alarmes
const alarmManager = new AlarmManager(); 