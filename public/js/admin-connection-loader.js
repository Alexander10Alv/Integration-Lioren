/**
 * Connection Loader - Sistema de animación reutilizable
 * Alterna entre spinner y ola cada 3 segundos
 */

class ConnectionLoader {
    constructor() {
        this.overlay = null;
        this.currentAnimation = 'spinner';
        this.animationInterval = null;
        this.stepInterval = null;
        this.currentStep = 0;
        this.steps = [
            'Validando credenciales...',
            'Conectando con Shopify...',
            'Conectando con Lioren...',
            'Creando webhooks...',
            'Sincronizando productos...',
            'Configurando integración...',
            'Finalizando conexión...'
        ];
    }

    /**
     * Crea el HTML del loader
     */
    createLoader() {
        const loaderHTML = `
            <div class="brosito-loader-overlay" id="connectionLoader">
                <!-- Partículas de fondo -->
                <div class="brosito-particles">
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                    <div class="brosito-particle"></div>
                </div>

                <!-- Container de animaciones -->
                <div class="brosito-loader-container">
                    <!-- Animación 1: Spinner -->
                    <div class="brosito-spinner active">
                        <div class="brosito-spinner-ring"></div>
                        <div class="brosito-spinner-ring"></div>
                        <div class="brosito-spinner-ring"></div>
                    </div>

                    <!-- Animación 2: Ola -->
                    <div class="brosito-wave">
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                        <div class="brosito-wave-bar"></div>
                    </div>
                </div>

                <!-- Texto de carga -->
                <div class="brosito-loader-text">
                    <div class="brosito-loader-title">🔌 Conectando Integración</div>
                    <div class="brosito-loader-subtitle">Configurando la conexión...</div>
                    <div class="brosito-loader-steps" id="connectionSteps">
                        ${this.steps.map((step, index) => 
                            `<div class="brosito-loader-step ${index === 0 ? 'active' : ''}" data-step="${index}">${step}</div>`
                        ).join('')}
                    </div>
                </div>
            </div>
        `;

        // Insertar en el body
        document.body.insertAdjacentHTML('beforeend', loaderHTML);
        this.overlay = document.getElementById('connectionLoader');
    }

    /**
     * Alterna entre las animaciones
     */
    toggleAnimation() {
        const spinner = this.overlay.querySelector('.brosito-spinner');
        const wave = this.overlay.querySelector('.brosito-wave');

        if (this.currentAnimation === 'spinner') {
            spinner.classList.remove('active');
            wave.classList.add('active');
            this.currentAnimation = 'wave';
        } else {
            wave.classList.remove('active');
            spinner.classList.add('active');
            this.currentAnimation = 'spinner';
        }
    }

    /**
     * Actualiza el paso actual
     */
    updateStep() {
        const steps = this.overlay.querySelectorAll('.brosito-loader-step');
        
        // Marcar paso anterior como completado
        if (this.currentStep > 0) {
            steps[this.currentStep - 1].classList.remove('active');
            steps[this.currentStep - 1].classList.add('completed');
        }

        // Activar paso actual
        if (this.currentStep < steps.length) {
            steps[this.currentStep].classList.add('active');
            this.currentStep++;
        } else {
            // Reiniciar si llegamos al final
            this.currentStep = 0;
            steps.forEach(step => {
                step.classList.remove('active', 'completed');
            });
            steps[0].classList.add('active');
            this.currentStep = 1;
        }
    }

    /**
     * Muestra el loader
     */
    show() {
        if (!this.overlay) {
            this.createLoader();
        }

        // Mostrar overlay
        this.overlay.classList.add('active');

        // Iniciar alternancia de animaciones cada 3 segundos
        this.animationInterval = setInterval(() => {
            this.toggleAnimation();
        }, 3000);

        // Actualizar pasos cada 2 segundos
        this.stepInterval = setInterval(() => {
            this.updateStep();
        }, 2000);
    }

    /**
     * Muestra mensaje de éxito
     */
    showSuccess(message) {
        if (!this.overlay) return;

        const title = this.overlay.querySelector('.brosito-loader-title');
        const subtitle = this.overlay.querySelector('.brosito-loader-subtitle');
        const stepsContainer = this.overlay.querySelector('.brosito-loader-steps');

        // Cambiar a mensaje de éxito
        title.textContent = '✅ ¡Conexión Exitosa!';
        subtitle.textContent = message || 'Sincronizando productos en segundo plano...';
        
        // Ocultar pasos
        if (stepsContainer) {
            stepsContainer.style.display = 'none';
        }

        // Detener cambio de animaciones pero mantener la actual
        if (this.animationInterval) {
            clearInterval(this.animationInterval);
        }
        if (this.stepInterval) {
            clearInterval(this.stepInterval);
        }
    }

    /**
     * Muestra mensaje de error
     */
    showError(message) {
        if (!this.overlay) return;

        const title = this.overlay.querySelector('.brosito-loader-title');
        const subtitle = this.overlay.querySelector('.brosito-loader-subtitle');
        const stepsContainer = this.overlay.querySelector('.brosito-loader-steps');

        // Cambiar a mensaje de error
        title.textContent = '❌ Error de Conexión';
        title.style.color = '#ef4444';
        subtitle.textContent = message || 'Ocurrió un error al conectar';
        subtitle.style.color = '#ef4444';
        
        // Ocultar pasos
        if (stepsContainer) {
            stepsContainer.style.display = 'none';
        }

        // Detener animaciones
        if (this.animationInterval) {
            clearInterval(this.animationInterval);
        }
        if (this.stepInterval) {
            clearInterval(this.stepInterval);
        }
    }

    /**
     * Oculta el loader
     */
    hide() {
        if (this.overlay) {
            this.overlay.classList.remove('active');
            
            // Limpiar intervalos
            if (this.animationInterval) {
                clearInterval(this.animationInterval);
                this.animationInterval = null;
            }
            if (this.stepInterval) {
                clearInterval(this.stepInterval);
                this.stepInterval = null;
            }

            // Resetear estado
            this.currentAnimation = 'spinner';
            this.currentStep = 0;

            // Remover del DOM después de la transición
            setTimeout(() => {
                if (this.overlay && this.overlay.parentNode) {
                    this.overlay.parentNode.removeChild(this.overlay);
                    this.overlay = null;
                }
            }, 300);
        }
    }

    /**
     * Destruye el loader completamente
     */
    destroy() {
        this.hide();
    }
}

// Instancia global
window.connectionLoader = new ConnectionLoader();

/**
 * Funciones de conveniencia
 */
window.showConnectionLoader = function() {
    window.connectionLoader.show();
};

window.hideConnectionLoader = function() {
    window.connectionLoader.hide();
};

window.showConnectionSuccess = function(message) {
    window.connectionLoader.showSuccess(message);
};

window.showConnectionError = function(message) {
    window.connectionLoader.showError(message);
};
