@extends('admin.layout.masteradmin')

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Payment Transaction Details</h6>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- Back Button -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <a href="{{ route('admin.payment-transactions.index') }}" class="btn btn-outline-secondary">
                                <i class="material-symbols-rounded">arrow_back</i> Back to Transactions
                            </a>
                        </div>
                    </div>

                    <!-- Transaction Overview -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Transaction Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Transaction ID:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->merchant_txn_no }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Gateway Reference:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->payphi_txn_no ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Amount:</strong></p>
                                            <p class="text-sm text-primary font-weight-bold">₹{{ number_format($transaction->amount, 2) }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Payment Type:</strong></p>
                                            @php
                                                $typeColors = [
                                                    'deposit' => 'info',
                                                    'rent' => 'warning', 
                                                    'full' => 'success',
                                                    'REFUND' => 'danger'
                                                ];
                                                $color = $typeColors[$transaction->transaction_type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-gradient-{{ $color }}">{{ ucfirst($transaction->transaction_type ?? 'N/A') }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Status:</strong></p>
                                            @php
                                                $statusColors = [
                                                    'SUCCESS' => 'success',
                                                    'FAILED' => 'danger',
                                                    'initiated' => 'warning',
                                                    'PENDING' => 'info'
                                                ];
                                                $color = $statusColors[$transaction->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-gradient-{{ $color }}">{{ $transaction->status }}</span>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Response Code:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->response_code ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Payment Date:</strong></p>
                                            <p class="text-sm text-secondary">
                                                {{ $transaction->payment_date ? $transaction->payment_date->format('d M Y, H:i:s') : 'N/A' }}
                                            </p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-sm mb-1"><strong>Created At:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->created_at->format('d M Y, H:i:s') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Customer Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-sm mb-1"><strong>Name:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->bookedHall->customer_name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-sm mb-1"><strong>Email:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->customer_email }}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-sm mb-1"><strong>Mobile:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->customer_mobile }}</p>
                                        </div>
                                        @if($transaction->bookedHall)
                                        <div class="col-12">
                                            <p class="text-sm mb-1"><strong>Hall:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->bookedHall->hall_name }}</p>
                                        </div>
                                        <div class="col-12">
                                            <p class="text-sm mb-1"><strong>Event Date:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->bookedHall->event_date ?? 'N/A' }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    @if($transaction->bookedHall && $transaction->bookedHall->enquiry)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Booking Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p class="text-sm mb-1"><strong>Organization:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->bookedHall->enquiry->organization ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="text-sm mb-1"><strong>Event Type:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->bookedHall->enquiry->event_type ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="text-sm mb-1"><strong>Duration:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->bookedHall->enquiry->duration ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="text-sm mb-1"><strong>Expected Audience:</strong></p>
                                            <p class="text-sm text-secondary">{{ $transaction->bookedHall->enquiry->expected_audience ?? 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-sm mb-1"><strong>Total Rent:</strong></p>
                                            <p class="text-sm text-secondary">₹{{ number_format($transaction->bookedHall->total_rent ?? 0, 2) }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-sm mb-1"><strong>Total Deposit:</strong></p>
                                            <p class="text-sm text-secondary">₹{{ number_format($transaction->bookedHall->total_deposit ?? 0, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Gateway Response -->
                    @if($transaction->full_response)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Gateway Response</h6>
                                </div>
                                <div class="card-body">
                                    <pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ json_encode($transaction->full_response, JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    @if($transaction->isSuccessful() && $transaction->transaction_type !== 'REFUND')
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Actions</h6>
                                </div>
                                <div class="card-body">
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#refundModal">
                                        <i class="material-symbols-rounded">undo</i> Initiate Refund
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Refund Modal -->
                    <div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.payment-transactions.refund', $transaction->id) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="refundModalLabel">Initiate Refund</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="refund_amount" class="form-label">Refund Amount</label>
                                            <input type="number" class="form-control" id="refund_amount" name="refund_amount" 
                                                   step="0.01" max="{{ $transaction->amount }}" required>
                                            <div class="form-text">Maximum refund amount: ₹{{ number_format($transaction->amount, 2) }}</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="refund_reason" class="form-label">Refund Reason</label>
                                            <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-warning">Initiate Refund</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection