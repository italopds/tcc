@if($notifications->isEmpty())
    <div class="alert alert-info">
        Nenhuma notificação encontrada.
    </div>
@else
    @foreach($notifications as $notification)
        <div class="notification-item d-flex justify-content-between align-items-center mb-3 p-3 {{ $notification->is_read ? 'bg-light' : 'bg-white' }}" 
             style="border: 1px solid #e2e8f0; border-radius: 8px;">
            <div>
                <h6 class="mb-1">{{ $notification->title }}</h6>
                <p class="mb-1 text-muted">{{ $notification->message }}</p>
                <small class="text-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</small>
            </div>
            @if(!$notification->is_read)
                <button class="btn btn-sm btn-outline-primary mark-as-read" 
                        data-notification-id="{{ $notification->id }}">
                    <i class="fas fa-check"></i>
                </button>
            @endif
        </div>
    @endforeach
@endif 