/**
 * Payment Callback Handler
 * This script handles the payment callback from Peach Payments
 * It sends the payment verification request to the server and processes the response
 */
document.addEventListener('DOMContentLoaded', function() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success') === 'true';
    const reference = urlParams.get('reference') || urlParams.get('merchantTransactionId');
    const betId = urlParams.get('bet_id');
    
    // Create the notification element if it doesn't exist
    let notification = document.getElementById('payment-notification');
    if (!notification) {
        notification = document.createElement('div');
        notification.id = 'payment-notification';
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.left = '50%';
        notification.style.transform = 'translateX(-50%)';
        notification.style.padding = '15px 20px';
        notification.style.borderRadius = '5px';
        notification.style.color = 'white';
        notification.style.zIndex = '1000';
        notification.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
        notification.style.display = 'none';
        document.body.appendChild(notification);
    }
    
    // Show processing notification
    notification.textContent = 'Processing payment...';
    notification.style.backgroundColor = '#3498db';
    notification.style.display = 'block';
    
    // If we have a reference, verify the payment
    if (reference) {
        // Build API endpoint URL - use the same URL pattern as the current page, but append 'api=true'
        let apiUrl = window.location.pathname + '?api=true';
        if (success !== null) apiUrl += '&success=' + success;
        if (reference) apiUrl += '&reference=' + encodeURIComponent(reference);
        if (betId) apiUrl += '&bet_id=' + encodeURIComponent(betId);
        
        // Send verification request
        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(result => {
                console.log('Payment verification result:', result);
                
                if (result.success) {
                    // Payment was successful
                    notification.textContent = 'Payment successful! Redirecting...';
                    notification.style.backgroundColor = '#4CAF50';
                    
                    // Redirect to the redirect URL provided by the server
                    setTimeout(() => {
                        window.location.href = result.redirect_url || '/my_bets.php';
                    }, 2000);
                } else {
                    // Payment failed
                    notification.textContent = result.error || 'Payment verification failed';
                    notification.style.backgroundColor = '#ef4444';
                    
                    // Redirect to the error URL provided by the server
                    setTimeout(() => {
                        window.location.href = result.redirect_url || '/my_bets.php';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                notification.textContent = 'An error occurred. Please try again.';
                notification.style.backgroundColor = '#ef4444';
                
                // Redirect to my bets page after a delay
                setTimeout(() => {
                    window.location.href = '/my_bets.php';
                }, 3000);
            });
    } else {
        // No payment reference found
        notification.textContent = 'Payment reference not found';
        notification.style.backgroundColor = '#ef4444';
        
        // Redirect to my bets page after a delay
        setTimeout(() => {
            window.location.href = '/my_bets.php';
        }, 3000);
    }
}); 