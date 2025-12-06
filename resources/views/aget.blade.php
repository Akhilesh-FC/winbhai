@extends('admin.body.adminmaster')

@section('admin')

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h5>Agents</h5>
            <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#createAgentModal">Create</button>
        </div>

        <!-- Create Agent Modal -->
       <!-- Create Agent Modal -->
<div class="modal fade" id="createAgentModal" tabindex="-1" role="dialog" aria-labelledby="createAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('agentStore') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Agent</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" id="username" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="text" name="password" id="password" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mobile No</label>
                        <input type="text" name="mobile" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Create</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


        <hr>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="agentTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Role</th>
                         <th>Email</th>
                        <th>Mobile No</th>
                        <th>Status</th>
                        <th>Action</th>
                        <th>Permission</th>
                        <th>view Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agents as $index => $agent)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $agent->username }}</td>
                            <td>{{ $agent->password }}</td>
                            <td>{{$agent->role_name }}</td>
                            <td>{{$agent->email }}</td>
                            <td>{{$agent->mobile }}</td>
                            <td>
                                @if($agent->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('agent.status', ['id' => $agent->id, 'status' => $agent->status == 1 ? 0 : 1]) }}" class="btn btn-sm btn-warning">
                                    {{ $agent->status == 1 ? 'Deactivate' : 'Activate' }}
                                </a>

                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editAgent{{ $agent->id }}">Edit</button>

                                <!-- Edit Modal -->
                              <div class="modal fade" id="editAgent{{ $agent->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <form action="{{ route('agentUpdate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Agent</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="{{ $agent->id }}">

                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="{{ $agent->username }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Password</label>
                        <input type="text" name="password" class="form-control" value="{{ $agent->password }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $agent->email }}" required>
                    </div>

                    <div class="mb-3">
                        <label>Mobile No</label>
                        <input type="text" name="mobile" class="form-control" value="{{ $agent->mobile }}" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

                                <!-- End Edit Modal -->
                            </td>
                            
                            
                                                                                        
<!--          <td>-->
                                 
<!--    <button class="btn btn-sm btn-dark" data-toggle="modal" data-target="#permissionModal{{ $agent->id }}">Permission</button>-->
<!--</td>-->

                       <td>
    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal{{ $agent->id }}">
        View Permission
    </button>

<div class="modal fade" id="modal{{ $agent->id }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('update.permission', $agent->id) }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Update Permission</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body ml-4">
                    <div class="row">
                        <!-- First Column -->
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="1">
                                <label class="form-check-label">Dashboard</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="2">
                                <label class="form-check-label">Game List</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="3">
                                <label class="form-check-label">Attendance</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="4">
                                <label class="form-check-label">Players</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="5">
                                <label class="form-check-label">MLM Levels</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="6">
                                <label class="form-check-label">Color Prediction</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="7">
                                <label class="form-check-label">Aviator Game</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="8">
                                <label class="form-check-label">Bet History</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="9">
                                <label class="form-check-label"> Chicken Road Game</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="14">
                                <label class="form-check-label">Gift</label>
                            </div>
                        </div>

                        <!-- Second Column -->
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="15">
                                <label class="form-check-label">Gift redeemed history</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="13">
                                <label class="form-check-label">Activity Banner</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="18">
                                <label class="form-check-label">Depsoit</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="20">
                                <label class="form-check-label">Withdraw</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="21">
                                <label class="form-check-label">Usdt Qr code</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="22">
                                <label class="form-check-label">USDT Depsoit</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="23">
                                <label class="form-check-label">USDT Withdrawl</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="24">
                                <label class="form-check-label"> Notice</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="25">
                                <label class="form-check-label">Setting</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="26">
                                <label class="form-check-label">Support Setting</label>
                            </div>
                        </div>
                        
                         <!-- Third Column -->
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="27">
                                <label class="form-check-label">Change Password</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="28">
                                <label class="form-check-label">Logout</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="35">
                                <label class="form-check-label">Manual Depsoit</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="36">
                                <label class="form-check-label">Manual Withdrawl</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="37">
                                <label class="form-check-label">Manual QR</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="38">
                                <label class="form-check-label">USDT Conversion</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="40">
                                <label class="form-check-label">Campaign</label>
                            </div>
                            
                        </div>
                        
                         <!-- Fourth Column -->
                        <div class="col-md-6">
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="41">
                                <label class="form-check-label">Conversion</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="42">
                                <label class="form-check-label">Feedback </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="43">
                                <label class="form-check-label">Sponser</label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="44">
                                <label class="form-check-label">Paymode show</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="45">
                                <label class="form-check-label">Offer</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="47">
                                <label class="form-check-label">revenue</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="48">
                                <label class="form-check-label">game_slider_img</label>
                            </div>
                            
                        </div>
                        
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
</td>
<td>
    <a href="{{ route('agent.users', $agent->id) }}" class="btn btn-primary">View Details</a>

</td>

                            

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>



<script>
    document.addEventListener("DOMContentLoaded", function () {
        const username = 'Ag_' + Math.random().toString(36).substring(2, 6); // Short username
        const password = Math.random().toString(36).substring(2, 10);
        document.getElementById("username").value = username;
        document.getElementById("password").value = password;
    });
</script>

@endsection
