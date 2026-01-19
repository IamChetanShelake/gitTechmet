@extends('admin.layout.masteradmin')

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Payment Transactions</h6>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">

                    <!-- Statistics Cards -->
                    <div class="row mb-4 px-3">
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-header p-2 ps-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-sm mb-0 text-capitalize">Total Transactions</p>
                                            <h4 class="mb-0">{{ $stats['total_transactions'] }}</h4>
                                        </div>
                                        <div class="icon icon-md icon-shape bg-gradient-dark shadow-dark text-center border-radius-lg">
                                            <i class="material-symbols-rounded opacity-10">receipt</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-header p-2 ps-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-sm mb-0 text-capitalize">Successful</p>
                                            <h4 class="mb-0 text-success">{{ $stats['successful_transactions'] }}</h4>
                                        </div>
                                        <div class="icon icon-md icon-shape bg-gradient-success shadow-success text-center border-radius-lg">
                                            <i class="material-symbols-rounded opacity-10">check_circle</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                            <div class="card">
                                <div class="card-header p-2 ps-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-sm mb-0 text-capitalize">Failed</p>
                                            <h4 class="mb-0 text-danger">{{ $stats['failed_transactions'] }}</h4>
                                        </div>
                                        <div class="icon icon-md icon-shape bg-gradient-danger shadow-danger text-center border-radius-lg">
                                            <i class="material-symbols-rounded opacity-10">cancel</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-sm-6">
                            <div class="card">
                                <div class="card-header p-2 ps-3">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <p class="text-sm mb-0 text-capitalize">Total Amount</p>
                                            <h4 class="mb-0 text-primary">₹{{ number_format($stats['total_amount'], 2) }}</h4>
                                        </div>
                                        <div class="icon icon-md icon-shape bg-gradient-primary shadow-primary text-center border-radius-lg">
                                            <i class="material-symbols-rounded opacity-10">payments</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Type Statistics -->
                    <div class="row mb-4 px-3">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Deposit Payments</h6>
                                    <h4 class="font-weight-bolder">{{ $stats['deposit_payments'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Rent Payments</h6>
                                    <h4 class="font-weight-bolder">{{ $stats['rent_payments'] }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase text-body text-xs font-weight-bolder">Full Payments</h6>
                                    <h4 class="font-weight-bolder">{{ $stats['full_payments'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-3 px-3">
                        <div class="col-12">
                            <form method="GET" action="{{ route('admin.payment-transactions.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="SUCCESS" {{ request('status') == 'SUCCESS' ? 'selected' : '' }}>Success</option>
                                        <option value="FAILED" {{ request('status') == 'FAILED' ? 'selected' : '' }}>Failed</option>
                                        <option value="initiated" {{ request('status') == 'initiated' ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="payment_type" class="form-select">
                                        <option value="">All Types</option>
                                        <option value="deposit" {{ request('payment_type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                                        <option value="rent" {{ request('payment_type') == 'rent' ? 'selected' : '' }}>Rent</option>
                                        <option value="full" {{ request('payment_type') == 'full' ? 'selected' : '' }}>Full Payment</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From Date">
                                </div>
                                <div class="col-md-2">
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To Date">
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search transactions...">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mb-3 px-3">
                        <div class="col-12">
                            <a href="{{ route('admin.payment-transactions.export', request()->query()) }}" class="btn btn-success btn-sm">
                                <i class="material-symbols-rounded">download</i> Export CSV
                            </a>
                            <a href="{{ route('admin.payment-transactions.analytics') }}" class="btn btn-info btn-sm">
                                <i class="material-symbols-rounded">analytics</i> Analytics
                            </a>
                        </div>
                    </div>

                    <!-- Transactions Table -->
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" style="border-collapse: separate; border-spacing: 0;">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Transaction</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Customer</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hall</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item/Service</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">GST (18%)</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-secondary opacity-7">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentTransactionId = null; $transactionIndex = 0; @endphp
                                @forelse($displayTransactions as $index => $displayItem)
                                @php
                                    $transaction = $displayItem['transaction'];
                                    $isMainRow = $displayItem['type'] === 'main';
                                    $isSubRow = $displayItem['type'] === 'sub';

                                    // Check if this is a new transaction group
                                    $isNewTransaction = $currentTransactionId !== $transaction->id;
                                    if ($isNewTransaction) {
                                        $currentTransactionId = $transaction->id;
                                        $transactionIndex++;
                                    }

                                    // Alternate background colors for different transactions
                                    $bgClass = $transactionIndex % 2 === 0 ? 'bg-light' : '';
                                @endphp
                                <tr class="{{ $bgClass }} @if($isSubRow) table-light @endif"
                                    style="@if($isNewTransaction) border-top: 2px solid #dee2e6; @endif
                                           @if($displayItem['is_group_member']) border-left: 3px solid #007bff; @endif
                                           @if($isMainRow && !$displayItem['has_sub_rows']) border-bottom: 2px solid #dee2e6; @endif">
                                    <td style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow)
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $transaction->merchant_txn_no }}</h6>
                                                    @if($transaction->payphi_txn_no)
                                                        <p class="text-xs text-secondary mb-0">Gateway: {{ $transaction->payphi_txn_no }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow)
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $transaction->bookedHall->customer_name ?? 'N/A' }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $transaction->customer_email }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $transaction->customer_mobile }}</p>
                                            </div>
                                        @endif
                                    </td>
                                    <td style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        <div class="d-flex flex-column justify-content-center">
                                            @if($isMainRow)
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $displayItem['hall_name'] ?? 'N/A' }}</h6>
                                            @else
                                                <span class="text-sm text-muted">
                                                    <i class="material-symbols-rounded text-xs me-1">location_on</i>
                                                    {{ $displayItem['hall_name'] ?? 'N/A' }}
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        <div class="d-flex flex-column justify-content-center">
                                            @if($isMainRow)
                                                <h6 class="mb-0 text-sm">{{ $displayItem['item_service'] ?? 'N/A' }}</h6>
                                            @else
                                                @if(isset($displayItem['type']) && $displayItem['type'] === 'accessory')
                                                    <span class="text-sm text-info font-weight-bold">
                                                        <i class="material-symbols-rounded text-xs me-1">settings</i>
                                                        {{ $displayItem['item_service'] ?? 'N/A' }}
                                                    </span>
                                                @elseif(isset($displayItem['type']) && $displayItem['type'] === 'hall_charge')
                                                    <span class="text-sm text-success font-weight-bold">
                                                        <i class="material-symbols-rounded text-xs me-1">meeting_room</i>
                                                        {{ $displayItem['item_service'] ?? 'N/A' }}
                                                    </span>
                                                @elseif(isset($displayItem['type']) && $displayItem['type'] === 'deposit')
                                                    <span class="text-sm text-warning font-weight-bold">
                                                        <i class="material-symbols-rounded text-xs me-1">account_balance_wallet</i>
                                                        {{ $displayItem['item_service'] ?? 'N/A' }}
                                                    </span>
                                                @else
                                                    <span class="text-sm">{{ $displayItem['item_service'] ?? 'N/A' }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                    <td class="align-middle text-center" style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow)
                                            <span class="text-secondary text-xs font-weight-bold text-primary">
                                                ₹{{ number_format($displayItem['amount'], 2) }}
                                            </span>
                                        @else
                                            @if(isset($displayItem['type']) && $displayItem['type'] === 'accessory')
                                                <span class="text-info text-xs font-weight-bold">
                                                    ₹{{ number_format($displayItem['amount'], 2) }}
                                                </span>
                                            @elseif(isset($displayItem['type']) && $displayItem['type'] === 'hall_charge')
                                                <span class="text-success text-xs font-weight-bold">
                                                    ₹{{ number_format($displayItem['amount'], 2) }}
                                                </span>
                                            @elseif(isset($displayItem['type']) && $displayItem['type'] === 'deposit')
                                                <span class="text-warning text-xs font-weight-bold">
                                                    ₹{{ number_format($displayItem['amount'], 2) }}
                                                </span>
                                            @else
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    ₹{{ number_format($displayItem['amount'], 2) }}
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="align-middle text-center" style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow && isset($displayItem['gst_amount']))
                                            <span class="text-secondary text-xs font-weight-bold text-success">
                                                ₹{{ number_format($displayItem['gst_amount'], 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center" style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow)
                                            @php
                                                $typeColors = [
                                                    'deposit' => 'info',
                                                    'rent' => 'warning',
                                                    'full' => 'success',
                                                    'REFUND' => 'danger'
                                                ];
                                                $color = $typeColors[$transaction->transaction_type] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-sm bg-gradient-{{ $color }}">{{ ucfirst($transaction->transaction_type ?? 'N/A') }}</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center text-sm" style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow)
                                            @php
                                                $statusColors = [
                                                    'SUCCESS' => 'success',
                                                    'FAILED' => 'danger',
                                                    'initiated' => 'warning',
                                                    'PENDING' => 'info'
                                                ];
                                                $color = $statusColors[$transaction->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-sm bg-gradient-{{ $color }}">{{ $transaction->status }}</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center" style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow)
                                            <span class="text-secondary text-xs font-weight-bold">
                                                {{ $transaction->payment_date ? $transaction->payment_date->format('d M Y, H:i') : $transaction->created_at->format('d M Y, H:i') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="align-middle" style="@if($isMainRow && $displayItem['has_sub_rows']) border-bottom: 1px solid #dee2e6; @endif">
                                        @if($isMainRow)
                                            <a href="{{ route('admin.payment-transactions.show', $transaction->id) }}" class="btn btn-link text-dark px-3 mb-0">
                                                <i class="material-symbols-rounded text-sm me-2">visibility</i>View
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <p class="text-secondary mb-0">No payment transactions found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
