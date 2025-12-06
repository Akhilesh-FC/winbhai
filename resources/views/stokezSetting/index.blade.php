@extends('admin.app')
@section('app')

@php
    $role_id = $role_id ?? null;
    $superStokezUsers = $superStokezUsers ?? [];
    $stokezUsers = $stokezUsers ?? [];
    $agentUsers = $agentUsers ?? [];
    $selectedUser = $selectedUser ?? null;
    $percentage = $percentage ?? null;
@endphp

<div class="container d-flex justify-content-center mt-5">
    <div class="card shadow border-0 w-100" style="max-width: 700px;">
        <div class="card-body p-5">
            <h2 class="text-center mb-5 fw-bold text-primary">Stokez Settings</h2>

            {{-- Show Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please check the form below for errors:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Super Stokez --}}
            <form method="POST" action="{{ route('stokezSetting-index.update') }}" class="mb-5">
                @csrf
                <a href="{{ route('Super_Stokez_Store.filter', 2) }}"><h4 class="mb-3 text-secondary">Super Stokez</h4></a>
                <div class="dropdown mb-3">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ----select----
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($superStokezUsers as $user)
                            <li><a class="dropdown-item" href="{{ route('Super_Stokez_Store.filter', [2, $user->id]) }}">{{ $user->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                @if($role_id == 2 && $selectedUser)
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="role_id" value="2">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Enter Percentage for <strong>{{ $selectedUser->name }}</strong></label>
                        <input type="number" class="form-control" name="percentage" placeholder="Enter percentage" min="0" max="100" value="{{ $percentage }}" required>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary px-5">Update</button>
                    </div>
                @endif
            </form>

            @if(isset($users) && $role_id == 2)
                <div class="mt-4">
                    <h5 class="mb-3 text-primary">Super Stokez List</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Username</th>
                                    <th>Percentage</th>
                                    <th>Name</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->percentage }}</td>
                                        <td>{{ $user->name }}</td> 
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Stokez --}}
            <form method="POST" action="{{ route('stokezSetting-index.update') }}" class="mb-5">
                @csrf
                <a href="{{ route('Super_Stokez_Store.filter', 3) }}"><h4 class="mb-3 text-secondary">Stokez</h4></a>
                <div class="dropdown mb-3">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ----select----
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($stokezUsers as $user)
                            <li><a class="dropdown-item" href="{{ route('Super_Stokez_Store.filter', [3, $user->id]) }}">{{ $user->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                @if($role_id == 3 && $selectedUser)
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="role_id" value="3">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Enter Percentage for <strong>{{ $selectedUser->name }}</strong></label>
                        <input type="number" class="form-control" name="percentage" placeholder="Enter percentage" min="0" max="100" value="{{ $percentage }}" required>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary px-5">Update</button>
                    </div>
                @endif
            </form>

            @if(isset($users) && $role_id == 3)
                <div class="mt-4">
                    <h5 class="mb-3 text-primary">Stokez List</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Percentage</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->percentage }}</td> 
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Agents --}}
            <form method="POST" action="{{ route('stokezSetting-index.update') }}">
                @csrf
                <a href="{{ route('Super_Stokez_Store.filter', 4) }}"><h4 class="mb-3 text-secondary">Agents</h4></a>
                <div class="dropdown mb-3">
                    <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        ----select----
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($agentUsers as $user)
                            <li><a class="dropdown-item" href="{{ route('Super_Stokez_Store.filter', [4, $user->id]) }}">{{ $user->name }}</a></li>
                        @endforeach
                    </ul>
                </div>

                @if($role_id == 4 && $selectedUser)
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <input type="hidden" name="role_id" value="4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Enter Percentage for <strong>{{ $selectedUser->name }}</strong></label>
                        <input type="number" class="form-control" name="percentage" placeholder="Enter percentage" min="0" max="100" value="{{ $percentage }}" required>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary px-5">Update</button>
                    </div>
                @endif
            </form>

            @if(isset($users) && $role_id == 4)
                <div class="mt-4">
                    <h5 class="mb-3 text-primary">Agents List</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Percentage</th> 
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->percentage }}</td> 
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
