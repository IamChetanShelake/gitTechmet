<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\BookedHall;
use App\Models\HallEnquiry;
use App\Models\Accessorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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

        $transactions = $query->get();

        // Prepare display data with breakdown for multi-hall bookings
        $displayTransactions = collect();

        foreach ($transactions as $transaction) {
            $bookedHall = $transaction->bookedHall;

            if (!$bookedHall) {
                // Fallback for missing booked hall
                $displayTransactions->push([
                    'transaction' => $transaction,
                    'type' => 'main',
                    'hall_name' => 'N/A',
                    'item_service' => 'N/A',
                    'amount' => $transaction->amount,
                    'gst_amount' => 0.00,
                    'is_group_header' => false,
                    'is_group_member' => false,
                    'has_sub_rows' => false,
                    'sub_items' => []
                ]);
                continue;
            }

            // Check if this is a multi-hall booking
            if ($bookedHall->group_code) {
                // Multi-hall booking - get all halls in the group
                $groupHalls = BookedHall::where('group_code', $bookedHall->group_code)
                    ->with('enquiry')
                    ->get();

                $isFirstHall = true;
                $groupSubItems = [];
                $groupTotalAmount = 0;
                $groupGstEligibleAmount = 0; // Amount eligible for GST (rent + accessories only)

                foreach ($groupHalls as $hall) {
                    $hallBreakdown = $this->calculateDetailedHallBreakdown($hall);
                    $hallSubItems = [];

                    // Calculate total for this hall
                    $hallTotal = $hallBreakdown['hall_charges'] + $hallBreakdown['accessories_charges'] + $hallBreakdown['deposit_charges'];
                    $groupTotalAmount += $hallTotal;
                    $groupGstEligibleAmount += $hallBreakdown['hall_charges'] + $hallBreakdown['accessories_charges']; // Only rent + accessories for GST

                    // Add hall rent
                    if ($hallBreakdown['hall_charges'] > 0) {
                        $hallSubItems[] = [
                            'type' => 'hall_charge',
                            'hall_name' => $hall->hall_name,
                            'item_service' => 'Hall Rent',
                            'amount' => $hallBreakdown['hall_charges']
                        ];
                    }

                    // Add individual accessories
                    foreach ($hallBreakdown['accessories'] as $accessory) {
                        $hallSubItems[] = [
                            'type' => 'accessory',
                            'hall_name' => $hall->hall_name,
                            'item_service' => $accessory['name'],
                            'amount' => $accessory['calculated_price']
                        ];
                    }

                    // Add deposit
                    if ($hallBreakdown['deposit_charges'] > 0) {
                        $hallSubItems[] = [
                            'type' => 'deposit',
                            'hall_name' => $hall->hall_name,
                            'item_service' => 'Refundable Deposit',
                            'amount' => $hallBreakdown['deposit_charges']
                        ];
                    }

                    if ($isFirstHall) {
                        $gstAmount = $groupGstEligibleAmount * 0.18; // 18% GST only on rent and accessories
                        $displayTransactions->push([
                            'transaction' => $transaction,
                            'type' => 'main',
                            'hall_name' => 'Multiple Halls',
                            'item_service' => 'Booking Summary (' . $groupHalls->count() . ' halls)',
                            'amount' => $transaction->amount,
                            'gst_amount' => $gstAmount,
                            'is_group_header' => true,
                            'is_group_member' => false,
                            'has_sub_rows' => !empty($groupSubItems),
                            'sub_items' => $hallSubItems
                        ]);
                    }

                    $groupSubItems = array_merge($groupSubItems, $hallSubItems);
                    $isFirstHall = false;
                }

                // Add all sub-items for the group
                foreach ($groupSubItems as $subItem) {
                    $displayTransactions->push([
                        'transaction' => $transaction,
                        'type' => 'sub',
                        'hall_name' => $subItem['hall_name'],
                        'item_service' => $subItem['item_service'],
                        'amount' => $subItem['amount'],
                        'gst_amount' => 0.00,
                        'is_group_header' => false,
                        'is_group_member' => true,
                        'has_sub_rows' => false,
                        'sub_items' => []
                    ]);
                }

            } else {
                // Single hall booking
                $hallBreakdown = $this->calculateDetailedHallBreakdown($bookedHall);
                $subItems = [];

                // Add hall rent
                if ($hallBreakdown['hall_charges'] > 0) {
                    $subItems[] = [
                        'type' => 'hall_charge',
                        'hall_name' => $bookedHall->hall_name,
                        'item_service' => 'Hall Rent',
                        'amount' => $hallBreakdown['hall_charges']
                    ];
                }

                // Add individual accessories
                foreach ($hallBreakdown['accessories'] as $accessory) {
                    $subItems[] = [
                        'type' => 'accessory',
                        'hall_name' => $bookedHall->hall_name,
                        'item_service' => $accessory['name'],
                        'amount' => $accessory['calculated_price']
                    ];
                }

                // Add deposit
                if ($hallBreakdown['deposit_charges'] > 0) {
                    $subItems[] = [
                        'type' => 'deposit',
                        'hall_name' => $bookedHall->hall_name,
                        'item_service' => 'Refundable Deposit',
                        'amount' => $hallBreakdown['deposit_charges']
                    ];
                }

                // Calculate GST only on rent and accessories (exclude deposits)
                $gstEligibleAmount = $hallBreakdown['hall_charges'] + $hallBreakdown['accessories_charges'];
                $gstAmount = $gstEligibleAmount * 0.18; // 18% GST

                // Main transaction row
                $displayTransactions->push([
                    'transaction' => $transaction,
                    'type' => 'main',
                    'hall_name' => $bookedHall->hall_name,
                    'item_service' => 'Booking Summary',
                    'amount' => $transaction->amount,
                    'gst_amount' => $gstAmount,
                    'is_group_header' => false,
                    'is_group_member' => false,
                    'has_sub_rows' => !empty($subItems),
                    'sub_items' => $subItems
                ]);

                // Add sub-items
                foreach ($subItems as $subItem) {
                    $displayTransactions->push([
                        'transaction' => $transaction,
                        'type' => 'sub',
                        'hall_name' => $subItem['hall_name'],
                        'item_service' => $subItem['item_service'],
                        'amount' => $subItem['amount'],
                        'gst_amount' => 0.00,
                        'is_group_header' => false,
                        'is_group_member' => false,
                        'has_sub_rows' => false,
                        'sub_items' => []
                    ]);
                }
            }
        }

        // Get summary statistics
        $stats = $this->getPaymentStats();

        return view('admin.PaymentTransactions.index', compact('displayTransactions', 'stats'));
    }

    /**
     * Show the specified payment transaction
     */
    public function show($id)
    {
        $transaction = PaymentTransaction::with(['bookedHall.enquiry'])->findOrFail($id);

        $breakdown = [];
        if ($transaction->bookedHall) {
            // Check if this is a multi-hall booking
            if ($transaction->bookedHall->group_code) {
                // Multi-hall booking - get all halls in the group
                $groupHalls = BookedHall::where('group_code', $transaction->bookedHall->group_code)
                    ->with('enquiry')
                    ->get();

                foreach ($groupHalls as $hall) {
                    $breakdown[] = array_merge($this->calculateHallBreakdown($hall), [
                        'hall_name' => $hall->hall_name
                    ]);
                }
            } else {
                // Single hall booking
                $breakdown[] = array_merge($this->calculateHallBreakdown($transaction->bookedHall), [
                    'hall_name' => $transaction->bookedHall->hall_name
                ]);
            }
        }

        return view('admin.PaymentTransactions.show', compact('transaction', 'breakdown'));
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
     * Export payment transactions with detailed breakdown
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

        $filename = 'payment_transactions_detailed_' . now()->format('Y_m_d_H_i_s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // CSV headers - updated for detailed breakdown
            fputcsv($file, [
                'Transaction ID',
                'Gateway Ref',
                'Customer Name',
                'Customer Email',
                'Customer Mobile',
                'Hall/Item/Service',
                'Type',
                'Amount (Rs.)',
                'GST (18%)',
                'Total with GST (Rs.)',
                'Payment Type',
                'Status',
                'Payment Date',
                'Created At'
            ]);

            // CSV data
            foreach ($transactions as $transaction) {
                $bookedHall = $transaction->bookedHall;

                if (!$bookedHall) {
                    // Fallback for missing booked hall
                    fputcsv($file, [
                        $transaction->merchant_txn_no,
                        $transaction->payphi_txn_no ?? 'N/A',
                        'N/A',
                        $transaction->customer_email,
                        $transaction->customer_mobile,
                        'N/A',
                        'N/A',
                        '0.00',
                        '0.00',
                        '0.00',
                        ucfirst($transaction->transaction_type ?? 'N/A'),
                        $transaction->status,
                        $transaction->payment_date ? $transaction->payment_date->format('Y-m-d H:i:s') : 'N/A',
                        $transaction->created_at->format('Y-m-d H:i:s')
                    ]);
                    continue;
                }

                // Check if this is a multi-hall booking
                if ($bookedHall->group_code) {
                    // Multi-hall booking - get all halls in the group
                    $groupHalls = BookedHall::where('group_code', $bookedHall->group_code)
                        ->with('enquiry')
                        ->get();

                    $isFirstTransaction = true;
                    $groupTotal = 0;
                    $groupGST = 0;
                    $groupTotalWithGST = 0;

                    foreach ($groupHalls as $hall) {
                        $hallBreakdown = $this->calculateDetailedHallBreakdown($hall);

                        // For multi-hall, show transaction details only on first row
                        $txnId = $isFirstTransaction ? $transaction->merchant_txn_no : '';
                        $gatewayRef = $isFirstTransaction ? ($transaction->payphi_txn_no ?? 'N/A') : '';
                        $customerName = $isFirstTransaction ? $hall->customer_name : '';
                        $customerEmail = $isFirstTransaction ? $transaction->customer_email : '';
                        $customerMobile = $isFirstTransaction ? $transaction->customer_mobile : '';
                        $paymentType = $isFirstTransaction ? ucfirst($transaction->transaction_type ?? 'N/A') : '';
                        $status = $isFirstTransaction ? $transaction->status : '';
                        $paymentDate = $isFirstTransaction ? ($transaction->payment_date ? $transaction->payment_date->format('Y-m-d H:i:s') : 'N/A') : '';
                        $createdAt = $isFirstTransaction ? $transaction->created_at->format('Y-m-d H:i:s') : '';

                        // Hall charges row
                        if ($hallBreakdown['hall_charges'] > 0) {
                            $hallGST = $hallBreakdown['hall_charges'] * 0.18;
                            $hallTotalWithGST = $hallBreakdown['hall_charges'] + $hallGST;

                            fputcsv($file, [
                                $txnId,
                                $gatewayRef,
                                $customerName,
                                $customerEmail,
                                $customerMobile,
                                $hall->hall_name . ' - Hall Charges',
                                'Hall Charges',
                                number_format($hallBreakdown['hall_charges'], 2),
                                number_format($hallGST, 2),
                                number_format($hallTotalWithGST, 2),
                                $paymentType,
                                $status,
                                $paymentDate,
                                $createdAt
                            ]);

                            $txnId = $gatewayRef = $customerName = $customerEmail = $customerMobile = $paymentType = $status = $paymentDate = $createdAt = '';
                        }

                        // Individual accessory rows
                        foreach ($hallBreakdown['accessories'] as $accessory) {
                            $accessoryGST = $accessory['calculated_price'] * 0.18;
                            $accessoryTotalWithGST = $accessory['calculated_price'] + $accessoryGST;

                            fputcsv($file, [
                                $txnId,
                                $gatewayRef,
                                $customerName,
                                $customerEmail,
                                $customerMobile,
                                $hall->hall_name . ' - ' . $accessory['name'],
                                $accessory['name'],
                                number_format($accessory['calculated_price'], 2),
                                number_format($accessoryGST, 2),
                                number_format($accessoryTotalWithGST, 2),
                                $paymentType,
                                $status,
                                $paymentDate,
                                $createdAt
                            ]);

                            $txnId = $gatewayRef = $customerName = $customerEmail = $customerMobile = $paymentType = $status = $paymentDate = $createdAt = '';
                        }

                        // Deposit charges row
                        if ($hallBreakdown['deposit_charges'] > 0) {
                            $depositGST = 0.00; // No GST on deposits
                            $depositTotalWithGST = $hallBreakdown['deposit_charges']; // Total equals deposit amount

                            fputcsv($file, [
                                $txnId,
                                $gatewayRef,
                                $customerName,
                                $customerEmail,
                                $customerMobile,
                                $hall->hall_name . ' - Deposit Charges',
                                'Deposit',
                                number_format($hallBreakdown['deposit_charges'], 2),
                                number_format($depositGST, 2),
                                number_format($depositTotalWithGST, 2),
                                $paymentType,
                                $status,
                                $paymentDate,
                                $createdAt
                            ]);

                            $txnId = $gatewayRef = $customerName = $customerEmail = $customerMobile = $paymentType = $status = $paymentDate = $createdAt = '';
                        }

                        $groupTotal += $hallBreakdown['total'];
                        $groupGST += ($hallBreakdown['hall_charges'] + $hallBreakdown['accessories_charges']) * 0.18; // GST only on rent and accessories
                        $groupTotalWithGST += $hallBreakdown['total'] * 1.18;
                        $isFirstTransaction = false;
                    }

                    // Add total row for the group
                    fputcsv($file, [
                        '',
                        '',
                        '',
                        '',
                        '',
                        'GROUP TOTAL',
                        'Total',
                        number_format($groupTotal, 2),
                        number_format($groupGST, 2),
                        number_format($groupTotalWithGST, 2),
                        '',
                        '',
                        '',
                        ''
                    ]);

                } else {
                    // Single hall booking
                    $hallBreakdown = $this->calculateDetailedHallBreakdown($bookedHall);

                    $txnId = $transaction->merchant_txn_no;
                    $gatewayRef = $transaction->payphi_txn_no ?? 'N/A';
                    $customerName = $bookedHall->customer_name;
                    $customerEmail = $transaction->customer_email;
                    $customerMobile = $transaction->customer_mobile;
                    $paymentType = ucfirst($transaction->transaction_type ?? 'N/A');
                    $status = $transaction->status;
                    $paymentDate = $transaction->payment_date ? $transaction->payment_date->format('Y-m-d H:i:s') : 'N/A';
                    $createdAt = $transaction->created_at->format('Y-m-d H:i:s');

                    // Hall charges row
                    if ($hallBreakdown['hall_charges'] > 0) {
                        $hallGST = $hallBreakdown['hall_charges'] * 0.18;
                        $hallTotalWithGST = $hallBreakdown['hall_charges'] + $hallGST;

                        fputcsv($file, [
                            $txnId,
                            $gatewayRef,
                            $customerName,
                            $customerEmail,
                            $customerMobile,
                            $bookedHall->hall_name . ' - Hall Charges',
                            'Hall Charges',
                            number_format($hallBreakdown['hall_charges'], 2),
                            number_format($hallGST, 2),
                            number_format($hallTotalWithGST, 2),
                            $paymentType,
                            $status,
                            $paymentDate,
                            $createdAt
                        ]);

                        $txnId = $gatewayRef = $customerName = $customerEmail = $customerMobile = $paymentType = $status = $paymentDate = $createdAt = '';
                    }

                    // Individual accessory rows
                    foreach ($hallBreakdown['accessories'] as $accessory) {
                        $accessoryGST = $accessory['calculated_price'] * 0.18;
                        $accessoryTotalWithGST = $accessory['calculated_price'] + $accessoryGST;

                        fputcsv($file, [
                            $txnId,
                            $gatewayRef,
                            $customerName,
                            $customerEmail,
                            $customerMobile,
                            $bookedHall->hall_name . ' - ' . $accessory['name'],
                            $accessory['name'],
                            number_format($accessory['calculated_price'], 2),
                            number_format($accessoryGST, 2),
                            number_format($accessoryTotalWithGST, 2),
                            $paymentType,
                            $status,
                            $paymentDate,
                            $createdAt
                        ]);

                        $txnId = $gatewayRef = $customerName = $customerEmail = $customerMobile = $paymentType = $status = $paymentDate = $createdAt = '';
                    }

                    // Deposit charges row
                    if ($hallBreakdown['deposit_charges'] > 0) {
                        $depositGST = 0.00; // No GST on deposits
                        $depositTotalWithGST = $hallBreakdown['deposit_charges']; // Total equals deposit amount

                        fputcsv($file, [
                            $txnId,
                            $gatewayRef,
                            $customerName,
                            $customerEmail,
                            $customerMobile,
                            $bookedHall->hall_name . ' - Deposit Charges',
                            'Deposit',
                            number_format($hallBreakdown['deposit_charges'], 2),
                            number_format($depositGST, 2),
                            number_format($depositTotalWithGST, 2),
                            $paymentType,
                            $status,
                            $paymentDate,
                            $createdAt
                        ]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate breakdown of charges for a hall (for CSV export)
     */
    private function calculateHallBreakdown($bookedHall)
    {
        $hallCharges = $bookedHall->total_rent ?? 0;
        $depositCharges = $bookedHall->total_deposit ?? 0;
        $accessoriesCharges = 0;

        // Calculate accessories charges if enquiry exists
        if ($bookedHall->enquiry && $bookedHall->enquiry->accessorie) {
            $accessoryIds = json_decode($bookedHall->enquiry->accessorie, true);

            if (is_array($accessoryIds) && !empty($accessoryIds)) {
                // Calculate hours per day
                try {
                    $startTime = Carbon::parse($bookedHall->start_time);
                    $endTime = Carbon::parse($bookedHall->end_time);
                    $hoursPerDay = $startTime->diffInHours($endTime, false);
                } catch (\Exception $e) {
                    // Fallback to 1 hour if parsing fails
                    $hoursPerDay = 1;
                }

                // Get number of days
                $dates = $bookedHall->enquiry->event_dates ?
                    json_decode($bookedHall->enquiry->event_dates, true) :
                    [$bookedHall->enquiry->event_date];
                $dates = array_filter($dates);
                $numberOfDays = count($dates);

                // Get accessories with price > 0 only
                $accessoryRecords = Accessorie::whereIn('id', $accessoryIds)
                    ->whereNotNull('price')
                    ->where('price', '>', 0)
                    ->get();

                foreach ($accessoryRecords as $accessory) {
                    $price = (float) ($accessory->price ?? 0);
                    $hours = (float) ($accessory->hours ?? 1);

                    if ($price > 0 && $hours > 0) {
                        // Calculate blocks per day
                        $blocksPerDay = floor($hoursPerDay / $hours);
                        $pricePerDay = $price * max($blocksPerDay, 1);

                        // Multiply by number of days
                        $totalPrice = $pricePerDay * $numberOfDays;
                        $accessoriesCharges += $totalPrice;
                    }
                }
            }
        }

        $total = $hallCharges + $accessoriesCharges + $depositCharges;

        return [
            'hall_charges' => $hallCharges,
            'accessories_charges' => $accessoriesCharges,
            'deposit_charges' => $depositCharges,
            'total' => $total
        ];
    }

    /**
     * Calculate detailed breakdown of charges for a hall including individual accessories
     */
    private function calculateDetailedHallBreakdown($bookedHall)
    {
        $hallCharges = $bookedHall->total_rent ?? 0;
        $depositCharges = $bookedHall->total_deposit ?? 0;
        $accessoriesCharges = 0;
        $accessories = [];

        // Calculate accessories charges if enquiry exists
        if ($bookedHall->enquiry && $bookedHall->enquiry->accessorie) {
            $accessoryIds = json_decode($bookedHall->enquiry->accessorie, true);

            if (is_array($accessoryIds) && !empty($accessoryIds)) {
                // Calculate hours per day
                try {
                    $startTime = Carbon::parse($bookedHall->start_time);
                    $endTime = Carbon::parse($bookedHall->end_time);
                    $hoursPerDay = $startTime->diffInHours($endTime, false);
                } catch (\Exception $e) {
                    // Fallback to 1 hour if parsing fails
                    $hoursPerDay = 1;
                }

                // Get number of days
                $dates = $bookedHall->enquiry->event_dates ?
                    json_decode($bookedHall->enquiry->event_dates, true) :
                    [$bookedHall->enquiry->event_date];
                $dates = array_filter($dates);
                $numberOfDays = count($dates);

                // Get accessories with price > 0 only
                $accessoryRecords = Accessorie::whereIn('id', $accessoryIds)
                    ->whereNotNull('price')
                    ->where('price', '>', 0)
                    ->get();

                foreach ($accessoryRecords as $accessory) {
                    $price = (float) ($accessory->price ?? 0);
                    $hours = (float) ($accessory->hours ?? 1);

                    if ($price > 0 && $hours > 0) {
                        // Calculate blocks per day
                        $blocksPerDay = floor($hoursPerDay / $hours);
                        $pricePerDay = $price * max($blocksPerDay, 1);

                        // Multiply by number of days
                        $totalPrice = $pricePerDay * $numberOfDays;

                        $accessories[] = [
                            'name' => $accessory->name,
                            'calculated_price' => $totalPrice
                        ];

                        $accessoriesCharges += $totalPrice;
                    }
                }
            }
        }

        $total = $hallCharges + $accessoriesCharges + $depositCharges;

        return [
            'hall_charges' => $hallCharges,
            'accessories_charges' => $accessoriesCharges,
            'deposit_charges' => $depositCharges,
            'accessories' => $accessories,
            'total' => $total
        ];
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
