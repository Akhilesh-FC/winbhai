<?php $__env->startSection('admin'); ?>
<div class="container mt-4">
    <h3 class="mb-4 text-center">📋 Campaigns List</h3>

    <div class="card shadow-sm p-3">
        
        <form method="GET" action="<?php echo e(route('campaign.list')); ?>" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by campaign name, code, link, or user ID"
                       value="<?php echo e(request('search')); ?>">
                <button class="btn btn-primary" type="submit">Search</button>
                <a href="<?php echo e(route('campaign.list')); ?>" class="btn btn-secondary">Reset</a>
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
                    <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="text-center"><?php echo e($campaign->id); ?></td>
                            <td class="text-center"><?php echo e($campaign->user_id); ?></td>
                            <td><?php echo e($campaign->campaign_name); ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?php echo e($campaign->unique_code); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo e($campaign->referral_link); ?>" target="_blank" class="text-decoration-none">
                                    <?php echo e($campaign->referral_link); ?>

                                </a>
                            </td>
                           
                           
                          <td class="text-center text-success fw-bold">
    ₹<?php echo e(number_format($campaign->real_revenue ?? 0, 2)); ?>

</td>

<td class="text-center text-warning fw-bold">
    ₹<?php echo e(number_format($campaign->fake_revenue ?? 0, 2)); ?>

</td>

<td class="text-center">
    <button 
        class="btn btn-sm btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#revenueModal"
        onclick="openRevenueModal(
            '<?php echo e($campaign->user_id); ?>',
            '<?php echo e($campaign->real_revenue ?? 0); ?>',
            '<?php echo e($campaign->fake_revenue ?? 0); ?>'
        )">
        Update Revenue
    </button>
</td>


                           
                            <td class="text-center"><?php echo e($campaign->players); ?></td>
                            <td class="text-center"><?php echo e($campaign->created_by); ?></td>
                            <td class="text-center"><?php echo e($campaign->created_by_mobile); ?></td>

                            <td class="text-center"><?php echo e(\Carbon\Carbon::parse($campaign->created_at)->format('d M Y')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="text-center text-danger fw-bold">No campaigns found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <div class="d-flex justify-content-center mt-3">
            <?php echo $campaigns->links('pagination::bootstrap-5'); ?>

        </div>
    </div>
    
    
    <!-- Revenue Update Modal -->
<div class="modal fade" id="revenueModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="<?php echo e(route('campaign.update.revenue')); ?>">
        <?php echo csrf_field(); ?>
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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/root.winbhai.in/resources/views/campaigns/index.blade.php ENDPATH**/ ?>