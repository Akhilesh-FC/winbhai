@extends('admin.body.adminmaster')

@section('admin')

<div class="container py-4">
  <div class="card shadow-lg border-0 rounded-4">
    <div class="card-header bg-primary text-white text-center fs-4 fw-semibold">
      Pay Modes List
    </div>
    <div class="card-body">
      @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Image</th>
            <th>Status</th>
            <!--<th>Type</th>-->
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($payModes as $mode)
          <tr>
            <td>{{ $mode->id }}</td>
            <td>{{ $mode->name }}</td>
            <td>
              @if($mode->image)
                <img src="{{ $mode->image }}" alt="{{ $mode->name }}" width="60" class="rounded-3 border">
              @else
                <span class="text-muted">No Image</span>
              @endif
            </td>
            <td>
              @if($mode->status == 1)
                <span class="badge bg-success">Active</span>
              @else
                <span class="badge bg-danger">Inactive</span>
              @endif
            </td>
            <!--<td>{{ ucfirst($mode->type) }}</td>-->
            <td>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateImageModal{{ $mode->id }}">
                Update Image
              </button>
            </td>
          </tr>

          <!-- Modal -->
          <div class="modal fade" id="updateImageModal{{ $mode->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content rounded-4">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title">Update Image - {{ $mode->name }}</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('paymode_show.updateImage', $mode->id) }}" method="POST" enctype="multipart/form-data">
                  @csrf
                  <div class="modal-body text-center">
                    @if($mode->image)
                      <img src="{{ asset('uploads/paymodes/'.$mode->image) }}" width="100" class="rounded mb-3 border">
                    @endif
                    <input type="file" name="image" class="form-control mb-3" required>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          @endforeach
        </tbody>
      </table>
      
      
      <hr class="my-4">

<div class="card mt-4 border-0 shadow-sm rounded-4">
  <div class="card-header bg-success text-white text-center fs-5 fw-semibold">
    WhatsApp Deposit Number
  </div>

  <div class="card-body text-center">
    <h4 class="mb-3 text-dark">
      {{ $whatsappDeposit ?? 'Not Available' }}
    </h4>

    <button class="btn btn-outline-success"
            data-bs-toggle="modal"
            data-bs-target="#whatsappDepositModal">
      Update Number
    </button>
  </div>
</div>

<!-- ✅ Update Modal -->
<div class="modal fade" id="whatsappDepositModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Update WhatsApp Deposit Number</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="{{ route('update.whatsapp.deposit') }}" method="POST">
        @csrf
        <div class="modal-body">
          <input type="text"
                 name="whatsapp_number"
                 class="form-control"
                 value="{{ $whatsappDeposit }}"
                 required>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

      
      
    </div>
  </div>
</div>

@endsection
