@extends('admin.layout.masteradmin')

@section('content')
<div class="container-fluid py-2">
    <div class="row">
        <div class="col-12">
            <div class="card my-4">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                    <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                        <h6 class="text-white text-capitalize ps-3">Payment Analytics</h6>
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

                    <!-- Monthly Trends -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Monthly Payment Trends (Last 12 Months)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Month</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Transactions</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Amount</th>
                                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Average</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($monthlyData as $data)
                                                <tr>
                                                    <td>
                                                        <span class="text-secondary text-xs font-weight-bold">
                                                            {{ DateTime::createFromFormat('!m', $data->month)->format('F') }} {{ $data->year }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-secondary text-xs font-weight-bold">{{ $data->count }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-secondary text-xs font-weight-bold">₹{{ number_format($data->total_amount, 2) }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-secondary text-xs font-weight-bold">₹{{ number_format($data->total_amount / $data->count, 2) }}</span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">No data available</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Type Distribution -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Payment Type Distribution</h6>
                                </div>
                                <div class="card-body">
                                    @forelse($paymentTypeData as $data)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="mb-0">{{ ucfirst($data->transaction_type ?? 'Unknown') }}</h6>
                                            <p class="text-sm text-secondary mb-0">{{ $data->count }} transactions</p>
                                        </div>
                                        <div class="text-end">
                                            <h6 class="mb-0">₹{{ number_format($data->total_amount, 2) }}</h6>
                                            <p class="text-sm text-secondary mb-0">Avg: ₹{{ number_format($data->total_amount / $data->count, 2) }}</p>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-center text-secondary">No payment type data available</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Status Distribution -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Transaction Status Distribution</h6>
                                </div>
                                <div class="card-body">
                                    @forelse($statusData as $data)
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            @php
                                                $statusColors = [
                                                    'SUCCESS' => 'success',
                                                    'FAILED' => 'danger',
                                                    'initiated' => 'warning',
                                                    'PENDING' => 'info'
                                                ];
                                                $color = $statusColors[$data->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-gradient-{{ $color }}">{{ $data->status }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $data->count }}</h6>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-center text-secondary">No status data available</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section (Optional - can be enhanced with Chart.js) -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header pb-0">
                                    <h6>Payment Insights</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="font-weight-bolder">
                                                    @if($paymentTypeData->sum('count') > 0)
                                                        {{ number_format(($paymentTypeData->where('transaction_type', 'full')->first()->count ?? 0) / $paymentTypeData->sum('count') * 100, 1) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </h4>
                                                <p class="mb-0">Full Payments</p>
                                                <p class="text-sm text-secondary">Customers prefer complete payment</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="font-weight-bolder">
                                                    @if($statusData->sum('count') > 0)
                                                        {{ number_format(($statusData->where('status', 'SUCCESS')->first()->count ?? 0) / $statusData->sum('count') * 100, 1) }}%
                                                    @else
                                                        0%
                                                    @endif
                                                </h4>
                                                <p class="mb-0">Success Rate</p>
                                                <p class="text-sm text-secondary">Payment gateway performance</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <h4 class="font-weight-bolder">
                                                    ₹{{ number_format($paymentTypeData->sum('total_amount') / max($paymentTypeData->sum('count'), 1), 2) }}
                                                </h4>
                                                <p class="mb-0">Average Transaction</p>
                                                <p class="text-sm text-secondary">Mean payment amount</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection