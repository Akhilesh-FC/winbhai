@extends('admin.app')
@section('app')
    <div class="card"> 
        <div class="card-body"> 
            <div class="d-flex justify-content-between">
                <h5>Super Stokez</h5>
                {{-- Create Model --}}
                <div><button type="button" class="btn btn-primary mt-2" data-toggle="modal" data-target="#exampleModalCenter">Create</button></div>
            </div>
            
                <!-- Modal -->
                <div class="modal fade" id="exampleModalCenter" role="dialog" aria-labelledby="exampleModalCenterTitle"
                    aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            
                             <div class="modal-header">
                                <h5 class="modal-title">Create Super Stokez</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div> 
                         <hr>
                                
                            <div class="card-body">
                                <form action="{{ route('SuperStokezStore') }}" method="POST">
                                    @csrf
                                    <div class="form">
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label>Username<small>*</small></label>
                                                <input type="text" id="username" class="form-control" name="username"placeholder="Username"maxlength="15"oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');" required>
                                                @error('username')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                            
  <div class="col-sm-6">
    <label>Password<small>*</small></label>
    <div class="input-group">
        <input type="password" id="password" class="form-control" name="password" maxlength="10">
        <div class="input-group-append">
            <span class="input-group-text bg-light" onclick="togglePassword()" style="cursor: pointer;">
                <i class="fas fa-eye" id="toggleIcon" style="color: #4caf50;"></i>
            </span>
        </div>
    </div>
    @error('password')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>

                                        </div>
                                        
   

                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <label>Name</label>
                                                <input type="text" class="form-control" name="name" placeholder="Name" maxlength="15" oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');"required>
                                                @error('name')
                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                @enderror
                                            </div> 
                                            
                                         <div class="col-sm-6">
        <label>Type</label>
        <select class="form-control" name="type" id="typeSelect" required onchange="updateLabel()">
            <option value="TN">TN</option>
            <option value="RV">RV</option> 
        </select>
        @error('type')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-6">
        <label id="revenueLabel">Revenue<small></small></label>
        <input type="number" class="form-control" name="revenue" placeholder="0"  min="0" max="100"oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(this.value > 100) this.value = 100;"required>
    </div>
    <!--<div class="col-sm-6">-->
    <!--    <label for="">Parent<small>*</small></label>-->
    <!--    @php-->
    <!--        use App\Models\User;-->
    <!--        $users = User::where('role_id', 2)->get();-->
    <!--    @endphp-->
    <!--    <select class="form-control" name="parent_id">-->
    <!--        @foreach($users as $item)-->
    <!--            <option value="{{ $item->id }}"> {{ $item->name }} {{ $item->mobile ? '(' . $item->mobile . ')' : '' }}</option>-->
    <!--        @endforeach-->
    <!--    </select>-->
    <!--    @error('parent_id')-->
    <!--        <div class="alert alert-danger">{{ $message }}</div>-->
    <!--    @enderror-->
    <!--</div>-->
