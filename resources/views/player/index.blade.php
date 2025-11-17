@extends('admin.body.adminmaster')
@section('admin')

<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-4">Player</h5>
        {{-- Create Model --}}
        @if(auth()->user()->role_id == 1 || auth()->user()->role_id == 2)
         <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter"
            style="position:absolute; top:30px; right:50px;">
            Create
        </button> 
        @endif
        <form action="{{ route('PlayerStore') }}" method="POST">
            @csrf
            <!-- Modal -->
            <div class="modal fade" id="exampleModalCenter" role="dialog" aria-labelledby="exampleModalCenterTitle"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div class="card-body">
                                {{-- Section-2 --}}
                                
                                <h4 class="card-title">Create Player</h4>
                                <hr>
                                <div class="form">
                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label>Username<small>*</small></label>
                                            <input type="text" id="username" class="form-control" name="username"placeholder="Username"oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');"maxlength="15"required>
                                        </div>
                                        <!--<div class="col-sm-6">-->
                                        <!--    <label for="">Password<small>*</small></label>-->
                                        <!--    <input type="text" id="password" class="form-control" name="password">-->
                                        <!--</div>-->
                                        <div class="col-sm-6">
    <label>Password<small>*</small></label>
    <div class="input-group">
        <input type="password" id="password" class="form-control" name="password" maxlength="10"required>
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
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name"placeholder="Name"oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');"maxlength="15"required>
                                    </div>
                                   
                                </div>
                                 
                                 <div class="row mb-3">
                                     <div class="col-sm-6">
                                         <label>Revenue<small>*</small></label>
                                            <input type="text" id="revenue" class="form-control" name="revenue"placeholder="Enter revenue"min="0"  onkeypress="return preventMinus(event)"
       onpaste="handlePaste(event)" min="0" max="100"oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(this.value > 100) this.value = 100;"required>
                                     </div>
                                       <div class="col-sm-6">
                                    <label>Select  Agent<small>*</small></label>
                                    <select class="form-control" name="parent_id" required>
                                        <option value="">-- Select --</option>
                                        @foreach($agents as $super)
                                            <option value="{{ $super->id }}">{{ $super->username }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>   

                            </div>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <hr>
        <div class="table-responsive">
            <table id="zero_config" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><b>ID</b></th>
                        <th><b>Login Device Name</b></th>
                        <th><b>Username</b></th>
                        <th><b>Name</b></th>
                        <th><b>Password</b></th>
                        <!--<th><b>Mobile</b></th>-->
                        <!--<th><b>Email</b></th>-->
                        <th><b>Parent</b></th>
                        <th><b>Balance</b></th>
                        <th><b>Revenue</b></th>
                        <th><b>Action</b></th>
                        <th><b>Block/Unblock</b></th>
                        <th><b>View/Details</b></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($user as $key => $item)
                    <tr>
                        <td>{{ $key+1}}</td>
                 <!-- Include Font Awesome (latest) -->
<!-- Include Font Awesome (latest) -->
<td style="text-align:center; padding:10px; background:#f8f9fa; border-radius:6px;">
    @php
        $deviceName = strtolower($item->device_name);
        $iconSrc = '';

        if (str_contains($deviceName, 'iphone') || str_contains($deviceName, 'ipad') || str_contains($deviceName, 'mac')) {
            $iconSrc = asset('assets/images/images (6).jpeg');
        } elseif (str_contains($deviceName, 'android')) {
            $iconSrc = asset('assets/images/images (4).jpeg');
        } elseif (str_contains($deviceName, 'windows')) {
            $iconSrc = asset('assets/images/images (5).jpeg');
        } else {
            $iconSrc = ''; // koi image nahi hai
        }
    @endphp

    @if($iconSrc)
        <img src="{{ $iconSrc }}" alt="Device Icon" style="width:50px; height:50px; vertical-align:middle;">
    @else
        <span style="color:red; font-weight:600;">Device Not Found</span>
    @endif
</td>



   <td>
    @if($item->login_status == 1)
        <span class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center" 
              style="width:20px; height:20px; color:white; font-size:12px;">
            <i class="fa fa-check"></i>
        </span>
        {{ $item->username }}
    @else
        <span class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center" 
              style="width:20px; height:20px; color:white; font-size:12px;">
            <i class="fa fa-times"></i>
        </span>
        {{ $item->username }}
    @endif
</td>


                        <td>{{ $item->name }}</td>
                        <td>{{ $item->password }}</td>
                        <!--<td>{{ $item->mobile }}</td>-->
                        <!--<td>{{ $item->email }}</td>-->
                        <td>{{ $item->sponsor->username }}</td>
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
                        <!--add-->
                        <td>{{ $item->revenue }}</td>
                        <!--end add-->
                        <!--<td></td>-->
                        <!--<td></td>-->
                        <!--<td></td>-->
                        <!--<td></td>-->
                        <!--<td></td>-->
                        @if($item->status == 1)
                        <td>
                            <div class="btn btn-success btn-sm"><a
                                    href="{{route('playerStatus', ['id' => $item->id, 'status' => 0])}}"><i
                                        class="fa fa-check-circle text-white" aria-hidden="true"></i><a></div>

                            <div class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#exampleModalpass{{$item->id}}"><a href="#"><i
                                        class="fa fa-pencil text-white" aria-hidden="true"></i><a></div>

                            <!-- Modal -->
                            <div class="modal fade" id="exampleModalpass{{$item->id}}" tabindex="-1"
                                aria-labelledby="exampleModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="exampleModalLabel">Edit</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"> X </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{route('PlayerUpdate')}}" method="POST">
                                                @csrf
                                                <div class="form">
                                                    <div class="row mb-3">
                                                        <input type="hidden" name="user_id" value="{{$item->id}}">
                                                        <div class="col-sm-6">
                                                            <label>Password</label>
                                                            <input type="text" class="form-control" name="password"
                                                                value="{{$item->password}}" required>
                                                            @error('password')
                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        
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
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="submit" class="btn btn-primary">Save changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        @elseif($item->status == 0)
                        <td>
                        <div class="btn btn-danger btn-sm"><a
                                    href="{{route('playerStatus', ['id' => $item->id, 'status' => 1])}}"><i
                                    class="fa fa-times-circle text-white" aria-hidden="true"></i><a></div>
                        </td>
                        @else
                        <td></td>
                        @endif

                        <td>
                            <center>
                                @if($item->status == '1')
                                <a href="{{ url('/player-index/update/'.$item->id.'/0') }}">
                                    <i class="fa fa-check-circle" style="color: green;"></i>
                                </a>
                                @else
                                <a href="{{ url('/player-index/update/'.$item->id.'/1') }}">
                                    <i class="fa fa-times-circle" style="color: red;"></i>
                                </a>
                                @endif
                            </center>
                        </td>

                        <td>
                            <a href="{{ route('player_activity_info', $item->id )}}" class="btn btn-secondary">view </a>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="row justify-content-center">
                <nav>
                    <ul class="pagination pagination-sm">
                        <!-- Bootstrap class for smaller pagination -->
                        {{ $user->links('pagination::bootstrap-4') }}
                        <!-- Use Bootstrap 4 pagination style -->
                    </ul>
                </nav>
            </div>
        </div>

    </div>
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
document.addEventListener("DOMContentLoaded", function() {
    // Generate random username and password
    const username = 'Player_' + Math.random().toString(36).substring(2, 8);
    const password = Math.random().toString(36).substring(2, 10);

    // Set the values in the input fields
    document.getElementById("username").value = username;
    document.getElementById("password").value = password;
});
</script>

<script>
    function preventMinus(e) {
    return e.key !== '-' && e.keyCode !== 45;
}

function handlePaste(e) {
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    if (pasted.includes('-')) {
        e.preventDefault();
    }
}

</script>
@endsection