<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentTransaction;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Log;

class CheckPendingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update status of pending payments';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('🔄 Starting pending payments check...');
        
        // Get all pending transactions that are older than 5 minutes
        $pendingTransactions = PaymentTransaction::where('status', 'PENDING')
            ->where('created_at', '<=', now()->subMinutes(5))
            ->where('created_at', '>=', now()->subHours(24)) // Don't check transactions older than 24 hours
            ->get();

        Log::info('📊 Found ' . $pendingTransactions->count() . ' pending transactions to check');

        $successCount = 0;
        $failedCount = 0;
        $stillPendingCount = 0;

        foreach ($pendingTransactions as $transaction) {
            try {
                Log::info('🔍 Checking transaction: ' . $transaction->merchant_txn_no);
                
                $finalStatus = $this->checkTransactionStatus($transaction);
                
                if ($finalStatus && $finalStatus !== 'PENDING') {
                    $transaction->update(['status' => $finalStatus]);
                    
                    if ($finalStatus === 'SUCCESS') {
                        $successCount++;
                        Log::info('✅ Transaction ' . $transaction->merchant_txn_no . ' updated to SUCCESS');
                        
                        // Update booking and send notification
                        $this->processSuccessfulPayment($transaction);
                    } else {
                        $failedCount++;
                        Log::info('❌ Transaction ' . $transaction->merchant_txn_no . ' updated to FAILED');
                    }
                } else {
                    $stillPendingCount++;
                    Log::info('⏳ Transaction ' . $transaction->merchant_txn_no . ' still pending');
                }
                
                // Add a small delay to avoid overwhelming the API
                sleep(1);
                
            } catch (\Exception $e) {
                Log::error('💥 Error checking transaction ' . $transaction->merchant_txn_no . ': ' . $e->getMessage());
            }
        }

        Log::info('✅ Pending payments check completed:', [
            'total_checked' => $pendingTransactions->count(),
            'success' => $successCount,
            'failed' => $failedCount,
            'still_pending' => $stillPendingCount
        ]);

        $this->info("Checked {$pendingTransactions->count()} pending transactions:");
        $this->info("✅ Success: {$successCount}");
        $this->info("❌ Failed: {$failedCount}");
        $this->info("⏳ Still Pending: {$stillPendingCount}");

        return Command::SUCCESS;
    }

    /**
     * Check transaction status via API
     */
    private function checkTransactionStatus($transaction)
    {
        try {
            $payload = [
                'merchantID' => config('payphi.merchant_id'),
                'merchantTxnNo' => $transaction->merchant_txn_no,
                'originalTxnNo' => $transaction->merchant_txn_no,
                'transactionType' => config('payphi.status_type', 'STATUS'),
                'amount' => number_format($transaction->amount, 2, '.', '')
            ];
            
            $secret = config('payphi.secret');
            // Use the correct hash format for status check
            $msg = $payload['amount'] . $payload['merchantID'] . $payload['merchantTxnNo'] . $payload['transactionType'];
            $payload['secureHash'] = hash_hmac('sha256', $msg, $secret);
            
            $response = \Illuminate\Support\Facades\Http::timeout(30)
                ->asForm()
                ->post(config('payphi.command_url'), $payload);
            
            if ($response->successful()) {
                $responseData = json_decode($response->body(), true);
                
                if ($responseData) {
                    $statusResponseCode = $responseData['responseCode'] ?? null;
                    $invoiceStatus = $responseData['invoiceStatus'] ?? null;
                    $paidAmount = $responseData['paidAmount'] ?? null;
                    
                    // Update transaction with status check response
                    $transaction->update([
                        'full_response' => array_merge($transaction->full_response ?? [], ['status_check_' . now()->timestamp => $responseData])
                    ]);
                    
                    // Determine final status
                    if ($statusResponseCode === '0000' && ($invoiceStatus === 'Paid' || $paidAmount > 0)) {
                        return 'SUCCESS';
                    } elseif ($invoiceStatus === 'Rejected' || $invoiceStatus === 'Failed') {
                        return 'FAILED';
                    } else {
                        return 'PENDING';
                    }
                }
            }
            
            return 'PENDING';
            
        } catch (\Exception $e) {
            Log::error('Status check failed for ' . $transaction->merchant_txn_no . ': ' . $e->getMessage());
            return 'PENDING';
        }
    }

    /**
     * Process successful payment
     */
    private function processSuccessfulPayment($transaction)
    {
        try {
            $bookedHall = $transaction->bookedHall;
            if ($bookedHall) {
                $bookedHall->update([
                    'paid_amount' => $transaction->amount,
                    'remaining_amount' => max(0, $bookedHall->total_rent - $transaction->amount)
                ]);
                
                // Send WhatsApp notification
                $this->sendPaymentSuccessNotification($bookedHall, $transaction);
            }
        } catch (\Exception $e) {
            Log::error('Error processing successful payment for ' . $transaction->merchant_txn_no . ': ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp notification for successful payment
     */
    private function sendPaymentSuccessNotification($bookedHall, $transaction)
    {
        try {
            $apiUrl = config('oneclick.api_url') . "/" . config('oneclick.api_version') . "/" . config('oneclick.phone_id') . "/messages";
            $token = trim(config('oneclick.api_token'));
            
            // Format contact number
            $contactNumber = "+91" . $bookedHall->customer_phone;
            
            // Prepare template variables
            $customerName = $bookedHall->customer_name;
            $hallName = $bookedHall->hall_name;
            $eventDate = $bookedHall->event_date;
            
            // Build payload using the new template format
            $payloadArray = [
                "to" => $contactNumber,
                "recipient_type" => "individual",
                "type" => "template",
                "template" => [
                    "language" => [
                        "policy" => "deterministic",
                        "code" => "en"
                    ],
                    "name" => "template10_clone",
                    "components" => [
                        [
                            "type" => "body",
                            "parameters" => [
                                [
                                    "type" => "text",
                                    "text" => $customerName
                                ],
                                [
                                    "type" => "text", 
                                    "text" => $hallName
                                ],
                                [
                                    "type" => "text",
                                    "text" => $eventDate
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            
            $payload = json_encode($payloadArray);
            
            // Send cURL request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $token,
                "Content-Type: application/json"
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200) {
                Log::info("✅ Payment success WhatsApp notification sent to {$contactNumber} for transaction {$transaction->merchant_txn_no}");
            } else {
                Log::error("❌ Payment success WhatsApp notification failed for {$contactNumber}. HTTP {$httpCode}");
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Failed to send payment success WhatsApp notification: ' . $e->getMessage());
        }
    }
}