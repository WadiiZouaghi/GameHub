document.addEventListener('DOMContentLoaded', async () => {
    if (!window.Square) {
        showError('Payment system failed to load. Please refresh the page.');
        return;
    }

    let payments;
    try {
        payments = await window.Square.payments(applicationId, locationId);
    } catch (e) {
        showError('Payment system initialization failed. Please check your connection.');
        return;
    }

    let card;
    try {
        card = await payments.card();
        await card.attach('#card-container');
    } catch (e) {
        showError('Failed to load payment form. Please refresh the page.');
        return;
    }

    const form = document.getElementById('payment-form');
    const button = document.getElementById('card-button');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        hideError();

        try {
            const result = await card.tokenize();
            
            if (result.status === 'OK') {
                await processPayment(result.token);
            } else {
                let errorMessage = 'Payment failed. Please try again.';
                
                if (result.errors) {
                    errorMessage = result.errors.map(error => error.message).join(', ');
                }
                
                showError(errorMessage);
                resetButton();
            }
        } catch (e) {
            console.error('Payment error:', e);
            showError('An unexpected error occurred. Please try again.');
            resetButton();
        }
    });

    async function processPayment(token) {
        try {
            const response = await fetch('/purchase/process-payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    sourceId: token,
                    gameId: gameId,
                    amount: Math.round(gamePrice * 100)
                })
            });
            
            const text = await response.text();
            
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                showError('Server error. Please try again.');
                resetButton();
                return;
            }

            if (response.ok && data.success) {
                button.innerHTML = '<i class="fas fa-check"></i> Payment Successful!';
                
                // Trigger confetti animation
                if (window.GameHubAnimations && typeof window.GameHubAnimations.confetti === 'function') {
                    const rect = button.getBoundingClientRect();
                    window.GameHubAnimations.confetti(rect.left + rect.width / 2, rect.top + rect.height / 2);
                }
                button.classList.remove('btn-hero');
                button.classList.add('bg-green-500', 'hover:bg-green-600');
                
                setTimeout(() => {
                    window.location.href = data.redirectUrl || '/library';
                }, 1500);
            } else {
                showError(data.message || 'Payment processing failed. Please try again.');
                resetButton();
            }
        } catch (e) {
            showError('Failed to process payment. Please try again.');
            resetButton();
        }
    }

    function showError(message) {
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        errorText.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    function hideError() {
        const errorDiv = document.getElementById('error-message');
        errorDiv.classList.add('hidden');
    }

    function resetButton() {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-lock"></i><span>Pay $' + gamePrice.toFixed(2) + '</span>';
    }
});
