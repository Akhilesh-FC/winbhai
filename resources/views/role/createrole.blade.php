@extends('admin.body.adminmaster') 
@section('admin')  

<div class="container mt-2">
    <div class="card shadow-lg rounded-4">
        @if(Session::has('message'))
            <div class="alert alert-success">{{ Session::get('message') }}</div>
        @endif
        @if(Session::has('fail'))
            <div class="alert alert-danger">{{ Session::get('fail') }}</div>
        @endif

        {{-- Debug output --}}
        <p style="color:red;">Auth Role: {{ $Auth_role }}</p>

        <div class="card-body">
            <h3 class="text-center mb-4">Create Role</h3>

            <form method="POST" action="{{ route('role.store') }}">
                @csrf 
                <div class="row">
                    <!-- Left Side -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Personal Info</h5>

                        <div class="form-group mb-3">
                            <input type="text" name="username" class="form-control rounded-pill" placeholder="Enter Username" value="{{ old('username') }}">
                            <span class="text-danger">@error('username') {{ $message }} @enderror</span>
                        </div>

                        <div class="form-group mb-3">
                            <input type="tel" name="mobile" class="form-control rounded-pill" placeholder="Contact No Here" value="{{ old('mobile') }}">
                            <span class="text-danger">@error('mobile') {{ $message }} @enderror</span>
                        </div>

                        <div class="form-group mb-3">
                            <input type="email" name="email" class="form-control rounded-pill" placeholder="Enter Email" value="{{ old('email') }}">
                            <span class="text-danger">@error('email') {{ $message }} @enderror</span>
                        </div>

                        <div class="form-group mb-3">
                            <input type="password" name="password" class="form-control rounded-pill" placeholder="Password Here">
                            <span class="text-danger">@error('password') {{ $message }} @enderror</span>
                        </div>

                        <div class="form-group mb-3">
                            <input type="password" name="password_confirmation" class="form-control rounded-pill" placeholder="Confirm Password Here">
                            <input type="hidden" name="auth_id" value="{{ $Auth_id }}" class="form-control rounded-pill">
                        </div>

                        {{-- Role Dropdown --}}
                        <div class="form-group mb-3">
                            <select name="role_id" id="roleSelector" class="form-control rounded-pill">
                                <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Select Role</option>

 @if($Auth_role == 1)
    <option value="3" {{ old('role_id') == 3 ? 'selected' : '' }}>Distributor</option>
    <option value="4" {{ old('role_id') == 4 ? 'selected' : '' }}>Agent</option>
   <option value="5" {{ old('role_id') == 5 ? 'selected' : '' }}>Player</option>
@elseif($Auth_role == 3)
    <option value="4" {{ old('role_id') == 4 ? 'selected' : '' }}>Agent</option>
    <option value="5" {{ old('role_id') == 5 ? 'selected' : '' }}>Player</option>
@elseif($Auth_role == 4)
    <option value="5" {{ old('role_id') == 5 ? 'selected' : '' }}>Player</option>
@else
   <!-- <option value="3" {{ old('role_id') == 3 ? 'selected' : '' }}>Distributor</option>-->
    <option value="4" {{ old('role_id') == 4 ? 'selected' : '' }}>Agent</option>
    <!--<option value="5" {{ old('role_id') == 5 ? 'selected' : '' }}>Player</option>-->
@endif


                            </select>
                            <span class="text-danger">@error('role_id') {{ $message }} @enderror</span>
                        </div>
                    </div>

                    <!-- Right Side: Permissions -->
                    <div class="col-md-6">
                        <h5 class="mb-3">Permissions</h5>
                        <div class="row">
                            <div class="col-md-6">
                                @foreach($permissions as $permission)
                                <div class="form-check">
                                    <input class="form-check-input permission-box" type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                        {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label">{{ ucfirst(str_replace('_', ' ', $permission->name)) }}</label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="button-container">
                    <button class="btn btn-info" type="submit">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Styles --}}
<style>
    .card { border-radius: 20px; padding: 20px; }
    .form-control { border-radius: 25px; padding: 10px; }
    .button-container { display: flex; justify-content: center; margin-top: 20px; }
    .btn-info { padding: 8px 16px; font-size: 14px; }
    #roleSelector {
        width: 100%;
        min-height: 45px;
        padding: 10px 15px;
        font-size: 16px;
        appearance: none;
    }
</style>

{{-- JavaScript --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const roleSelect = document.getElementById("roleSelector");
        const permissions = document.querySelectorAll(".permission-box");

        function togglePermissions() {
            if (roleSelect.value == "5") {
                permissions.forEach(el => {
                    el.disabled = true;
                    el.checked = false;
                });
            } else {
                permissions.forEach(el => {
                    el.disabled = false;
                });
            }
        }

        togglePermissions(); // initial
        roleSelect.addEventListener("change", togglePermissions); // on change
    });
</script>

@endsection
