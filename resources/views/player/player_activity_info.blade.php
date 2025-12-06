@extends('admin.body.adminmaster')
@section('admin')
<!-- DataTables CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<style>
    table td, table th {
        white-space: nowrap;
        vertical-align: middle;
        font-size: 14px;
        padding: 6px 10px;
    }
    .badge {
        font-size: 12px;
    }
</style>

<!-- 1. Lucky12 Bets -->
<div class="card mb-4">
    <div class="card-body">
        <h4 class="card-title">Wingo Player Activity</h4>
        <hr>
        <div class="table-responsive">
            <table id="lucky12_table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Period No</th>
                        <th>Game ID</th>
                        <th>Amount</th>
                        <th>Win Number</th>
                        <th>Win Amount</th>
                        <th>Ticket ID</th>
                        <th>Ticket Time</th>
                        <th>Status</th>
                        <th>Claim Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lucky12_bets as $bet)
                        <tr>
                            <td>{{ $bet->userid }}</td>
                            <td>{{ $bet->games_no }}</td>
                            <td>{{ $bet->game_id }}</td>
                            <td>{{ $bet->amount }}</td>
                            <td>{{ $bet->win_number }}</td>
                            <td>{{ $bet->win_amount }}</td>
                           
                            <td>
                                @if($bet->status == 1)
                                    <span class="badge bg-success">Win</span>
                                @elseif($bet->status == 2)
                                    <span class="badge bg-danger">Loss</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                           
                            <td>{{ $bet->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 2. LuckyCard Bets -->
<div class="card mb-4">
    <div class="card-body">
        <h4 class="card-title">Aviator Player Activity</h4>
        <hr>
        <div class="table-responsive">
            <table id="luckycard_table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Period No</th>
                        <th>Game ID</th>
                        <th>Amount</th>
                        <th>Win Number</th>
                        <th>Win Amount</th>
                        <th>Ticket ID</th>
                        <th>Ticket Time</th>
                        <th>Status</th>
                        <th>Claim Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($luckycard_bets as $bet)
                        <tr>
                            <td>{{ $bet->uid }}</td>
                            <td>{{ $bet->game_sr_num }}</td>
                            <td>{{ $bet->game_id }}</td>
                            <td>{{ $bet->amount }}</td>
                            <td>{{ $bet->number }}</td>
                            <td>{{ $bet->win }}</td>
                           
                            <td>
                                @if($bet->status == 1)
                                    <span class="badge bg-success">Win</span>
                                @elseif($bet->status == 2)
                                    <span class="badge bg-danger">Loss</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                           
                            <td>{{ $bet->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 3. Triplechance Bets -->
<div class="card mb-4">
    <div class="card-body">
        <h4 class="card-title">Chicken Player Activity</h4>
        <hr>
        <div class="table-responsive">
            <table id="triplechance_table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Period No</th>
                        <th>Game ID</th>
                        <th>Amount</th>
                        <th>Win Number</th>
                        <th>Win Amount</th>
                        <th>Ticket ID</th>
                        <th>Ticket Time</th>
                        <th>Status</th>
                        <th>Claim Status</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($triplechance_bets as $bet)
                        <tr>
                            <td>{{ $bet->user_id }}</td>
                            
                            <td>{{ $bet->game_id }}</td>
                            <td>{{ $bet->amount }}</td>
                            <td>{{ $bet->win_number }}</td>
                            <td>{{ $bet->win_amount }}</td>
                           
                            <td>
                                @if($bet->status == 1)
                                    <span class="badge bg-success">Win</span>
                                @elseif($bet->status == 2)
                                    <span class="badge bg-danger">Loss</span>
                                @else
                                    <span class="badge bg-warning">Pending</span>
                                @endif
                            </td>
                           
                            <td>{{ $bet->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-center">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 4. Wallet History -->
<div class="card">
    <div class="card-body">
        <h4 class="card-title">Wallet History</h4>
        <hr>
        <div class="table-responsive">
            <table id="wallet_table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Action</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallet_history as $history)
                        <tr>
                            <td>{{ $history->id }}</td>
                            <td>{{ $history->userid }}</td>
                            <td>{{ $history->action ?? 'N/A' }}</td>
                            <td>{{ $history->amount }}</td>
                            <td>{{ $history->description }}</td>
                            <td>
                                @if($history->type == 1)
                                    <span class="badge bg-success">Credit</span>
                                @elseif($history->type == 2)
                                    <span class="badge bg-danger">Debit</span>
                                @else
                                    <span class="badge bg-secondary">Unknown</span>
                                @endif
                            </td>
                            <td>{{ $history->created_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No wallet history found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTable Initialization -->
<script>
    $(document).ready(function() {
        $('#lucky12_table').DataTable();
        $('#luckycard_table').DataTable();
        $('#triplechance_table').DataTable();
        $('#wallet_table').DataTable();
    });
</script>

@endsection
