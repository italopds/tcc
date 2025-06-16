class TipsManager {
    constructor() {
        console.log('Inicializando TipsManager');
        this.tipsContainer = document.getElementById('tipsContainer');
        this.babySelector = document.getElementById('baby-selector');
        this.currentTipIndex = 0;
        this.tips = [
            {
                title: 'Postura Correta',
                content: 'Mantenha o bebê com a cabeça alinhada ao corpo e o queixo tocando o seio. Isso ajuda na pega correta e evita dores.',
                category: 'Amamentação'
            },
            {
                title: 'Hidratação',
                content: 'Beba bastante água durante a amamentação para manter a produção de leite. Recomenda-se 3-4 litros por dia.',
                category: 'Saúde'
            },
            {
                title: 'Rotina de Sono',
                content: 'Estabeleça uma rotina de sono desde cedo. Banho, massagem e música suave ajudam o bebê a relaxar.',
                category: 'Sono'
            },
            {
                title: 'Cólicas',
                content: 'Massagens suaves na barriga e movimentos de bicicleta com as perninhas ajudam a aliviar as cólicas.',
                category: 'Saúde'
            },
            {
                title: 'Introdução Alimentar',
                content: 'Comece com alimentos pastosos e aumente gradualmente a consistência. Ofereça um alimento novo por vez.',
                category: 'Alimentação'
            },
            {
                title: 'Desenvolvimento Motor',
                content: 'Incentive o bebê a engatinhar e se movimentar. Coloque brinquedos a uma distância que ele precise se esforçar para alcançar.',
                category: 'Desenvolvimento'
            }
        ];
        this.initializeUI();
    }

    initializeUI() {
        if (!this.tipsContainer) {
            console.error('Container de dicas não encontrado');
            return;
        }

        // Mostrar as primeiras dicas
        this.showCurrentTips();

        // Iniciar o carrossel
        this.startCarousel();

        // Atualizar dicas quando o bebê for alterado
        if (this.babySelector) {
            this.babySelector.addEventListener('change', () => {
                this.resetCarousel();
            });
        }
    }

    showCurrentTips() {
        const currentTip = this.tips[this.currentTipIndex];
        const nextTipIndex = (this.currentTipIndex + 1) % this.tips.length;
        const nextTip = this.tips[nextTipIndex];

        this.tipsContainer.innerHTML = `
            <div class="tip-item mb-3 fade-in">
                <h6>${currentTip.title}</h6>
                <p>${currentTip.content}</p>
                <small class="text-muted">Categoria: ${currentTip.category}</small>
            </div>
            <div class="tip-item mb-3 fade-in">
                <h6>${nextTip.title}</h6>
                <p>${nextTip.content}</p>
                <small class="text-muted">Categoria: ${nextTip.category}</small>
            </div>
        `;
    }

    nextTips() {
        this.currentTipIndex = (this.currentTipIndex + 2) % this.tips.length;
        this.showCurrentTips();
    }

    startCarousel() {
        this.carouselInterval = setInterval(() => {
            this.nextTips();
        }, 10000); // 10 segundos
    }

    resetCarousel() {
        clearInterval(this.carouselInterval);
        this.currentTipIndex = 0;
        this.showCurrentTips();
        this.startCarousel();
    }
}

// Inicialização quando o DOM estiver carregado
console.log('Script tips-manager.js carregado');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado - Iniciando TipsManager');
    if (window.tipsManager) {
        console.log('TipsManager já existe, não criando novo');
        return;
    }
    window.tipsManager = new TipsManager();
}); 