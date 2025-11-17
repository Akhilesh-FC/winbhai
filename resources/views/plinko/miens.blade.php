@extends('admin.body.adminmaster')

@section('admin')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="container mt-5">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-gamepad me-2"></i>Plinko Bets History</h4>
            <span class="badge bg-light text-dark">Total: {{ $bets->count() }}</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle text-center mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Game ID</th>
                            <th>Amount</th>
                            <th>Win Amount</th>
                            <th>Status</th>
                            <th>Date/Time</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bets as $bet)
                            <tr>
                                <td>{{ $bet->id }}</td>
                                <td>{{ $bet->userid }}</td>
                                <td>{{ $bet->game_id }}</td>
                                <td class="text-success fw-semibold">₹{{ number_format($bet->amount, 2) }}</td>
                                <td class="text-primary fw-semibold">₹{{ number_format($bet->win_amount, 2) }}</td>
                                <td>
                                    @if(strtolower($bet->status) === 'win')
                                        <span class="badge bg-success"><i class="fas fa-trophy me-1"></i>Win</span>
                                    @elseif(strtolower($bet->status) === 'lose')
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Lose</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($bet->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($bet->datetime)->format('d M Y, h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($bet->created_at)->diffForHumans() }}</td>
                                <td>{{ \Carbon\Carbon::parse($bet->updated_at)->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No Plinko Bets Found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
