<?php $__env->startSection('admin'); ?>
<div class="container mt-4">
    <h3 class="mb-4 text-center">💰 Payment Limits</h3>

    <div class="card shadow-sm p-3">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $paymentLimits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $limit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e($limit->id); ?></td>
                            <td><?php echo e($limit->name); ?></td>
                            <td class="text-end">₹<?php echo e(number_format($limit->amount, 2)); ?></td>
                            <td class="text-center">
                                <?php if($limit->status == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo e(\Carbon\Carbon::parse($limit->created_at)->format('d M Y')); ?></td>
                            <td class="text-center">
                                <button 
                                    class="btn btn-sm btn-primary editBtn"
                                    data-id="<?php echo e($limit->id); ?>"
                                    data-name="<?php echo e($limit->name); ?>"
                                    data-amount="<?php echo e($limit->amount); ?>"
                                >
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-danger fw-bold">No payment limits found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="d-flex justify-content-center mt-3">
            <?php echo $paymentLimits->links('pagination::bootstrap-5'); ?>

        </div>
    </div>
</div>

<!-- 🟩 Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="editModalLabel">Edit Payment Limit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateForm">
          <?php echo csrf_field(); ?>
          <input type="hidden" id="edit_id" name="id">

          <div class="mb-3">
            <label for="edit_name" class="form-label">Name</label>
            <input type="text" id="edit_name" class="form-control" readonly>
          </div>

          <div class="mb-3">
            <label for="edit_amount" class="form-label">Amount</label>
            <input type="number" id="edit_amount" name="amount" class="form-control" required min="0" step="0.01">
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-success">💾 Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- 🟩 Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // When Edit button is clicked
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_amount').value = this.dataset.amount;
            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });

    // When form is submitted
    document.getElementById('updateForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch("<?php echo e(route('payment.limit.update')); ?>", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "<?php echo e(csrf_token()); ?>",
            },
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (data.status) {
                alert(data.message);
                location.reload();
            } else {
                alert("Update failed!");
            }
        })
        .catch(err => console.error(err));
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/root.winbhai.in/resources/views/paymentLimitsList/index.blade.php ENDPATH**/ ?>