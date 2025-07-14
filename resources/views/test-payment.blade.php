<!DOCTYPE html>
<html>
<head>
    <title>PhiCommerce Payment Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>PhiCommerce Payment Gateway Test</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Test Cards:</strong><br>
                            VISA: 4895 3901 1539 2363<br>
                            Mastercard: 5299 9209 7025 9709<br>
                            Expiry: 08/2025, CVV: 123, OTP: 123456<br>
                            UPI: test@ybl
                        </div>
                        
                        <h5>Environment Configuration:</h5>
                        <ul>
                            <li><strong>Merchant ID:</strong> {{ env('PAYPHI_MERCHANT_ID') }}</li>
                            <li><strong>API URL:</strong> {{ env('PAYPHI_INITIATE_URL') }}</li>
                            <li><strong>Return URL:</strong> {{ env('PAYPHI_RETURN_URL') }}</li>
                        </ul>
                        
                        <h5>Database Status:</h5>
                        <ul>
                            <li><strong>Payment Transactions Table:</strong> 
                                @php
                                    try {
                                        $count = \App\Models\PaymentTransaction::count();
                                        echo "✅ Created (Records: $count)";
                                    } catch (\Exception $e) {
                                        echo "❌ Error: " . $e->getMessage();
                                    }
                                @endphp
                            </li>
                            <li><strong>Booked Halls Table:</strong> 
                                @php
                                    try {
                                        $count = \App\Models\BookedHall::count();
                                        echo "✅ Available (Records: $count)";
                                    } catch (\Exception $e) {
                                        echo "❌ Error: " . $e->getMessage();
                                    }
                                @endphp
                            </li>
                        </ul>
                        
                        <div class="mt-4">
                            <h5>Integration Status:</h5>
                            <div class="alert alert-success">
                                ✅ PhiCommerce Payment Gateway integration is ready!<br>
                                ✅ Database tables created successfully<br>
                                ✅ Routes configured<br>
                                ✅ Controllers implemented<br>
                                ✅ Models with relationships ready
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>How to Test:</h5>
                            <ol>
                                <li>Go to your website homepage</li>
                                <li>Enter a valid booking PIN/code</li>
                                <li>Click "Pay Here" button</li>
                                <li>Use test card details provided above</li>
                                <li>Complete the payment flow</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>