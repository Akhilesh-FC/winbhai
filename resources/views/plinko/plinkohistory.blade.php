@extends('admin.body.adminmaster')

@section('admin')
<div class="container mt-5">
    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">🎯 Plinko Bets History</h4>
            <span class="badge bg-light text-dark">Total Bets: {{ $bets->count() }}</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0 text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Amount</th>
                            <th>Game ID</th>
                            <th>Type</th>
                            <th>Indexes</th>
                            <th>Multiplier</th>
                            <th>Win Amount</th>
                            <th>Status</th>
                            <th>Tax</th>
                            <th>After Tax</th>
                            <th>Order ID</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bets as $bet)
                            <tr>
                                <td>{{ $bet->id }}</td>
                                <td>{{ $bet->userid }}</td>
                                <td class="text-success fw-bold">₹{{ number_format($bet->amount, 2) }}</td>
                                <td>{{ $bet->game_id }}</td>
                                <td>
                                    @switch($bet->type)
                                        @case(1)
                                            <span class="badge bg-success">Green</span>
                                            @break
                                        @case(2)
                                            <span class="badge bg-warning text-dark">Yellow</span>
                                            @break
                                        @case(3)
                                            <span class="badge bg-danger">Red</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">Unknown</span>
                                    @endswitch
                                </td>
                                <td>{{ $bet->indexs }}</td>
                                <td><span class="badge bg-info text-dark">{{ $bet->multipler }}x</span></td>
                                <td class="text-primary fw-semibold">₹{{ number_format($bet->win_amount, 2) }}</td>
                                <td>
                                    @if($bet->status == 'win')
                                        <span class="badge bg-success">Win</span>
                                    @elseif($bet->status == 'lose')
                                        <span class="badge bg-danger">Lose</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($bet->status) }}</span>
                                    @endif
                                </td>
                                <td>₹{{ number_format($bet->tax, 2) }}</td>
                                <td class="text-info">₹{{ number_format($bet->after_tax, 2) }}</td>
                                <td>{{ $bet->orderid }}</td>
                                <td>{{ \Carbon\Carbon::parse($bet->created_at)->format('d M Y, h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($bet->updated_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center text-muted py-4">No Plinko Bets Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
