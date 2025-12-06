@extends('admin.body.adminmaster')

@section('admin')
@php
    use Illuminate\Support\Facades\DB;

    $userId = session('id'); // get user id from session
    $role_id = DB::table('users')->where('id', $userId)->value('role_id');
   
@endphp

<div class="card shadow-lg rounded">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="card-title">Manage Roles & Permissions</h4>
            <input type="text" id="searchInput" placeholder="🔍 Search permissions..." class="form-control" style="width: 250px;">
        </div>

        <div class="table-responsive mt-4">
            <form action="{{ route('permissions.store') }}" method="POST">
                @csrf
                 @if(in_array($role_id, [1, 2, 3]))
                    <div class="text-end">
                        <button type="submit" class="btn btn-success mt-3">Update Permissions</button>
                    </div>
                @endif
                <br>
                
                <table class="table table-bordered table-hover align-middle w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50%;">Permission Name</th>
                            @if($role_id == 1)
                               
                                <th>Agents</th>
                         
                            @endif
                        </tr>
                    </thead>
                    <tbody>
        

                        @foreach($permission as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                              
                                @if($role_id == 1)
                                    
                                    <td><input type="checkbox" name="Agents[]" value="{{ $item->id }}"
                                        {{ in_array($item->id, $permissionsData['Agents']) ? 'checked' : '' }}></td>

                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

               
            </form>
        </div>
    </div>
</div>

<!-- Search Filter Script -->
<script>
    document.getElementById("searchInput").addEventListener("keyup", function () {
        var input = this.value.toLowerCase();
        var rows = document.querySelectorAll("table tbody tr");

        rows.forEach(function (row) {
            var permissionName = row.cells[0].textContent.toLowerCase();
            row.style.display = permissionName.includes(input) ? "" : "none";
        });
    });
</script>

@endsection
