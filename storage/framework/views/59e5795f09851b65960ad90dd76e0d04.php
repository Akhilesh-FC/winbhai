<?php $__env->startSection('admin'); ?>
<div class="container-fluid mt-4">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">📢 Manage Notices</h4>
            <!--<a href="#" class="btn btn-light btn-sm">+ Add New Notice</a>-->
        </div>

        <div class="card-body">
            <?php if(session('success')): ?>
                <div class="alert alert-success"><?php echo e(session('success')); ?></div>
            <?php elseif(session('error')): ?>
                <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
            <?php endif; ?>

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
                        <?php $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="text-center">
                            <td><?php echo e($notice->id); ?></td>
                            <td><?php echo e($notice->title); ?></td>
                            <td><?php echo e(Str::limit($notice->content, 50)); ?></td>
                           <td>
                                <img src="<?php echo e(asset($notice->image)); ?>" width="70" height="70" class="rounded">
                            </td>

                            <!--<td><?php echo e(ucfirst($notice->type)); ?></td>-->
                            <td>
                                <?php if($notice->status == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info editBtn"
                                    data-id="<?php echo e($notice->id); ?>"
                                    data-title="<?php echo e($notice->title); ?>"
                                    data-content="<?php echo e($notice->content); ?>">
                                   
                                    Edit
                                </button>

                                <a href="<?php echo e(route('admin.sponser.toggle', $notice->id)); ?>" class="btn btn-sm btn-warning">
                                    <?php echo e($notice->status ? 'Deactivate' : 'Activate'); ?>

                                </a>

                                <a href="<?php echo e(route('admin.sponser.delete', $notice->id)); ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this notice?')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- 🧩 Edit Modal -->
<div class="modal fade" id="editNoticeModal" tabindex="-1" aria-labelledby="editNoticeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="<?php echo e(route('admin.sponser.update')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/root.winbhai.in/resources/views/sponser/index.blade.php ENDPATH**/ ?>