</div>


                                        </div>

                                </div>
                                
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary">Create</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div> 
    

    <hr>
    <div class="table-responsive">
        <table id="zero_config" class="table table-striped table-bordered">
            <thead>

                <tr>
                    <th><b>id</b></th>
                    <th><b>Username</b></th>
                    <th><b>Password</b></th>
                    <th><b>Name</b></th>
                    <th><b>Role</b></th> 
                    <!--<th><b>Mobile</b></th>-->
                    <th><b>Balance</b></th>
                    <th><b>Type</b></th>
                    <th><b>Revenue</b></th>
                    <th><b>Parent</b></th>
                     <th><b>Actions</b></th>
                    <th><b>Inside Player</b></th>
                   
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->password }}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                            @if($item->role_id == 1)
                                Admin
                            @elseif($item->role_id == 3)
                                Super Stokez
                            @elseif($item->role_id == 2)
                                Stokez
                            @elseif($item->role_id == 4)
                                Agents
                            @elseif($item->role_id == 5)
                                Player
                            @else
                                Unknown
                            @endif
                        </td>
                        <!--<td>{{ $item->mobile }}</td>-->
                        <td>{{$item->wallet}}
                            <div style="display: flex; gap: 10px;">
                                <div class="btn btn-info btn-sm" style="border-radius: 10px;" data-toggle="modal"
                                    data-target="#exampleModalCenter{{$item->id}}">
                                    <i class="fa fa-plus" style="font-size:15px"></i>
                                </div>
                                <div class="btn btn-danger btn-sm" style="border-radius: 10px;" data-toggle="modal"
                                    data-target="#subtractWalletModal{{$item->id}}">
                                    <i class="fa fa-minus" style="font-size:15px"></i>
                                </div>
                            </div>



                            <!----------------------------ADD WALLET---------------------------------------->
                            <div class="modal fade" id="exampleModalCenter{{$item->id}}" tabindex="-1" role="dialog"
                                aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLongTitle">Add Wallet</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{route('wallet.store',$item->id)}}" method="post"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                        <div class="form-group col-md-6">
                                                            <label for="wallet">Wallet Amount</label>
                                                            <input type="text" class="form-control" id="wallet"
                                                                name="wallet" value="" placeholder="Enter Amount">
                                                            @error('wallet')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                              
                            <!---------------------Subtract Wallet Modal--------------------------->
                            <div class="modal fade" id="subtractWalletModal{{$item->id}}" tabindex="-1" role="dialog"
                                aria-labelledby="subtractWalletModalTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="subtractWalletModalTitle">Subtract Wallet</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="{{route('wallet.subtract', $item->id)}}" method="POST"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="container-fluid">
                                                    <div class="row">
                                                        <div class="form-group col-md-12">
                                                            <label for="wallet">Wallet Amount</label>
                                                            <input type="text" class="form-control" id="wallet"
                                                                name="wallet" value="" placeholder="Enter Amount">
                                                            @error('wallet')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </td>
                        <td>{{ $item->type }}</td>
                        <td>{{ $item->revenue }}</td>
                        <td>{{ $item->sponsor->username }}</td>
                        @if($item->status == 1)
                        <td><div class="btn btn-success btn-sm"><a href="{{route('SuperStokezStore.status', ['id' => $item->id, 'status' => 0])}}" ><i class="fa fa-check-circle text-white" aria-hidden="true"></i><a></div>
                        <div class="btn btn-primary btn-sm"  data-bs-toggle="modal" data-bs-target="#exampleModalpass{{$item->id}}" ><a href="#"><i class="fa fa-pencil text-white" aria-hidden="true"></i><a></div>
                        
                        


                    <!-- Modal -->
<div class="modal fade" id="exampleModalpass{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> X </button>
            </div>
            <div class="modal-body">
                <form action="{{route('SuperStokez.Upadte')}}" method="POST">
                    @csrf
                    <div class="form">
                        <div class="row mb-3">
                            <input type="hidden" name="user_id" value="{{$item->id}}">
                            <div class="col-sm-6">
                                <label>Password</label>
                                <input type="text" class="form-control" name="password" value="{{$item->password}}" required>
                                @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-sm-6">
                                <label>Type</label>
                                <select class="form-control" name="type" required>
                                    <option value="TN" {{ $item->type == 'TN' ? 'selected' : '' }}>TN</option>
                                    <option value="RV" {{ $item->type == 'RV' ? 'selected' : '' }}>RV</option>
                                </select>
                                @error('type')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-6">
                                <label>Revenue</label>
                                <input type="text" class="form-control" name="revenue" value="{{$item->revenue}}" required>
                                @error('revenue')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

                        
                        
                        
                        </td>
                        @elseif($item->status == 0)
                        <td><div class="btn btn-danger btn-sm"><a href="{{route('SuperStokezStore.status', ['id' => $item->id, 'status' => 1])}}" ><i class="fa fa-times-circle text-white" aria-hidden="true"></i><a></div>
                      
                        </td>
                        @else
                        
                        <td></td>
                        @endif
                        
                         <td class="text-center">
                @php
                    $count = \App\Models\User::where('created_by', $item->id)->count();
                @endphp

                <form method="POST" action="{{ route('player.index') }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="created_by" value="{{ $item->id }}">
                    <button type="submit" style="padding: 4px 8px; background: #007bff; color: #fff; border: none; border-radius: 4px; font-size: 13px; cursor: pointer;">
                        {{ $count }}<br>
                        <span style="font-size: 11px;">Players</span>
                    </button>
                </form>
            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <script>
    function togglePassword() {
        const passwordInput = document.getElementById("password");
        const icon = document.getElementById("toggleIcon");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            passwordInput.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>

    <script>
    function updateLabel() {
        var type = document.getElementById("typeSelect").value;
        var label = document.getElementById("revenueLabel");

        if (type === "RV") {
            label.innerHTML = "Revenue Percent<small></small>";
        } else {
            label.innerHTML = "Turnover Percent<small></small>";
        }
    }
</script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Generate random username and password
        const username = 'SStokez_' + Math.random().toString(36).substring(2, 8);
        const password = Math.random().toString(36).substring(2, 10);

        // Set the values in the input fields
        document.getElementById("username").value = username;
        document.getElementById("password").value = password;
    });
</script>
@endsection