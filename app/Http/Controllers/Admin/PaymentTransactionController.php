<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\BookedHall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            // Create refund transaction record
            $refundRef = 'REF' . now()->format('YmdHis') . rand(100, 999);

            PaymentTransaction::create([
                'booked_hall_id' => $transaction->booked_hall_id,
                'merchant_txn_no' => $refundRef,
                'amount' => $request->refund_amount,
                'customer_email' => $transaction->customer_email,
                'customer_mobile' => $transaction->customer_mobile,
                'transaction_type' => 'REFUND',
                'status' => 'initiated',
                'full_response' => [
                    'refund_reason' => $request->refund_reason,
                    'original_transaction' => $transaction->merchant_txn_no,
                    'refunded_by' => auth()->user()->name ?? 'Admin'
                ]
            ]);

            return back()->with('success', 'Refund initiated successfully. Reference: ' . $refundRef);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to initiate refund: ' . $e->getMessage());
        }
    }
}
