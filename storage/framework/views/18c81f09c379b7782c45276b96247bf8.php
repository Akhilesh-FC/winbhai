<?php $__env->startSection('admin'); ?>

<style>
    body {
        background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
        min-height: 100vh;
    }

    .white_shd {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .graph_head {
        background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
        padding: 20px;
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
    }

    .heading1 h2 {
        margin: 0;
        font-weight: bold;
    }

    .table_section {
        padding: 20px;
    }

    table th, table td {
        white-space: nowrap;
        text-overflow: ellipsis;
        vertical-align: middle;
    }

    table th {
        background: #343a40;
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    table tbody tr:hover {
        background: #f1f1f1;
        cursor: pointer;
        box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
    }

    .btn-group .btn {
        margin-right: 5px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }

    .modal-header {
        background: linear-gradient(135deg, #43cea2 0%, #185a9d 100%);
        color: white;
    }

    .table-responsive-sm {
        max-height: 70vh;
        overflow-y: auto;
    }

    .fa-edit, .fa-trash, .fa-eye {
        cursor: pointer;
        transition: transform 0.2s;
    }

    .fa-edit:hover, .fa-trash:hover, .fa-eye:hover {
        transform: scale(1.2);
    }
    
    
    
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
           <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
        <?php echo e(session('success')); ?>

    </div>

    <script>
        setTimeout(function() {
            $('#success-alert').fadeOut('slow');
        }, 2000); // 3 seconds
    </script>
<?php endif; ?>

            <div class="white_shd full margin_bottom_30">
                <div class="full graph_head d-flex justify-content-between align-items-center">
                    <h2>User List</h2>
                </div>
                
                <!--<button class="btn btn-success" data-toggle="modal" data-target="#createPlayerModal">-->
                <!--     <i class="fa fa-user-plus"></i> Add Player-->
                <!-- </button>-->
                <div class="table_section padding_infor_info">
                    <div class="table-responsive-sm">
                        <table id="example" class="table table-striped" style="width:100%">
                            <thead class="thead">
                                <tr>
                                    <th>Id</th>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Sponsor</th>
                                    <th>Sponsor ID</th>
                                    <th>Wallet</th>
                                    <th>Winning Wallet</th>
                                    <th>Commission</th>
                                    <th>Bonus</th>
                                    <th>Turnover</th>
                                    <th>Today Turnover</th>
                                    <th>Password</th>
                                    <th>Date</th>
                                    <th>Last Login Time</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($item->id); ?></td>
                                        <td><?php echo e($item->u_id); ?></td>
                                        <td><?php echo e($item->username); ?></td>
                                        <td><?php echo e($item->email); ?></td>
                                        <td><?php echo e($item->mobile); ?></td>
                                        <td><?php echo e($item->sname); ?></td>
                                        <td>
                                            <?php echo e($item->referral_user_id); ?>

                                            <i class="fa fa-edit ml-2" data-toggle="modal" data-target="#editReferralModal<?php echo e($item->id); ?>"></i>
                                        </td>
                                      <td>
                                            <div class="d-flex justify-content-between align-items-center" style="min-width: 160px;">
                                                <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?php echo e($item->wallet); ?>

                                                </span>
                                                <div class="btn-group ml-2">
                                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#addWalletModal<?php echo e($item->id); ?>">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                        			 
                                               
                                                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#subtractWalletModal<?php echo e($item->id); ?>">
                                                        <i class="fa fa-minus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>

                                        <td><?php echo e($item->winning_wallet); ?></td>
                                        <td><?php echo e($item->commission); ?></td>
                                        <td><?php echo e($item->bonus); ?></td>
                                        <td><?php echo e($item->turnover); ?></td>
                                        <td><?php echo e($item->today_turnover); ?></td>
                                        <td>
                                            <?php echo e($item->password); ?>

                                            <i class="fa fa-edit ml-2" data-toggle="modal" data-target="#editPasswordModal<?php echo e($item->id); ?>"></i>
                                        </td>
                                        <td><?php echo e($item->created_at); ?></td>
                                        <td><?php echo e($item->updated_at); ?></td>
                                        <td>
                                            <?php if($item->status == 1): ?>
                                                <a href="<?php echo e(route('user.inactive', $item->id)); ?>"><i class="fa fa-check-square-o green_color" style="font-size:25px"></i></a>
                                            <?php else: ?>
                                                <a href="<?php echo e(route('user.active', $item->id)); ?>"><i class="fa fa-ban red_color" style="font-size:25px"></i></a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo e(route('userdetail', $item->id)); ?>"><i class="fa fa-eye" style="font-size:25px"></i></a>
                                        </td>
                                        
                                        <td style="max-width:250px; white-space: normal; word-break: break-word;">
                                            <?php echo e($item->remark ?? '—'); ?>

                                            <i class="fa fa-edit ml-2" data-toggle="modal" data-target="#editRemarkModal<?php echo e($item->id); ?>"></i>
                                        </td>

                                        
                                    </tr>
                                
                                    <!-- ✅ Create Player Modal -->
                                    <div class="modal fade" id="createPlayerModal" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Create Player</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                    
                                                <form action="<?php echo e(route('player.store')); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-body">
                                                        <!-- Username -->
                                                        <div class="form-group">
                                                            <label>Username</label>
                                                            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                                                        </div>
                                    
                                                        <!-- Password -->
                                                        <div class="form-group">
                                                            <label>Password</label>
                                                            <input type="text" name="password" class="form-control" placeholder="Enter password" required>
                                                        </div>
                                    
                                                        <!-- Full Name -->
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input type="text" name="name" class="form-control" placeholder="Enter full name" required>
                                                        </div>
                                    
                                                        <!-- Role (session se milega) -->
                                                        <!--<div class="form-group">-->
                                                        <!--    <label>Role</label>-->
                                                        <!--    <select name="role_type" class="form-control" required>-->
                                                        <!--       // <option value="admin" <?php echo e(session('role_id') == 1 ? 'selected' : ''); ?>>Admin</option>-->
                                                        <!--       // <option value="agent" <?php echo e(session('role_id') == 2 ? 'selected' : ''); ?>>Agent</option>-->
                                                        <!--    </select>-->
                                                        <!--</div>-->
                                                    </div>
                                    
                                                    <div class="modal-footer">
                                                        <button class="btn btn-primary" type="submit">Create</button>
                                                    </div>
                                                </form>
                                            </div>
        </div>
                                    </div>
                                    <!-- Referral Modal -->
                                    <div class="modal fade" id="editReferralModal<?php echo e($item->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Edit Sponsor ID</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="<?php echo e(route('referral.update', $item->id)); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-body">
                                                        <input type="text" name="referral_user_id" class="form-control" value="<?php echo e($item->referral_user_id); ?>">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-primary" type="submit">Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Add Wallet Modal -->
                                    <div class="modal fade" id="addWalletModal<?php echo e($item->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Add Wallet</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="<?php echo e(route('wallet.store', $item->id)); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-body">
                                                        <input type="text" name="wallet" class="form-control" placeholder="Enter amount">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-primary" type="submit">Add</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Subtract Wallet Modal -->
                                    <div class="modal fade" id="subtractWalletModal<?php echo e($item->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Subtract Wallet</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="<?php echo e(route('wallet.subtract', $item->id)); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-body">
                                                        <input type="text" name="wallet" class="form-control" placeholder="Enter amount">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-danger" type="submit">Subtract</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Password Modal -->
                                    <div class="modal fade" id="editPasswordModal<?php echo e($item->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Change Password</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form action="<?php echo e(route('password.update', $item->id)); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-body">
                                                        <input type="text" name="password" class="form-control" value="<?php echo e($item->password); ?>" placeholder="Enter new password">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button class="btn btn-primary" type="submit">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!--Remark add section-->
                                   <!-- ✅ Edit Remark Modal -->
                                    <div class="modal fade" id="editRemarkModal<?php echo e($item->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5>Edit Remark</h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                    
                                                <form action="<?php echo e(route('remark.update', $item->id)); ?>" method="POST">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="modal-body">
                                                        <textarea name="remark" class="form-control" rows="4"
                                                            placeholder="Enter remark"><?php echo e($item->remark); ?></textarea>
                                                    </div>
                                    
                                                    <div class="modal-footer">
                                                        <button class="btn btn-primary" type="submit">Save Remark</button>
                                                    </div>
                                                </form>
                                    
                                            </div>
                                        </div>
                                    </div>

                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#myModal').on('shown.bs.modal', function () {
        $('#myInputs').trigger('focus')
    })
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/root.winbhai.in/resources/views/user/index.blade.php ENDPATH**/ ?>