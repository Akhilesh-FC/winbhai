@extends('admin.body.adminmaster')

@section('admin')

<div class="container mt-4">

    <div class="d-flex justify-content-between">
        <h4>Agent Players</h4>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addPlayerModal">
            Add Player
        </button>
    </div>

    <hr>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Join Date</th>
            </tr>
        </thead>

        <tbody>
            @foreach($players as $index => $player)
            <tr>
                <td>{{ $index+1 }}</td>
                <td>{{ $player->username }}</td>
                <td>{{ $player->email }}</td>
                <td>{{ $player->mobile }}</td>
                <td>{{ $player->created_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- 🚀 Add Player Modal -->
<div class="modal fade" id="addPlayerModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form method="POST" action="{{ route('agent.player.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add New Player</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Mobile</label>
                        <input type="text" name="mobile" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Create Player</button>
                </div>
            </form>

        </div>
    </div>
</div>

@endsection
