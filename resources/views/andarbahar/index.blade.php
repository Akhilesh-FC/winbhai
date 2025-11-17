@extends('admin.body.adminmaster')

@section('admin')

<!-- ✅ SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .game-container {
        max-width: 1100px;
        margin: auto;
        padding: 30px;
        background: #f4f6f9;
        border-radius: 15px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
    }

    .clickable-card {
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-radius: 15px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 1.2rem;
        box-shadow: 6px 6px 12px #d1d9e6, -6px -6px 12px #ffffff;
    }

    .clickable-card:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 25px rgba(0, 0, 0, 0.2);
    }

    .card-bg-andar {
        background: linear-gradient(135deg, #ff416c, #ff4b2b);
    }

    .card-bg-bahar {
        background: linear-gradient(135deg, #1d976c, #93f9b9);
    }

    .game-header {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 30px;
    }

    .amount-box {
        background: #ffffff;
        margin-top: 10px;
        border-radius: 10px;
        font-weight: bold;
        color: #333;
        padding: 10px;
        font-size: 0.95rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        text-align: center;
    }

    @media (max-width: 767px) {
        .clickable-card {
            height: 80px;
            font-size: 1rem;
        }
        .amount-box {
            font-size: 0.85rem;
        }
    }
</style>

<div class="container game-container mt-5">
    <input type="hidden" id="game_id" value="{{ $gameid }}">
    <input type="hidden" id="games_no" value="{{ $bets[0]->games_no ?? '' }}">

    {{-- 🔹 Profit Summary Boxes --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5>Total Admin Profit</h5>
                    <h3 data-admin-profit>₹{{ $total_admin_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5>Total User Profit</h5>
                    <h3 data-user-profit>₹{{ $total_user_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow">
                <div class="card-body">
                    <h5>Today Admin Profit</h5>
                    <h3 data-today-admin-profit>₹{{ $today_admin_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <h5>Today User Profit</h5>
                    <h3 data-today-user-profit>₹{{ $today_user_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>
    
  <div class="row d-flex justify-content-between align-items-center">
    <!-- Left Side -->
    <div class="col-md-6">
        <div class="game-header">
            Period No - {{ $bets[0]->games_no ?? '-' }}
        </div>
    </div>

    <!-- Right Side -->
    <div class="col-md-6 text-end">
        <b id="users_playing_block" style="font-weight: bold; font-size: 22px;">
            Total Users Playing - <span>{{ $total_users_playing ?? 0 }}</span>
        </b>
    </div>
</div>

 {{-- 🔹 Bet Cards --}}
<div class="row justify-content-center g-4" id="card-area">
    @foreach ($bets as $item)
        @php
            // 1) determine number for this card (use item's number if exists, else loop iteration)
            // Note: $loop->iteration starts at 1
            $num = $item->number ?? $loop->iteration;

            // 2) label and bg class
            $label = $num == 1 ? 'Andar' : ($num == 2 ? 'Bahar' : $num);
            $bgClass = $num == 1 ? 'card-bg-andar' : 'card-bg-bahar';

            // 3) amounts from arrays passed by controller (safe fallback 0)
            $amount = $betSummary[$num] ?? 0;
            $winAmount = $winSummary[$num] ?? 0;
        @endphp

        <div class="col-6 col-sm-4 col-md-3 text-center">
            <div class="clickable-card {{ $bgClass }}" data-number="{{ $num }}">
                {{ $label }}
            </div>

            <!-- Bet + Win Amount in single line -->
            <div class="amount-box d-flex justify-content-between px-4">
                <span id="bet-amount-{{ $num }}">Bet Amt: ₹{{ number_format($amount, 2) }}</span>
                <span id="win-amount-{{ $num }}">Win Amt: ₹{{ number_format($winAmount, 2) }}</span>
            </div>
        </div>
    @endforeach
</div>


    {{-- 🔹 Schedule Future Result --}}
    <div class="card-modern shadow-sm mt-4">
        <div class="card-header  d-flex justify-content-between align-items-center" style="background: linear-gradient(60deg,#20c997,#0dcaf0);">
            <h5 class="mb-0"><i class="fas fa-hourglass-half me-2"></i>Schedule Future Result</h5>
            <button class="btn btn-light btn-sm rounded-pill" onclick="copyPeriodNo()" title="Copy Period No">
                <i class="fas fa-copy me-1" style="color:black;"></i> Copy Period:
                <span id="copy-period">{{ $bets[0]->games_no ?? '-' }}</span>
            </button>
        </div>
        <div class="section-bg p-3">
            <form method="post" action="{{ url('ab_future_result_store') }}" class="row g-3 needs-validation" novalidate>
                @csrf
                <div class="col-sm-5">
                    <label><i class="fas fa-calendar-alt me-1"></i>Future Period</label>
                    <input type="text" name="game_no" class="form-control rounded-pill" required>
                </div>
                <div class="col-sm-5">
                    <label><i class="fas fa-sort-numeric-up-alt me-1"></i>Result</label>
                    <select name="number" class="form-select rounded-pill" required>
                        <option value="">Select</option>
                        @for($i=1;$i<=2;$i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-primary rounded-pill">Submit <i class="fas fa-check"></i></button>
                </div>
            </form>
        </div>
    </div>

    {{-- 🔹 Toggle Buttons --}}
    <div class="d-flex justify-content-start gap-3 mb-3 mt-4">
        <button class="btn btn-primary toggle-btn" data-target="future">Future Predictions</button>
        <button class="btn btn-secondary toggle-btn" data-target="user">User Bets</button>
    </div>

    {{-- 🔹 Future Predictions Table --}}
    <div id="table-future" class="toggle-table">
        <div class="card shadow">
            <div class="card-header bg-info text-white"><strong>Future Prediction List</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Period No</th><th>Predicted Number</th><th>Result</th><th>Created</th><th>Updated</th></tr>
                    </thead>
                    <tbody>
                        @forelse($futurePredictions as $prediction)
                            <tr>
                                <td>{{ $prediction->id }}</td>
                                <td>{{ $prediction->gamesno }}</td>
                                <td>{{ $prediction->predicted_number }}</td>
                                <td>{!! $prediction->result_number === 'pending' ? '<span class="badge bg-warning text-dark">Pending</span>' : '<span class="badge bg-success">'.$prediction->result_number.'</span>' !!}</td>
                                <td>{{ $prediction->created_at }}</td>
                                <td>{{ $prediction->updated_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">No predictions found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-center mt-3">
                    {{ $futurePredictions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    {{-- 🔹 User Bets Table --}}
    <div id="table-user" class="toggle-table d-none">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark"><strong>User Bet List</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User ID</th>
                            <th>Period Number</th>
                            <th>Game ID</th>
                            <th>Amount</th>
                            <th>Win Number</th>
                            <th>Win Amount</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userBets as $bet)
                            <tr>
                                <td>{{ $bet->id }}</td>
                                <td>{{ $bet->userid }}</td>
                                <td>{{ $bet->games_no }}</td>
                                <td>{{ $bet->game_id }}</td>
                                <td>{{ $bet->amount }}</td>
                                <td>{{ $bet->win_number }}</td>
                                <td>{{ $bet->win_amount }}</td>
                                <td>{{ $bet->status }}</td>
                                <td>{{ $bet->created_at }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-center">No user bets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $userBets->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- ✅ Scripts -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
    // 🔹 Fetch Data
    function fetchData() {
        const gameid = {{ $gameid }};
        fetch('/ab_fetch/' + gameid)
            .then(response => response.json())
            .then(data => {
                updateBets(data.bets);
                updateProfit(data);
                updateUsers(data.total_users_playing);
            })
            .catch(error => console.error('❌ Fetch error:', error));
    }

    function updateBets(bets) {
        $('#card-area .amount-box').text('₹0'); 
        let currentGameNo = '-';
        bets.forEach(item => {
            $(`#amount-${item.number}`).text(`₹${item.amount}`);
            currentGameNo = item.games_no;
        });
        $('.game-header').text(`Period No - ${currentGameNo}`);
        $('#games_no').val(currentGameNo);
        $('#copy-period').text(currentGameNo);
    }

    function updateProfit(data) {
        $('[data-admin-profit]').text('₹' + (data.total_admin_profit ?? 0));
        $('[data-user-profit]').text('₹' + (data.total_user_profit ?? 0));
        $('[data-today-admin-profit]').text('₹' + (data.today_admin_profit ?? 0));
        $('[data-today-user-profit]').text('₹' + (data.today_user_profit ?? 0));
    }

    function updateUsers(count) {
        $('#users_playing_block span').text(count);
    }

    function copyPeriodNo() {
        let period = $('#games_no').val();
        navigator.clipboard.writeText(period);
        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: `Period number ${period} copied.`,
            timer: 1000,
            showConfirmButton: false
        });
    }

    $(document).ready(function () {
        fetchData();
        setInterval(fetchData, 5000);

        $(document).on('click', '.clickable-card', function () {
            const number = $(this).data('number');
            const game_id = $('#game_id').val();
            const game_no = $('#games_no').val();

            $.ajax({
                url: "{{ route('ab.store') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    number: number,
                    game_id: game_id,
                    game_no: game_no
                },
                success: function () {
                    Swal.fire({
                        icon: 'success',
                        title: 'Result Submitted!',
                        text: 'Your result was added successfully.',
                        timer: 1200,
                        showConfirmButton: false
                    });
                    fetchData();
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    });
                    console.error(xhr.responseText);
                }
            });
        });

        $('.toggle-btn').on('click', function () {
            let target = $(this).data('target');
            $('.toggle-table').addClass('d-none');
            $('#table-' + target).removeClass('d-none');
        });
    });
    
    
    // example update function (adjust to your API response)
function updateBets(payload) {
    // reset all known numbers first (adjust list as needed)
    [1,2,3].forEach(n => {
        $('#bet-amount-' + n).text('Bet: ₹0.00');
        $('#win-amount-' + n).text('Win: ₹0.00');
    });

    // payload.bets should be array of { number, amount, win_amount }
    payload.bets.forEach(item => {
        const num = item.number;
        $('#bet-amount-' + num).text('Bet: ₹' + (parseFloat(item.amount || 0)).toFixed(2));
        $('#win-amount-' + num).text('Win: ₹' + (parseFloat(item.win_amount || 0)).toFixed(2));
    });
}

    
    
    
</script>

@endsection
