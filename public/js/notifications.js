// Verificar se o navegador suporta notificações
if ('serviceWorker' in navigator && 'PushManager' in window) {
    console.log('Service Worker e Push são suportados');

    // Função para solicitar permissão de notificações
    async function requestNotificationPermission() {
        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                console.log('Permissão de notificações concedida');
                return true;
            } else {
                console.warn('Permissão de notificações negada');
                return false;
            }
        } catch (error) {
            console.error('Erro ao solicitar permissão:', error);
            return false;
        }
    }

    // Função para registrar o Service Worker e se inscrever para notificações
    async function registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('Service Worker registrado com sucesso:', registration);

            const permissionGranted = await requestNotificationPermission();
            if (!permissionGranted) {
                throw new Error('Permissão de notificações negada');
            }

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array('BBBDWQYtX_cPVZKxTFGCdqIB_M8SHRuBttPc4j8_xTsP5SNYqCAypc36co8hBaxI_uKD1vIYQedeu282RNwgFuc')
            });

            console.log('Usuário inscrito:', subscription);

            const response = await fetch('/notifications/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ subscription })
            });

            if (!response.ok) {
                throw new Error('Falha ao salvar inscrição no servidor');
            }

            console.log('Inscrição salva com sucesso no servidor');
            return true;
        } catch (error) {
            console.error('Erro ao configurar notificações:', error);
            return false;
        }
    }

    // Iniciar o processo de registro
    registerServiceWorker();
} else {
    console.warn('Push messaging não é suportado neste navegador');
}

// Função auxiliar para converter a chave VAPID
function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

// Marcar notificação como lida
document.querySelectorAll('.mark-as-read').forEach(button => {
    button.addEventListener('click', async function() {
        const notificationId = this.dataset.notificationId;
        try {
            const response = await fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                // Atualizar a UI
                const notificationItem = this.closest('.notification-item');
                notificationItem.classList.remove('bg-white');
                notificationItem.classList.add('bg-light');
                this.remove();
            } else {
                console.error('Erro ao marcar notificação como lida');
            }
        } catch (error) {
            console.error('Erro ao marcar notificação como lida:', error);
        }
    });
}); 