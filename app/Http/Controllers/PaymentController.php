<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentTransaction;
use App\Models\BookedHall;
use App\Models\HallEnquiry;

class PaymentController extends Controller
{
    /**
     * Initiate payment for a booked hall
     */
    public function initiatePayment(Request $request, $bookingId)
    {
        try {
            // Find the booked hall
            $bookedHall = BookedHall::with('enquiry')->findOrFail($bookingId);
            
            // Calculate total amount (you can modify this logic as needed)
            $totalAmount = $this->calculateTotalAmount($bookedHall);
            
            // Generate unique transaction number
            $txnNo = 'GD' . now()->format('YmdHis') . rand(100, 999);
            $txnDate = now()->format('YmdHis');
            
            // Prepare payment payload
            $payload = [
                "merchantId" => config('payphi.merchant_id'),
                "merchantTxnNo" => $txnNo,
                "amount" => number_format($totalAmount, 2, '.', ''),
                "currencyCode" => config('payphi.currency_code'), // INR
                "payType" => config('payphi.pay_type'), // Redirect
                "customerEmailID" => $bookedHall->customer_email,
                "customerMobileNo" => $bookedHall->customer_phone,
                "transactionType" => config('payphi.transaction_type'),
                "txnDate" => $txnDate,
                "returnURL" => config('payphi.return_url'),
                "addlParam1" => "BookingID_" . $bookingId,
                "addlParam2" => "Gurudakshina_Payment"
            ];
            
            // Generate secure hash
            $payload['secureHash'] = $this->generateSecureHash($payload);
            
            // Save transaction to database
            $transaction = PaymentTransaction::create([
                'booked_hall_id' => $bookingId,
                'merchant_txn_no' => $txnNo,
                'amount' => $totalAmount,
                'customer_email' => $bookedHall->customer_email,
                'customer_mobile' => $bookedHall->customer_phone,
                'status' => 'initiated'
            ]);
            
            Log::info('Payment initiated for booking ID: ' . $bookingId, [
                'transaction_id' => $transaction->id,
                'merchant_txn_no' => $txnNo,
                'amount' => $totalAmount
            ]);
            
            // Send request to PhiCommerce
            $response = Http::timeout(30)->post(config('payphi.initiate_url'), $payload);
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['redirectURI']) && isset($responseData['tranCtx'])) {
                    $redirectUrl = $responseData['redirectURI'] . '?tranCtx=' . $responseData['tranCtx'];
                    
                    // Update transaction with response
                    $transaction->update([
                        'full_response' => $responseData
                    ]);
                    
                    return redirect()->away($redirectUrl);
                } else {
                    Log::error('Invalid response from PhiCommerce', $responseData);
                    return redirect()->back()->with('error', 'Payment gateway error. Please try again.');
                }
            } else {
                Log::error('PhiCommerce API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return redirect()->back()->with('error', 'Payment gateway is currently unavailable. Please try again later.');
            }
            
        } catch (\Exception $e) {
            Log::error('Payment initiation failed', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'An error occurred while processing payment. Please try again.');
        }
    }

    /**
     * Handle payment response from PhiCommerce
     */
    public function handleResponse(Request $request)
    {
        try {
            Log::info('Payment response received', $request->all());
            
            $merchantTxnNo = $request->input('merchantTxnNo');
            $transactionStatus = $request->input('transactionStatus');
            $responseCode = $request->input('responseCode');
            $payPhiTxnNo = $request->input('payPhiTxnNo');
            
            // Find the transaction
            $transaction = PaymentTransaction::where('merchant_txn_no', $merchantTxnNo)->first();
            
            if (!$transaction) {
                Log::error('Transaction not found for merchant txn no: ' . $merchantTxnNo);
                return view('website.payment-status', [
                    'status' => 'error',
                    'message' => 'Transaction not found'
                ]);
            }
            
            // Update transaction status
            $transaction->update([
                'payphi_txn_no' => $payPhiTxnNo,
                'status' => $transactionStatus,
                'response_code' => $responseCode,
                'full_response' => $request->all(),
                'payment_date' => now()
            ]);
            
            // Update booked hall payment status if payment is successful
            if ($transactionStatus === 'SUCCESS') {
                $bookedHall = $transaction->bookedHall;
                if ($bookedHall) {
                    $bookedHall->update([
                        'paid_amount' => $transaction->amount,
                        'remaining_amount' => max(0, $bookedHall->total_rent - $transaction->amount)
                    ]);
                    
                    // Send WhatsApp notification for successful payment
                    $this->sendPaymentSuccessNotification($bookedHall, $transaction);
                }
                
                return view('website.payment-status', [
                    'status' => 'success',
                    'message' => 'Payment completed successfully!',
                    'transaction' => $transaction,
                    'booking' => $bookedHall
                ]);
            } else {
                return view('website.payment-status', [
                    'status' => 'failed',
                    'message' => 'Payment failed. Please try again.',
                    'transaction' => $transaction
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Payment response handling failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return view('website.payment-status', [
                'status' => 'error',
                'message' => 'An error occurred while processing payment response.'
            ]);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus($merchantTxnNo)
    {
        try {
            $transaction = PaymentTransaction::where('merchant_txn_no', $merchantTxnNo)->first();
            
            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }
            
            $payload = [
                'merchantID' => config('payphi.merchant_id'),
                'merchantTxnNo' => $merchantTxnNo,
                'originalTxnNo' => $merchantTxnNo,
                'transactionType' => config('payphi.status_type'),
                'amount' => number_format($transaction->amount, 2, '.', '')
            ];
            
            $payload['secureHash'] = $this->generateStatusHash($payload);
            
            $response = Http::asForm()->post(config('payphi.command_url'), $payload);
            
            if ($response->successful()) {
                $responseData = $response->body();
                
                // Update transaction with latest status
                $transaction->update([
                    'full_response' => array_merge($transaction->full_response ?? [], ['status_check' => $responseData])
                ]);
                
                return response()->json([
                    'transaction' => $transaction,
                    'gateway_response' => $responseData
                ]);
            }
            
            return response()->json(['error' => 'Status check failed'], 500);
            
        } catch (\Exception $e) {
            Log::error('Payment status check failed', [
                'merchant_txn_no' => $merchantTxnNo,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Status check failed'], 500);
        }
    }

    /**
     * Refund transaction
     */
    public function refundTransaction($merchantTxnNo, Request $request)
    {
        try {
            $transaction = PaymentTransaction::where('merchant_txn_no', $merchantTxnNo)->first();
            
            if (!$transaction || !$transaction->isSuccessful()) {
                return response()->json(['error' => 'Transaction not found or not eligible for refund'], 400);
            }
            
            $refundAmount = $request->input('amount', $transaction->amount);
            $refundRef = 'REF' . now()->format('YmdHis') . rand(100, 999);
            
            $payload = [
                'merchantID' => config('payphi.merchant_id'),
                'merchantTxnNo' => $refundRef,
                'originalTxnNo' => $merchantTxnNo,
                'transactionType' => config('payphi.refund_type'),
                'amount' => number_format($refundAmount, 2, '.', '')
            ];
            
            $payload['secureHash'] = $this->generateStatusHash($payload);
            
            $response = Http::asForm()->post(config('payphi.command_url'), $payload);
            
            if ($response->successful()) {
                // Create refund transaction record
                PaymentTransaction::create([
                    'booked_hall_id' => $transaction->booked_hall_id,
                    'merchant_txn_no' => $refundRef,
                    'amount' => $refundAmount,
                    'customer_email' => $transaction->customer_email,
                    'customer_mobile' => $transaction->customer_mobile,
                    'transaction_type' => 'REFUND',
                    'status' => 'initiated',
                    'full_response' => ['refund_response' => $response->body()]
                ]);
                
                return response()->json([
                    'message' => 'Refund initiated successfully',
                    'refund_reference' => $refundRef,
                    'gateway_response' => $response->body()
                ]);
            }
            
            return response()->json(['error' => 'Refund failed'], 500);
            
        } catch (\Exception $e) {
            Log::error('Refund failed', [
                'merchant_txn_no' => $merchantTxnNo,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Refund failed'], 500);
        }
    }

    /**
     * Calculate total amount for booking
     */
    private function calculateTotalAmount($bookedHall)
    {
        $hallRent = $bookedHall->total_rent ?? 0;
        
        // Get accessories if any
        $accessoriesAmount = 0;
        if ($bookedHall->enquiry && $bookedHall->enquiry->accessorie) {
            $accessoryIds = json_decode($bookedHall->enquiry->accessorie, true);
            if (is_array($accessoryIds)) {
                $accessories = \App\Models\Accessorie::whereIn('id', $accessoryIds)->get();
                $accessoriesAmount = $accessories->sum('price');
            }
        }
        
        $subtotal = $hallRent + $accessoriesAmount;
        $gst = $subtotal * 0.18; // 18% GST
        
        return $subtotal + $gst;
    }

    /**
     * Generate secure hash for payment initiation
     */
    private function generateSecureHash($data)
    {
        $secret = config('payphi.secret');
        $msg = $data['addlParam1'] . $data['addlParam2'] . $data['amount'] . 
               $data['currencyCode'] . $data['customerEmailID'] . $data['customerMobileNo'] . 
               $data['merchantId'] . $data['merchantTxnNo'] . $data['payType'] . 
               $data['returnURL'] . $data['transactionType'] . $data['txnDate'];
        
        return hash_hmac('sha256', $msg, $secret);
    }

    /**
     * Generate secure hash for status/refund operations
     */
    private function generateStatusHash($data)
    {
        $secret = config('payphi.secret');
        $msg = $data['merchantID'] . $data['merchantTxnNo'] . $data['originalTxnNo'] . 
               $data['transactionType'] . $data['amount'];
        
        return hash_hmac('sha256', $msg, $secret);
    }

    /**
     * Send WhatsApp notification for successful payment
     * Template message: "Hello {{1}}, Your vendor booking for {{2}} has been completed. We're all set for you {{3}}. Thank you!"
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
            $curlError = curl_error($ch);
            curl_close($ch);
            
            // Log the WhatsApp notification attempt
            Log::info("📤 Payment Success WhatsApp Notification", [
                'contact' => $contactNumber,
                'customer_name' => $customerName,
                'hall_name' => $hallName,
                'event_date' => $eventDate,
                'transaction_id' => $transaction->merchant_txn_no,
                'amount' => $transaction->amount,
                'http_code' => $httpCode,
                'response' => $response,
                'payload' => $payloadArray,
                'curl_error' => $curlError
            ]);
            
            if ($httpCode == 200) {
                Log::info("✅ Payment success WhatsApp notification sent to {$contactNumber}");
            } else {
                Log::error("❌ Payment success WhatsApp notification failed for {$contactNumber}. HTTP {$httpCode} - {$response}");
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Failed to send payment success WhatsApp notification', [
                'error' => $e->getMessage(),
                'booking_id' => $bookedHall->id,
                'customer_phone' => $bookedHall->customer_phone,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}