@extends('admin.body.adminmaster')

@section('admin')
<div class="container-fluid mt-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📢 Manage Notices</h4>
            <!--<a href="#" class="btn btn-light btn-sm">+ Add New Notice</a>-->
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @elseif(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Image</th>
                            <!--<th>Type</th>-->
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notices as $notice)
                        <tr class="text-center">
                            <td>{{ $notice->id }}</td>
                            <td>{{ $notice->title }}</td>
                            <td>{{ Str::limit($notice->content, 50) }}</td>
                           <td>
                                <img src="{{ asset($notice->image) }}" width="70" height="70" class="rounded">
                            </td>

                            <!--<td>{{ ucfirst($notice->type) }}</td>-->
                            <td>
                                @if($notice->status == 1)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info editBtn"
                                    data-id="{{ $notice->id }}"
                                    data-title="{{ $notice->title }}"
                                    data-content="{{ $notice->content }}">
                                   
                                    Edit
                                </button>

                                <a href="{{ route('admin.sponser.toggle', $notice->id) }}" class="btn btn-sm btn-warning">
                                    {{ $notice->status ? 'Deactivate' : 'Activate' }}
                                </a>

                                <a href="{{ route('admin.sponser.delete', $notice->id) }}" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this notice?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 🧩 Edit Modal -->
<div class="modal fade" id="editNoticeModal" tabindex="-1" aria-labelledby="editNoticeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('admin.sponser.update') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" id="edit_id">

            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editNoticeLabel">Edit Notice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Content</label>
                        <textarea name="content" id="edit_content" class="form-control" required></textarea>
                    </div>

                    <!--<div class="mb-3">-->
                    <!--    <label>Type</label>-->
                    <!--    <input type="text" name="type" id="edit_type" class="form-control" required>-->
                    <!--</div>-->

                    <div class="mb-3">
                        <label>Image (optional)</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Update</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 🧠 Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const editButtons = document.querySelectorAll(".editBtn");

        editButtons.forEach(btn => {
            btn.addEventListener("click", function () {
                // Fill modal with current notice data
                document.getElementById("edit_id").value = this.dataset.id;
                document.getElementById("edit_title").value = this.dataset.title;
                document.getElementById("edit_content").value = this.dataset.content;
                // document.getElementById("edit_type").value = this.dataset.type;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById("editNoticeModal"));
                modal.show();
            });
        });
    });
</script>
@endsection
