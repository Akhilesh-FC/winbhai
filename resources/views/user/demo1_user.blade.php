@extends('admin.body.adminmaster')

@section('admin')

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5>Demo User Details</h5>
        </div>

        <div class="card-body">

            <table class="table table-bordered">
                <tr>
                    <th>Username</th>
                    <td>{{ $demo_user->name }}</td>
                </tr>

                <tr>
                    <th>Email</th>
                    <td>{{ $demo_user->email }}</td>
                </tr>

                <tr>
                    <th>Password</th>
                    <td>{{ $demo_user->password }}</td>
                </tr>

                <tr>
                    <th>Mobile</th>
                    <td>{{ $demo_user->mobile }}</td>
                </tr>
            </table>

        </div>
    </div>
</div>

@endsection
