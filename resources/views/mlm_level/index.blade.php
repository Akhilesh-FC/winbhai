@extends('admin.body.adminmaster')

@section('admin')

<style>
    body {
        background: linear-gradient(135deg, #00b09b, #96c93d);
        min-height: 100vh;
    }

    .white_shd {
        background: white;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        padding: 20px;
    }

    .graph_head h2 {
        font-weight: bold;
    }

    .btn-info {
        background: linear-gradient(45deg, #17a2b8, #138496);
        border: none;
        color: #fff;
        transition: 0.3s;
    }

    .btn-info:hover {
        box-shadow: 0 0 10px rgba(23,162,184,0.7);
        transform: scale(1.05);
    }

    .table thead {
        background: linear-gradient(45deg, #343a40, #23272b);
        color: #fff;
    }

    .table td, .table th {
        vertical-align: middle;
        text-align: center;
    }

    .fa-edit, .fa-trash {
        cursor: pointer;
        transition: 0.3s;
    }

    .fa-edit:hover {
        color: #17a2b8;
        transform: scale(1.2);
    }

    .fa-trash:hover {
        color: red;
        transform: scale(1.2);
    }

    .modal-content {
        border-radius: 15px;
    }

    .modal-header {
        background: linear-gradient(45deg, #17a2b8, #138496);
        color: #fff;
    }

    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
    }

    .btn-primary:hover {
        box-shadow: 0 0 10px rgba(0,123,255,0.7);
        transform: scale(1.05);
    }
</style>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="white_shd">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>MLM Level List</h2>
{{-- <button type="button" class="btn btn-info" data-toggle="modal" data-target="#exampleModalCenter">Add MLM Level</button> --}}
                </div>

                <div class="table-responsive">
                    <table id="example" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Level</th>
                                <th>Count</th>
                                <th>Commission</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mlmlevels as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td> 
                                <td>{{$item->name}}</td>
                                <td>{{$item->count}}</td>
                                <td>{{$item->commission}}</td>
                                <td>
                                    <i class="fa fa-edit mr-2" data-toggle="modal" data-target="#editModal{{$item->id}}"></i>
                                   {{-- <a href="{{route('mlmlevel.delete',$item->id)}}"><i class="fa fa-trash"></i></a> --}}

                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal{{$item->id}}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit MLM Level</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form action="{{route('mlmlevel.update',$item->id)}}" method="post">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Name</label>
                                                    <input type="text" class="form-control" name="name" value="{{$item->name}}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Count</label>
                                                    <input type="text" class="form-control" name="count" value="{{$item->count}}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Commission</label>
                                                    <input type="text" class="form-control" name="commission" value="{{$item->commission}}" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add MLM Level</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{route('mlmlevel.store')}}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Count</label>
                        <input type="text" class="form-control" name="count" required>
                    </div>
                    <div class="form-group">
                        <label>Commission</label>
                        <input type="text" class="form-control" name="commission" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
