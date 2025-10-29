<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\BookedHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentTransactionController extends Controller
{
    /**
     * Display a listing of payment transactions
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['bookedHall.enquiry'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->where('transaction_type', $request->payment_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by transaction ID or customer details
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('merchant_txn_no', 'like', "%{$search}%")
                  ->orWhere('payphi_txn_no', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_mobile', 'like', "%{$search}%")
                  ->orWhereHas('bookedHall', function($bq) use ($search) {
                      $bq->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('hall_name', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->simplePaginate(20);

        // Get summary statistics
        $stats = $this->getPaymentStats();

        return view('admin.PaymentTransactions.index', compact('transactions', 'stats'));
    }

    /**
     * Show the specified payment transaction
     */
    public function show($id)
    {
        $transaction = PaymentTransaction::with(['bookedHall.enquiry'])->findOrFail($id);

        return view('admin.PaymentTransactions.show', compact('transaction'));
    }

    /**
     * Get payment statistics
     */
    private function getPaymentStats()
    {
        return [
            'total_transactions' => PaymentTransaction::count(),
            'successful_transactions' => PaymentTransaction::where('status', 'SUCCESS')->count(),
            'failed_transactions' => PaymentTransaction::where('status', 'FAILED')->count(),
            'pending_transactions' => PaymentTransaction::where('status', 'initiated')->count(),
            'total_amount' => PaymentTransaction::where('status', 'SUCCESS')->sum('amount'),
            'deposit_payments' => PaymentTransaction::where('transaction_type', 'deposit')->where('status', 'SUCCESS')->count(),
            'rent_payments' => PaymentTransaction::where('transaction_type', 'rent')->where('status', 'SUCCESS')->count(),
            'full_payments' => PaymentTransaction::where('transaction_type', 'full')->where('status', 'SUCCESS')->count(),
        ];
    }

    /**
     * Export payment transactions
     */
    public function export(Request $request)
    {
        $query = PaymentTransaction::with(['bookedHall.enquiry'])
            ->orderBy('created_at', 'desc');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->where('transaction_type', $request->payment_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->get();

        $filename = 'payment_transactions_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Transaction ID',
                'Gateway Ref',
                'Customer Name',
                'Customer Email',
                'Customer Mobile',
                'Hall Name',
                'Payment Type',
                'Amount',
                'Status',
                'Payment Date',
                'Created At'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->merchant_txn_no,
                    $transaction->payphi_txn_no ?? 'N/A',
                    $transaction->bookedHall->customer_name ?? 'N/A',
                    $transaction->customer_email,
                    $transaction->customer_mobile,
                    $transaction->bookedHall->hall_name ?? 'N/A',
                    ucfirst($transaction->transaction_type ?? 'N/A'),
                    $transaction->amount,
                    $transaction->status,
                    $transaction->payment_date ? $transaction->payment_date->format('Y-m-d H:i:s') : 'N/A',
                    $transaction->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get payment analytics data
     */
    public function analytics()
    {
        // Monthly payment trends
        $monthlyData = PaymentTransaction::where('status', 'SUCCESS')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Payment type distribution
        $paymentTypeData = PaymentTransaction::where('status', 'SUCCESS')
            ->select('transaction_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('transaction_type')
            ->get();

        // Status distribution
        $statusData = PaymentTransaction::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return view('admin.PaymentTransactions.analytics', compact('monthlyData', 'paymentTypeData', 'statusData'));
    }

    /**
     * Generate secure hash for status/refund operations
     */
    private function generateStatusHash($data)
    {
        $secret = config('payphi.secret');

        // For PhiCommerce status check API, the hash format is:
        // amount + merchantID + merchantTxnNo + transactionType
        $msg = $data['amount'] . $data['merchantID'] . $data['merchantTxnNo'] . $data['transactionType'];

        Log::info('🔐 Generating secure hash for status check:', [
            'transaction_type' => $data['transactionType'],
            'message_string' => $msg,
            'secret_length' => strlen($secret)
        ]);

        $hash = hash_hmac('sha256', $msg, $secret);
        Log::info('🔑 Generated hash: ' . $hash);

        return $hash;
    }

    /**
     * Refund a transaction
     */
    public function refund(Request $request, $id)
    {
        $request->validate([
            'refund_amount' => 'required|numeric|min:0.01',
            'refund_reason' => 'required|string|max:255'
        ]);

        $transaction = PaymentTransaction::findOrFail($id);

        if (!$transaction->isSuccessful()) {
            return back()->with('error', 'Only successful transactions can be refunded.');
        }

        if ($request->refund_amount > $transaction->amount) {
            return back()->with('error', 'Refund amount cannot exceed the original transaction amount.');
        }

        try {
            // Generate refund reference
            $refundRef = 'REF' . now()->format('YmdHis') . rand(100, 999);

            // Prepare refund payload for PhiCommerce
            $refundPayload = [
                'merchantID' => config('payphi.merchant_id'),
                'merchantTxnNo' => $refundRef,
                'originalTxnNo' => $transaction->merchant_txn_no,
                'transactionType' => config('payphi.refund_type'),
                'amount' => number_format($request->refund_amount, 2, '.', '')
            ];

            // Generate secure hash
            $refundPayload['secureHash'] = $this->generateStatusHash($refundPayload);

            Log::info('💰 Initiating refund via PhiCommerce:', [
                'original_txn' => $transaction->merchant_txn_no,
                'refund_txn' => $refundRef,
                'amount' => $request->refund_amount,
                'gateway_payload' => $refundPayload // Exclude secureHash from log
            ]);

            // Send refund request to PhiCommerce
            $response = Http::timeout(30)->asForm()->post(config('payphi.command_url'), $refundPayload);

            // Prepare refund transaction data
            $refundFullResponse = [
                'refund_reason' => $request->refund_reason,
                'original_transaction' => $transaction->merchant_txn_no,
                'refunded_by' => auth()->user()->name ?? 'Admin'
            ];

            if ($response->successful()) {
                // Refund initiated successfully
                $refundStatus = 'SUCCESS';
                $refundFullResponse['gateway_refund_response'] = $response->body();

                // Update booked hall paid amount if necessary
                // This would depend on business logic - refunding might reduce paid_amount
                $bookedHall = $transaction->bookedHall;
                if ($bookedHall) {
                    // Reduce the paid amount by refund amount
                    $bookedHall->paid_amount = max(0, $bookedHall->paid_amount - $request->refund_amount);
                    $bookedHall->save();
                }

                Log::info('✅ Refund processed successfully:', [
                    'refund_reference' => $refundRef,
                    'gateway_response' => $response->body()
                ]);
            } else {
                // Refund failed at gateway
                $refundStatus = 'FAILED';
                $refundFullResponse['gateway_refund_error'] = $response->body();

                Log::error('❌ Refund failed at PhiCommerce:', [
                    'refund_reference' => $refundRef,
                    'status_code' => $response->status(),
                    'response' => $response->body()
                ]);
            }

            // Create refund transaction record
            PaymentTransaction::create([
                'booked_hall_id' => $transaction->booked_hall_id,
                'merchant_txn_no' => $refundRef,
                'amount' => $request->refund_amount,
                'customer_email' => $transaction->customer_email,
                'customer_mobile' => $transaction->customer_mobile,
                'transaction_type' => 'REFUND',
                'status' => $refundStatus,
                'full_response' => $refundFullResponse
            ]);

            if ($refundStatus === 'SUCCESS') {
                return back()->with('success', 'Refund processed successfully. Reference: ' . $refundRef);
            } else {
                return back()->with('error', 'Refund failed at payment gateway. Reference: ' . $refundRef);
            }

        } catch (\Exception $e) {
            Log::error('💥 Refund processing failed:', [
                'original_transaction' => $transaction->merchant_txn_no,
                'amount' => $request->refund_amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to process refund: ' . $e->getMessage());
        }
    }
}
