@extends('admin.body.adminmaster')

@section('admin')

<style>
/* ✅ Modal image styling */
.modal-img {
    width: 100%;
    height: auto;
    border-radius: 10px;
}
.table th, .table td {
    text-align: center;
    vertical-align: middle;
}
</style>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="white_shd full margin_bottom_30 shadow-sm rounded bg-white p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="font-weight-bold text-dark">📋 Manual Deposit List</h4>
                </div>

                <div class="table_section">
                    <div class="table-responsive">
                        <table id="example" class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Id</th>
                                    <th>User Id</th>
                                    <th>User Name</th>
                                    <th>Mobile</th>
                                    <th>Order Id</th>
                                    <th>INR Amount</th>
                                    <th>Screenshot</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deposits as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td>{{ $item->userid }}</td>
                                    <td>{{ $item->uname }}</td>
                                    <td>{{ $item->mobile }}</td>
                                    <td>{{ $item->order_id }}</td>
                                    <td>{{ $item->cash }}</td>

                                    <!-- ✅ Screenshot View Button -->
                                    <td>
                                        @if($item->typeimage)
                                            <button class="btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#imageModal{{$item->id}}">
                                                View
                                            </button>
                                        @else
                                            <span class="text-muted">No Image</span>
                                        @endif
                                    </td>

                                    <!-- ✅ Dropdown Status -->
                                    <td>
                                        @if($item->status == 1)
                                        <div class="dropdown">
                                            <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton{{$item->id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                Pending
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton{{$item->id}}">
                                                <a class="dropdown-item text-success" href="{{ route('manual_success', $item->id) }}">Mark as Success</a>
                                                <a class="dropdown-item text-danger" href="{{ route('manual_reject', $item->id) }}">Reject</a>
                                            </div>
                                        </div>
                                        @elseif($item->status == 2)
                                        <button class="btn btn-success">Success</button>
                                        @elseif($item->status == 3)
                                        <button class="btn btn-danger">Rejected</button>
                                        @else
                                        <span class="badge badge-secondary">Unknown</span>
                                        @endif
                                    </td>

                                    <td>{{ $item->created_at }}</td>
                                </tr>

                                <!-- ✅ Image Preview Modal -->
                                <div class="modal fade" id="imageModal{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel{{$item->id}}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="imageModalLabel{{$item->id}}">Deposit Screenshot</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <img src="{{ $item->typeimage }}" class="modal-img" alt="Deposit Screenshot">
                                            </div>
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
</div>

<!-- ✅ Bootstrap + jQuery for dropdown and modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

@endsection
