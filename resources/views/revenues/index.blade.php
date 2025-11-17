@extends('admin.body.adminmaster')

@section('admin')
<div class="container mt-4">

    <h3>Revenue Management</h3>

    @if(session('success'))
        <div class="alert alert-success mt-2">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Revenue</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>
                    <form action="{{ route('revenues.update') }}" method="POST" class="d-flex">
                        @csrf
                        <input type="hidden" name="id" value="{{ $item->id }}">
                        <input type="number" step="0.01" name="revenue" value="{{ $item->revenue }}" class="form-control" required>

                        <button type="submit" class="btn btn-primary ms-2">
                            Update
                        </button>
                    </form>
                </td>
                <td>
                    <button class="btn btn-success" disabled>Editable</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection
