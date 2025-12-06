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
                        <th>Affiliation percentage</th>
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
                           <td class="text-center">{{ $campaign->affiliation_percentage }}</td>
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
</div>
@endsection
