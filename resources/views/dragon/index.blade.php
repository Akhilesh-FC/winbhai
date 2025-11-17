@extends('admin.body.adminmaster')

@section('admin')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    .game-container {
        max-width: 1100px;
        margin: auto;
        padding: 30px;
        background: #f4f6f9;
        border-radius: 15px;
        box-shadow: 0 4px 25px rgba(0, 0, 0, 0.05);
    }

    /* Container for each card + amount */
    .col-6.col-sm-4.col-md-3 {
        height: 250px; /* Adjust height as needed */
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
    }

.card-image-full {
    width: 100%;
    height:100%; /* 👈 fix height deni padegi box ko */
    flex-grow: 1;
    background-size: contain; /* ✅ image fully dikhegi, no crop */
    background-position: center;
    background-repeat: no-repeat;
    position: relative;
    color: white;
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    border-radius: 15px;
    box-shadow: 6px 6px 12px #d1d9e6, -6px -6px 12px #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
    cursor: pointer;
    margin-bottom: 8px;
    overflow: hidden;
}


.amount-box {
    background: #ffffff;
    border-radius: 10px;
    font-weight: bold;
    color: #333;
    padding: 8px 12px;
    font-size: 1rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    text-align: center;
    width: 100%;
    max-width: 200px;
    margin: auto;
}



    .card-image-full:hover {
        transform: scale(1.05);
    }

    .card-overlay {
        background: rgba(0, 0, 0, 0.45);
        padding: 10px 18px;
        border-radius: 12px;
    }

    .amount-box {
        background: #ffffff;
        border-radius: 10px;
        font-weight: bold;
        color: #333;
        padding: 8px 12px;
        font-size: 1rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        text-align: center;
        width: 100%;
        max-width: 150px; /* optional width */
        flex-shrink: 0;
    }

    .game-header {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 30px;
    }

    .toggle-table.d-none {
        display: none;
    }

    @media (max-width: 768px) {
        .card-image-full {
            font-size: 1.2rem;
        }
        .col-6.col-sm-4.col-md-3 {
            height: 220px; /* slightly smaller on tablet */
        }
    }

    @media (max-width: 480px) {
        .card-image-full {
            font-size: 1rem;
        }
        .col-6.col-sm-4.col-md-3 {
            height: 180px; /* smaller on mobile */
        }
    }
</style>

