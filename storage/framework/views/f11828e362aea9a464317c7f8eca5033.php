<?php $__env->startSection('admin'); ?>

<div class="container-fluid py-3">

  <div class="row">
    <div class="col-md-12">

      
      <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
          <h4 class="mb-0">Settings List</h4>
        </div>

        <div class="card-body">

            <h5 class="fw-bold text-secondary mb-3">Need Help? Chat With Us</h5>

            <div class="table-responsive">
              <table id="example" class="table table-striped table-bordered align-middle text-center">
                <thead class="table-dark">
                  <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Social Media</th>
                    <th style="width: 90px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                    <td><?php echo e($item->id); ?></td>
                    <td><?php echo e($item->name); ?></td>
                    <td><?php echo e($item->link); ?></td>
                    <td>
                      <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#socialEditModal<?php echo e($item->id); ?>">
                        <i class="fa fa-edit"></i>
                      </button>
                    </td>
                  </tr>

                  
                  <div class="modal fade" id="socialEditModal<?php echo e($item->id); ?>">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <form action="<?php echo e(route('supportsetting.update',$item->id)); ?>" method="POST">
                          <?php echo csrf_field(); ?>

                          <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Edit Social Media</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                          </div>

                          <div class="modal-body">
                            <input type="text" name="socialmedia" class="form-control"
                                   value="<?php echo e($item->link); ?>" placeholder="Enter Social Media Link">
                          </div>

                          <div class="modal-footer">
                            <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button class="btn btn-primary">Update</button>
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



      
      <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white">
          <h4 class="mb-0">Contact Us</h4>
        </div>

        <div class="card-body">
          <table class="table table-bordered table-striped text-center">
            <tr>
              <th>Contact Number</th>
              <th style="width: 90px">Action</th>
            </tr>
            <tr>
              <td class="fw-bold fs-5"><?php echo e($contactUs->contact ?? 'N/A'); ?></td>
              <td>
                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#contactUsModal">
                  <i class="fa fa-edit"></i>
                </button>
              </td>
            </tr>
          </table>
        </div>
      </div>


      
      <div class="modal fade" id="contactUsModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <form action="<?php echo e(route('contact.us.update',$contactUs->id)); ?>" method="POST">
              <?php echo csrf_field(); ?>

              <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Update Contact Number</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>

              <div class="modal-body">
                <input type="text" name="contact" class="form-control form-control-lg"
                       value="<?php echo e($contactUs->contact); ?>" placeholder="Enter Contact Number">
              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn btn-primary">Update</button>
              </div>

            </form>

          </div>
        </div>
      </div>




      
      <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
          <h4 class="mb-0">Need Help? Chat With Us</h4>
        </div>

        <div class="card-body">

          <table class="table table-bordered text-center">
            <tr>
              <th class="fs-5">Whatsapp Number</th>
              <th style="width: 90px">Action</th>
            </tr>

            <tr>
              <td class="fw-bold fs-5 text-success"><?php echo e($Chat_With_Us_view->longtext ?? 'N/A'); ?></td>
              <td>
                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#Chat_With_Us_edit">
                  <i class="fa fa-edit"></i>
                </button>
              </td>
            </tr>
          </table>

        </div>
      </div>


      
      <div class="modal fade" id="Chat_With_Us_edit">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <form action="<?php echo e(route('needhelp.chat.update', $Chat_With_Us_view->id)); ?>" method="POST">
              <?php echo csrf_field(); ?>

              <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Update Whatsapp Number</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>

              <div class="modal-body">
                <input type="text" name="chat_on_whatsapp" class="form-control form-control-lg"
                       value="<?php echo e($Chat_With_Us_view->longtext); ?>" placeholder="Enter Whatsapp Number">
              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn btn-success">Update</button>
              </div>

            </form>

          </div>
        </div>
      </div>




      
      <div class="card shadow">
        <div class="card-header bg-info text-white">
          <h4 class="mb-0">Contact With Us</h4>
        </div>

        <div class="card-body">

          <table class="table table-bordered text-center align-middle">
            <thead class="table-dark">
              <tr>
                <th>Instagram</th>
                <th>Telegram</th>
                <th>WhatsApp</th>
                <th style="width: 90px">Action</th>
              </tr>
            </thead>
            <tr>
              <td><?php echo e($contactWithUs->instagram_link); ?></td>
              <td><?php echo e($contactWithUs->telegram_link); ?></td>
              <td><?php echo e($contactWithUs->whatsapp_link); ?></td>
              <td>
                <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#contactWithUsModal">
                  <i class="fa fa-edit"></i>
                </button>
              </td>
            </tr>
          </table>

        </div>
      </div>


      
      <div class="modal fade" id="contactWithUsModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">

            <form action="<?php echo e(route('contact.with.us.update',$contactWithUs->id)); ?>" method="POST">
              <?php echo csrf_field(); ?>

              <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Update Social Links</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>

              <div class="modal-body">

                <div class="mb-3">
                  <label>Instagram Username</label>
                  <input type="text" name="instagram_username" class="form-control"
                         value="<?php echo e(str_replace('https://www.instagram.com/','',$contactWithUs->instagram_link)); ?>">
                </div>

                <div class="mb-3">
                  <label>Telegram Username</label>
                  <input type="text" name="telegram_username" class="form-control"
                         value="<?php echo e(str_replace('https://t.me/','',$contactWithUs->telegram_link)); ?>">
                </div>

                <div class="mb-3">
                  <label>Whatsapp Number</label>
                  <input type="text" name="whatsapp_number" class="form-control"
                         value="<?php echo e(str_replace('https://wa.me/','',$contactWithUs->whatsapp_link)); ?>">
                </div>

              </div>

              <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button class="btn btn-info">Update</button>
              </div>

            </form>

          </div>
        </div>
      </div>


    </div>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/root.winbhai.in/resources/views/work_order_assign/support_setting.blade.php ENDPATH**/ ?>