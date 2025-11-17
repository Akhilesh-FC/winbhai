@extends('admin.body.adminmaster')

@section('admin')
<style>
.table thead th {
    background-color: #343a40;
    color: white;
    text-align: center;
}
.table td, .table th {
    text-align: center;
    vertical-align: middle;
}
.qr-img {
    width: 50px;
    height: 50px;
    cursor: pointer;
    transition: transform 0.2s;
}
.qr-img:hover {
    transform: scale(1.3);
}
.switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 26px;
}
.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: #ccc;
    transition: 0.4s;
    border-radius: 34px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 20px; width: 20px;
    left: 3px; bottom: 3px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
}
input:checked + .slider {
    background-color: #28a745;
}
input:checked + .slider:before {
    transform: translateX(24px);
}
.modal-img {
    width: 100%;
    height: auto;
}
</style>

<div class="container-fluid mt-5">
  <div class="row">
    <div class="col-md-12">
      <div class="white_shd full margin_bottom_30 shadow-sm p-4 rounded bg-white">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h4 class="mb-0 font-weight-bold">📱 Manual QR Management</h4>
        </div>

        <div class="table_section">
          <div class="table-responsive">
            <table id="example" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Sr.No</th>
                  <th>Option Name</th>
                  <th>QR Code</th>
                  <th>Wallet Address</th>
                  <th>Status</th>
                  <th>Account Number</th>
                  <th>IFSC Code</th>
                  <th>Account Name</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($manual as $item)
                <tr>
                  <td>{{ $item->id }}</td>
                  <td>{{ $item->option_name }}</td>
                  <td>
                    <img src="{{ $item->qr_code }}" alt="QR Code" class="qr-img" data-toggle="modal" data-target="#qrPreview{{$item->id}}">
                  </td>
                  <td>{{ $item->wallet_address }}</td>
                  <td>
                    <label class="switch">
                      <input type="checkbox" {{ $item->status == 1 ? 'checked' : '' }} onchange="toggleStatus({{ $item->id }}, this)">
                      <span class="slider"></span>
                    </label>
                  </td>
                  <td>{{ $item->account_number }}</td>
                  <td>{{ $item->ifsc_code }}</td>
                  <td>{{ $item->account_name }}</td>
                  <td>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#updateModal{{$item->id}}">
                      <i class="fa fa-edit"></i> Update
                    </button>
                  </td>
                </tr>

                <!-- QR Preview Modal -->
                <div class="modal fade" id="qrPreview{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="qrPreviewTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">QR Code Preview</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span>&times;</span>
                        </button>
                      </div>
                      <div class="modal-body text-center">
                        <img src="{{ $item->qr_code }}" class="modal-img" alt="QR Code">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Update QR Modal -->
                <div class="modal fade" id="updateModal{{$item->id}}" tabindex="-1" role="dialog" aria-labelledby="updateModalTitle" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Update QR</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span>&times;</span>
                        </button>
                      </div>
                      <form action="{{ route('manual_qr.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                          <div class="form-group">
                            <label for="image">New QR Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                          </div>
                          <div class="form-group">
                            <label for="wallet_address">Wallet Address</label>
                            <input type="text" class="form-control" name="wallet_address" value="{{ $item->wallet_address }}" required>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-success">Update</button>
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
</div>

<script>
// ✅ Toggle Status (AJAX)
function toggleStatus(id, checkbox) {
  const status = checkbox.checked ? 1 : 0;
  fetch(`/manual_qr/status/${id}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}'
    },
    body: JSON.stringify({ status })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert('Status updated successfully!');
    } else {
      alert('Failed to update status.');
      checkbox.checked = !checkbox.checked;
    }
  })
  .catch(() => {
    alert('Something went wrong.');
    checkbox.checked = !checkbox.checked;
  });
}
</script>
@endsection
