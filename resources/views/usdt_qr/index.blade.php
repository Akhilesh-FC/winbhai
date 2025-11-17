@extends('admin.body.adminmaster')

@section('admin')
<div class="container-fluid mt-4">
    <div class="card shadow-lg border-0">
       <div class="card-header text-white d-flex justify-content-between align-items-center"
     style="background: linear-gradient(90deg, #6cb2eb 0%, #3490dc 100%);">
    <h5 class="mb-0">USDT Withdraw Conversion Rate</h5>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm">← Back</a>
</div>


        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <form action="{{ route('usdt_conversion.update') }}" method="POST" class="mt-3">
                @csrf
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Conversion Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $conversion->name }}" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Conversion Rate (Amount)</label>
                        <div class="input-group">
                            <input type="text" name="amount" class="form-control" value="{{ $conversion->amount }}" required>
                            <div class="input-group-append">
                                <span class="input-group-text bg-success text-white fw-bold">USDT</span>
                            </div>
                        </div>
                        @error('amount')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa fa-save me-2"></i> Update Rate
                    </button>
                </div>
            </form>
        </div>

        <div class="card-footer text-muted text-center small">
            Last Updated: {{ \Carbon\Carbon::parse($conversion->updated_at)->format('d M Y, h:i A') }}
        </div>
    </div>
</div>
@endsection
