<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Redirecting to PayHere...</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f8fafc; }
        .box { text-align: center; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .spinner { width: 40px; height: 40px; border: 3px solid #e2e8f0; border-top-color: #6366f1; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
        p { color: #64748b; margin: 0; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="box">
        <div class="spinner"></div>
        <p>Opening PayHere payment...</p>
    </div>
    <script type="text/javascript" src="https://www.payhere.lk/lib/payhere.js"></script>
    <script>
        (function() {
            var payment = @json($payment);
            payment.return_url = payment.return_url || undefined;
            payment.cancel_url = payment.cancel_url || undefined;

            payhere.onCompleted = function(orderId) {
                var sep = payment.return_url.indexOf('?') >= 0 ? '&' : '?';
                window.location.href = payment.return_url + sep + 'order_id=' + encodeURIComponent(orderId);
            };
            payhere.onDismissed = function() {
                if (payment.cancel_url) {
                    window.location.href = payment.cancel_url;
                } else {
                    window.history.back();
                }
            };
            payhere.onError = function(error) {
                console.error('PayHere error:', error);
                alert('Payment error: ' + (error || 'Unknown error'));
                window.history.back();
            };

            payhere.startPayment(payment);
        })();
    </script>
</body>
</html>
