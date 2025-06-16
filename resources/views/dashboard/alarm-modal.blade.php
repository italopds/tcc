<div class="modal fade" id="alarmModal" tabindex="-1" aria-labelledby="alarmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alarmModalLabel">Novo Alarme</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="alarmForm">
                    <input type="hidden" id="alarmId" name="alarm_id">
                    <input type="hidden" id="babyId" name="baby_id" value="{{ $baby->id ?? '' }}">

                    <div class="mb-3">
                        <label for="time" class="form-label">Horário</label>
                        <input type="time" class="form-control" id="time" name="time" required>
                    </div>

                    <div class="mb-3">
                        <label for="day_name" class="form-label">Dia da Semana</label>
                        <select class="form-select" id="day_name" name="day_name" required>
                            <option value="">Selecione...</option>
                            <option value="Segunda">Segunda</option>
                            <option value="Terça">Terça</option>
                            <option value="Quarta">Quarta</option>
                            <option value="Quinta">Quinta</option>
                            <option value="Sexta">Sexta</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>

                    <div class="mb-3" id="isActiveContainer" style="display: none;">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active">
                            <label class="form-check-label" for="is_active">Ativo</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveAlarmBtn">Salvar</button>
            </div>
        </div>
    </div>
</div> 