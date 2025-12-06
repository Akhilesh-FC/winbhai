@extends('admin.app')
@section('app')
<div class="card">
    <div class="card-body">

        <h4>Stokez Management</h4>

        {{-- Create Form --}}
        <form action="{{ route('StokezStore.index') }}" method="POST">
            @csrf
            <div class="row mb-3">
                <div class="col-sm-4">
                    <label>Username *</label>
                    <input type="text" id="username" class="form-control" name="username" placeholder="Username" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '');" maxlength="15" required>
                </div>
                <div class="col-sm-4">
                    <label>Password *</label>
                    <input type="text" id="password" class="form-control" name="password" required>
                </div>
                <div class="col-sm-4">
                    <label>Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Name" oninput="this.value = this.value.replace(/[^a-zA-Z]/g, '');" maxlength="15" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4">
                    <label>Type</label>
                    <select class="form-control" name="type" id="typeSelect" required onchange="updateLabel()">
                        <option value="TN">TN</option>
                        <option value="RV">RV</option>
                    </select>
                </div>

                <div class="col-sm-4">
                    <label id="revenueLabel">Turnover Percent</label>
                    <input type="number" class="form-control" name="revenue" placeholder="0" min="0" max="100" oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5); if(this.value > 100) this.value = 100;" required>
                </div>
                
             @if(session('role_id') == 1)
    <div class="col-sm-4">
        <label>Select Super Stokez</label>
        <select class="form-control" name="parent_id" id="typeSelect" required onchange="updateLabel()">
            <option value="">-- Select --</option>
            @foreach($superStokez as $super)
                <option value="{{ $super->id }}">{{ $super->username }}</option>
            @endforeach
        </select>
    </div>
@endif


               
            </div>

            <button type="submit" class="btn btn-primary">Create Stokez</button>
        </form>

        <hr>
<style>
    .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-responsive table {
    min-width: 1200px;
    white-space: nowrap;
}

</style>
        {{-- Scrollable Table --}}
        <div class="table-responsive mt-3"style="overflow-x: auto;">
            <table class="table table-bordered" style="min-width: 1200px; white-space: nowrap;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Password</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Balance</th>
                        <th>Type</th>
                        <th>Revenue</th>
                        <th>Parent</th>
                        <th>Status</th>
                        <th>Actions</th>
                        <th>Inside Player</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stokez as $key => $item)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->password }}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                            @if($item->role_id == 1) Admin
                            @elseif($item->role_id == 3) Super Stokez
                            @elseif($item->role_id == 2) Stokez
                            @elseif($item->role_id == 4) Agent
                            @else Unknown
                            @endif
                        </td>
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
                        <td>{{ $item->sponsor->username ?? '-' }}</td>
                        <td>
                            @if($item->status == 1)
                            <a href="{{ route('StokezStore.status', ['id' => $item->id, 'status' => 0]) }}" class="btn btn-success btn-sm">Active</a>
                            @else
                            <a href="{{ route('StokezStore.status', ['id' => $item->id, 'status' => 1]) }}" class="btn btn-danger btn-sm">Inactive</a>
                            @endif
                        </td> 
                            @if($item->status == 1)
                        <td><div class="btn btn-success btn-sm"><a href="{{route('StokezStore.status', ['id' => $item->id, 'status' => 0])}}" ><i class="fa fa-check-circle text-white" aria-hidden="true"></i><a></div>
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
                                <button type="submit" class="btn btn-primary btn-sm">
                                    {{ $count }}<br><span style="font-size: 11px;">Players</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>

{{-- Script --}}
<script>
    function updateLabel() {
        var type = document.getElementById("typeSelect").value;
        var label = document.getElementById("revenueLabel");
        label.innerHTML = type === "RV" ? "Revenue Percent" : "Turnover Percent";
    }

    function forceScrollRender() {
        const wrapper = document.querySelector('.table-responsive');
        if (wrapper) {
            wrapper.style.overflowX = 'auto';
            wrapper.style.webkitOverflowScrolling = 'touch';
            wrapper.scrollLeft = 1; // force scroll DOM paint
            wrapper.scrollLeft = 0;
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("username").value = "Stokez_" + Math.random().toString(36).substring(2, 8);
        document.getElementById("password").value = Math.random().toString(36).substring(2, 10);

        setTimeout(forceScrollRender, 300); // Ensures scroll is visible even on dynamic load
    });
</script>
@endsection
