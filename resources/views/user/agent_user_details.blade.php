@extends('admin.body.adminmaster')

@section('admin')

<!DOCTYPE html>
<html>
<head>
    <title>Agent User Details</title>

    <!-- DataTable CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <style>
        body { background: #f7fafd; }
        .wrapper { display: flex; width: 100%; }
        .content-wrapper-fixed { width: 100%; padding: 20px; overflow-x: hidden; }

        h2, h3 { font-weight: 600; color: #003f6b; }

        .card {
            background: #ffffff;
            padding: 18px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,.08);
            margin-bottom: 25px;
        }

        table.dataTable thead th {
            background: #003f6b;
            color: #fff;
            font-weight: 600;
            text-align: center;
        }

        table.dataTable tbody tr:hover { background: #e7f3ff !important; }
        .dataTables_wrapper { overflow-x: auto; }

        /* ⭐ Summary Box Grid */
        .summary-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 25px;
        }

        .summary-box {
            background: linear-gradient(135deg, #004d79, #007bbf);
            color: #fff;
            padding: 18px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 12px rgba(0,0,0,0.12);
            transition: 0.3s;
        }

        .summary-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .summary-box h4 {
            font-size: 17px;
            margin: 0;
            font-weight: 500;
            opacity: 0.9;
        }

        .summary-box h2 {
            font-size: 28px;
            margin-top: 6px;
            font-weight: 700;
        }

        /* heading design */
        .card h3 {
            margin-bottom: 15px;
            border-left: 5px solid #007bbf;
            padding-left: 10px;
        }
    </style>
</head>

<body>

<div class="wrapper">
<div class="content-wrapper-fixed">

<h2>Agent User Dashboard</h2>

<form method="GET" style="margin-bottom:20px;">
    <div style="display:flex; gap:10px; align-items:center;">
        
        <div>
            <label>From Date</label>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
        </div>

        <div>
            <label>To Date</label>
            <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
        </div>

        <div style="margin-top:22px;">
            <button class="btn btn-primary">Filter</button>
        </div>

        <div style="margin-top:22px;">
            <a href="{{ url()->current() }}" class="btn btn-danger">Reset</a>
        </div>

    </div>
</form>


<!-- ⭐ New Stylish Summary Section -->
<div class="summary-container">

    <div class="summary-box">
        <h4>Total Users</h4>
        <h2>{{ $summary['total_users'] }}</h2>
    </div>
    
      <div class="summary-box">
        <h4>Today Users</h4>
        <h2>{{ $summary['today_users'] }}</h2>
    </div>

    <div class="summary-box">
        <h4>Total Deposit</h4>
        <h2>₹ {{ $summary['total_deposit'] }}</h2>
    </div>

     <div class="summary-box">
        <h4>Today Deposit</h4>
        <h2>₹ {{ $summary['today_deposit'] }}</h2>
    </div>

    <div class="summary-box">
        <h4>Total Withdraw</h4>
        <h2>₹ {{ $summary['total_withdraw'] }}</h2>
    </div>
    
    <div class="summary-box">
        <h4>Today Withdraw</h4>
        <h2>₹ {{ $summary['today_withdraw'] }}</h2>
    </div>

    <div class="summary-box">
        <h4>Total Wingo Bet</h4>
        <h2>₹ {{ $summary['total_wingo_bet'] }}</h2>
    </div>

    <div class="summary-box">
        <h4>Total Chicken road Bet</h4>
        <h2>₹ {{ $summary['total_dragon_bet'] }}</h2>
    </div>

    <div class="summary-box">
        <h4>Total Aviator Bet</h4>
        <h2>₹ {{ $summary['total_aviator_bet'] }}</h2>
    </div>

</div>


{{-- USERS --}}
<div class="card">
    <h3>USERS LIST</h3>
    <table id="usersTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Mobile</th><th>Join Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $u)
            <tr>
                <td>{{ $u->id }}</td>
                <td>{{ $u->username }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->mobile }}</td>
                <td>{{ $u->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


{{-- DEPOSITS --}}
<div class="card">
    <h3>DEPOSIT HISTORY</h3>
    <table id="depositsTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th><th>User ID</th><th>Amount</th><th>Status</th><th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deposits as $d)
            <tr>
                <td>{{ $d->id }}</td>
                <td>{{ $d->user_id }}</td>
                <td>₹ {{ $d->cash }}</td>
                <td>
                    @if($d->status == 1)
                        <span style="background:#ffcc00;color:#000;padding:4px 10px;border-radius:4px;">Pending</span>
                    @elseif($d->status == 2)
                        <span style="background:#28a745;color:#fff;padding:4px 10px;border-radius:4px;">Success</span>
                    @elseif($d->status == 3)
                        <span style="background:#dc3545;color:#fff;padding:4px 10px;border-radius:4px;">Rejected</span>
                    @endif
                </td>
                <td>{{ $d->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


{{-- WITHDRAW --}}
<div class="card">
    <h3>WITHDRAW HISTORY</h3>
    <table id="withdrawsTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th><th>User ID</th><th>Amount</th><th>Status</th><th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($withdraws as $w)
            <tr>
                <td>{{ $w->id }}</td>
                <td>{{ $w->user_id }}</td>
                <td>₹ {{ $w->amount }}</td>
                <td>
                    @if($w->status == 1)
                        <span style="background:#ffcc00;color:#000;padding:4px 10px;border-radius:4px;">Pending</span>
                    @elseif($w->status == 2)
                        <span style="background:#28a745;color:#fff;padding:4px 10px;border-radius:4px;">Success</span>
                    @elseif($w->status == 3)
                        <span style="background:#dc3545;color:#fff;padding:4px 10px;border-radius:4px;">Rejected</span>
                    @endif
                </td>
                <td>{{ $w->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


{{-- WINGO --}}
<div class="card">
    <h3>WINGO GAME BET HISTORY</h3>
    <table id="wingoTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th><th>User ID</th><th>Game ID</th><th>Amount</th><th>Status</th><th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wingo_history as $g)
            <tr>
                <td>{{ $g->id }}</td>
                <td>{{ $g->userid }}</td>
                <td>{{ $g->game_id }}</td>
                <td>₹ {{ $g->amount }}</td>
                <td>
                    @if($g->status == 0)
                        <span style="background:#ffcc00;color:#000;padding:4px 10px;border-radius:4px;">Pending</span>
                    @elseif($g->status == 1)
                        <span style="background:#28a745;color:#fff;padding:4px 10px;border-radius:4px;">Win</span>
                    @elseif($g->status == 2)
                        <span style="background:#dc3545;color:#fff;padding:4px 10px;border-radius:4px;">Loss</span>
                    @endif
                </td>
                <td>{{ $g->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


{{-- DRAGON & TIGER --}}
<div class="card">
    <h3>Chicken Road BET HISTORY</h3>
    <table id="dragonTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th><th>User ID</th><th>Game ID</th><th>Amount</th><th>Status</th><th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dragon_history as $g)
            <tr>
                <td>{{ $g->id }}</td>
                <td>{{ $g->userid }}</td>
                <td>{{ $g->game_id }}</td>
                <td>₹ {{ $g->amount }}</td>
                <td>
                    @if($g->status == 0)
                        <span style="background:#ffcc00;color:#000;padding:4px 10px;border-radius:4px;">Pending</span>
                    @elseif($g->status == 1)
                        <span style="background:#28a745;color:#fff;padding:4px 10px;border-radius:4px;">Win</span>
                    @elseif($g->status == 2)
                        <span style="background:#dc3545;color:#fff;padding:4px 10px;border-radius:4px;">Loss</span>
                    @endif
                </td>
                <td>{{ $g->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


{{-- AVIATOR --}}
<div class="card">
    <h3>AVIATOR BET HISTORY</h3>
    <table id="aviatorTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>ID</th><th>User ID</th><th>Amount</th><th>Win/Trade</th><th>Status</th><th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($aviator_history as $g)
            <tr>
                <td>{{ $g->id }}</td>
                <td>{{ $g->uid }}</td>
                <td>₹ {{ $g->amount }}</td>
                <td>{{ $g->win }}</td>
                <td>
                    @if($g->status == 0)
                        <span style="background:#ffcc00;color:#000;padding:4px 10px;border-radius:4px;">Pending</span>
                    @elseif($g->status == 1)
                        <span style="background:#28a745;color:#fff;padding:4px 10px;border-radius:4px;">Win</span>
                    @elseif($g->status == 2)
                        <span style="background:#dc3545;color:#fff;padding:4px 10px;border-radius:4px;">Loss</span>
                    @endif
                </td>
                <td>{{ $g->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>


</div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $('#usersTable, #depositsTable, #withdrawsTable, #wingoTable, #dragonTable, #aviatorTable').DataTable({
        pageLength: 10,
        responsive: true
    });
</script>

</body>
</html>

@endsection