<div class="container game-container mt-5">
    <input type="hidden" id="game_id" value="{{ $gameid }}">
    <input type="hidden" id="games_no" value="{{ $bets[0]->games_no ?? '' }}">

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success shadow">
                <div class="card-body">
                    <h5>Total Admin Profit</h5>
                    <h3>₹{{ $total_admin_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger shadow">
                <div class="card-body">
                    <h5>Total User Profit</h5>
                    <h3>₹{{ $total_user_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info shadow">
                <div class="card-body">
                    <h5>Today Admin Profit</h5>
                    <h3>₹{{ $today_admin_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning shadow">
                <div class="card-body">
                    <h5>Today User Profit</h5>
                    <h3>₹{{ $today_user_profit ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row d-flex justify-content-between align-items-center">
        <div class="col-md-6">
            <div class="game-header">Period No - {{ $bets[0]->games_no ?? '-' }}</div>
        </div>
        <div class="col-md-6 text-end">
            <b id="users_playing_block" style="font-weight: bold; font-size: 22px;">Total Users Playing - <span>{{ $total_users_playing ?? 0 }}</span></b>
        </div>
    </div>

    {{-- 🔥 Cards with Images --}}
   
    
    {{-- 🔥 Cards with Images --}}
<div class="row justify-content-center g-4" id="card-area">
    @foreach ([1 => 'Dragon', 3 => 'Tie', 2 => 'Tiger'] as $num => $label)
        @php
            $amount = $betSummary[$num] ?? 0;
            $winAmount = $winSummary[$num] ?? 0;

            $imgUrl = $num == 1
                ? 'https://25game.codingjourney.in/assets/dragon.png'
                : ($num == 2
                    ? 'https://25game.codingjourney.in/assets/tiger.png'
                    : 'https://25game.codingjourney.in/assets/tie.png');
        @endphp

        <div class="col-6 col-sm-4 col-md-3 text-center">
            <div class="clickable-card card-image-full" 
                 style="background-image: url('{{ $imgUrl }}');" 
                 data-number="{{ $num }}">
            </div>
            
            <!-- Bet + Win Amount in single line -->
            <div class="amount-box d-flex justify-content-between px-4">
                <span id="bet-amount-{{ $num }}">Bet: ₹{{ number_format($amount ?? 0, 2) }}</span>
                <span id="win-amount-{{ $num }}">Win: ₹{{ number_format($winAmount ?? 0, 2) }}</span>
            </div>
        </div>
    @endforeach
</div>




      


    {{-- 🕓 Future Result Form --}}
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-info d-flex justify-content-between align-items-center">
            <h5><i class="fas fa-hourglass-half me-2"></i>Schedule Future Result</h5>
            <button class="btn btn-light btn-sm rounded-pill" onclick="copyPeriodNo()">
                <i class="fas fa-copy me-1"></i> Copy Period: <span id="copy-period">{{ $bets[0]->games_no ?? '-' }}</span>
            </button>
        </div>
        <div class="p-3">
            <form method="post" action="{{ url('dragon_future_result_store') }}" class="row g-3">
                @csrf
                <div class="col-sm-5">
                    <label>Future Period</label>
                    <input type="text" name="game_no" class="form-control rounded-pill" required>
                </div>
                <div class="col-sm-5">
                    <label>Result</label>
                    <select name="number" class="form-select rounded-pill" required>
                        <option value="">Select</option>
                        <option value="1">Dragon</option>
                        <option value="2">Tiger</option>
                        <option value="3">Tie</option>
                    </select>
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-primary rounded-pill">Submit</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 🔁 Toggle Buttons --}}
    <div class="d-flex justify-content-start gap-3 mb-3 mt-4">
        <button class="btn btn-primary toggle-btn" data-target="future">Future Predictions</button>
        <button class="btn btn-secondary toggle-btn" data-target="user">User Bets</button>
    </div>

    {{-- 🔮 Future Predictions Table --}}
    <div id="table-future" class="toggle-table">
        <div class="card shadow">
            <div class="card-header bg-info text-white"><strong>Future Prediction List</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Period No</th>
                            <th>Predicted Number</th>
                            <th>Result</th>
                            <th>Created</th>
                            <th>Updated</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($futurePredictions as $prediction)
                            <tr>
                                <td>{{ $prediction->id }}</td>
                                <td>{{ $prediction->gamesno }}</td>
                                <td>{{ $prediction->predicted_number }}</td>
                                <td>
                                    {!! $prediction->result_number === 'pending'
                                        ? '<span class="badge bg-warning text-dark">Pending</span>'
                                        : '<span class="badge bg-success">'.$prediction->result_number.'</span>' !!}
                                </td>
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

    {{-- 👥 User Bets Table --}}
    <div id="table-user" class="toggle-table d-none">
        <div class="card shadow">
            <div class="card-header bg-warning text-dark"><strong>User Bet List</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>User ID</th><th>Period</th><th>Game ID</th><th>Amount</th><th>Win No</th><th>Win Amt</th><th>Status</th><th>Created</th></tr>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script>
    function fetchData() {
        const gameid = {{ $gameid }};
        fetch('/dragon_fetch/' + gameid)
            .then(res => res.json())
            .then(data => updateBets(data.bets))
            .catch(console.error);
    }

    function updateBets(bets) {
        $('#card-area .amount-box').text('₹0');
        let currentGameNo = '';
        bets.forEach(item => {
            $('#amount-' + item.number).text('₹' + item.amount);
            currentGameNo = item.games_no;
        });
        $('#games_no').val(currentGameNo);
        $('#copy-period').text(currentGameNo);
    }

    function copyPeriodNo() {
        const period = $('#games_no').val();
        navigator.clipboard.writeText(period);
        Swal.fire({ icon: 'success', title: 'Copied!', text: 'Period number copied.', timer: 1000, showConfirmButton: false });
    }

    $(document).ready(function () {
        fetchData();
        setInterval(fetchData, 5000);

        $('.clickable-card').on('click', function () {
            const number = $(this).data('number');
            const game_id = $('#game_id').val();
            const game_no = $('#games_no').val();

            $.post("{{ route('dragon.store') }}", {
                _token: "{{ csrf_token() }}",
                number, game_id, game_no
            }).done(() => {
                Swal.fire({ icon: 'success', title: 'Result Submitted!', timer: 1500, showConfirmButton: false });
                fetchData();
            }).fail(() => {
                Swal.fire({ icon: 'error', title: 'Submission Failed' });
            });
        });

        $('.toggle-btn').on('click', function () {
            const target = $(this).data('target');
            $('.toggle-table').addClass('d-none');
            $('#table-' + target).removeClass('d-none');
        });
    });

    setInterval(() => location.reload(), 30000);
    
    function updateBets(bets) {
    // reset sabhi ko 0 pe
    [1,2,3].forEach(num => {
        $('#bet-amount-' + num).text('Bet: ₹0.00');
        $('#win-amount-' + num).text('Win: ₹0.00');
    });

    // jo bhi bet aaye uske hisaab se update karo
    bets.forEach(item => {
        $('#bet-amount-' + item.number).text('Bet: ₹' + parseFloat(item.amount).toFixed(2));
        $('#win-amount-' + item.number).text('Win: ₹' + parseFloat(item.win_amount ?? 0).toFixed(2));
    });
}




    
    
    
</script>

@endsection
