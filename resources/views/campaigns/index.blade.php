@extends('admin.body.adminmaster')

@section('admin')
<div class="container mt-4">
    <h3 class="mb-4 text-center">📋 Campaigns List</h3>

    <div class="card shadow-sm p-3">
        {{-- 🔍 Search Form --}}
        <form method="GET" action="{{ route('campaign.list') }}" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by campaign name, code, link, or user ID"
                       value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">Search</button>
                <a href="{{ route('campaign.list') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>User ID</th>
                        <th>Campaign Name</th>
                        <th>Unique Code</th>
                        <th>Referral Link</th>
                        <!--<th>Affiliation percentage</th>-->
                        
                       <th>Real Revenue</th>
<th>Fake Revenue</th>
<th>Action</th>



                        <th>No.of Players</th>
                        <th>Created By</th>
                        <th>Created By Mobile no.</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td class="text-center">{{ $campaign->id }}</td>
                            <td class="text-center">{{ $campaign->user_id }}</td>
                            <td>{{ $campaign->campaign_name }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $campaign->unique_code }}</span>
                            </td>
                            <td>
                                <a href="{{ $campaign->referral_link }}" target="_blank" class="text-decoration-none">
                                    {{ $campaign->referral_link }}
                                </a>
                            </td>
                           
                           
                          <td class="text-center text-success fw-bold">
    ₹{{ number_format($campaign->real_revenue ?? 0, 2) }}
</td>

<td class="text-center text-warning fw-bold">
    ₹{{ number_format($campaign->fake_revenue ?? 0, 2) }}
</td>

<td class="text-center">
    <button 
        class="btn btn-sm btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#revenueModal"
        onclick="openRevenueModal(
            '{{ $campaign->user_id }}',
            '{{ $campaign->real_revenue ?? 0 }}',
            '{{ $campaign->fake_revenue ?? 0 }}'
        )">
        Update Revenue
    </button>
</td>


                           
                            <td class="text-center">{{ $campaign->players }}</td>
                            <td class="text-center">{{ $campaign->created_by }}</td>
                            <td class="text-center">{{ $campaign->created_by_mobile }}</td>

                            <td class="text-center">{{ \Carbon\Carbon::parse($campaign->created_at)->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-danger fw-bold">No campaigns found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 📄 Pagination --}}
        <div class="d-flex justify-content-center mt-3">
            {!! $campaigns->links('pagination::bootstrap-5') !!}
        </div>
    </div>
    
    
    <!-- Revenue Update Modal -->
<div class="modal fade" id="revenueModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('campaign.update.revenue') }}">
        @csrf
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Update Campaign Revenue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="user_id" id="modal_user_id">

                <div class="mb-3">
                    <label class="form-label">Real Revenue</label>
                    <input type="number" step="0.01" name="real_revenue"
                           id="modal_real_revenue"
                           class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fake Revenue</label>
                    <input type="number" step="0.01" name="fake_revenue"
                           id="modal_fake_revenue"
                           class="form-control" required>
                </div>

                <small class="text-danger">
                    ⚠ Ye update is user ke sab campaigns par apply hoga
                </small>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                    Update Revenue
                </button>
            </div>

        </div>
    </form>
  </div>
</div>

    
    
</div>

<script>
function openRevenueModal(userId, realRevenue, fakeRevenue) {
    document.getElementById('modal_user_id').value = userId;
    document.getElementById('modal_real_revenue').value = realRevenue;
    document.getElementById('modal_fake_revenue').value = fakeRevenue;
}
</script>

@endsection
