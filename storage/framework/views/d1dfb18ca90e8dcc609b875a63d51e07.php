<?php  
  $loggedInUser = DB::table('users')->where('id', 1)->first();
  
  
   $permissionsJson = $loggedInUser->permissions ?? '[]';  
   $permissionsArray = json_decode($permissionsJson, true) ?? [];
   $permissionsArray = array_map('intval', $permissionsArray);
   $role_id = $loggedInUser->role_id ?? null;

?>


<?php $__env->startSection('admin'); ?>

<?php if(in_array(1, $permissionsArray)): ?>

<!-- dashboard inner -->
<div class="midde_cont">
   <div class="container-fluid">
      <div class="row column_title">
         <div class="col-md-12">
            <div class="page_title">
               <h2>Dashboard</h2>
            </div>
         </div>
      </div>
      
      <div class="row">
         <div class="col-md-3 form-group">
            <form action="<?php echo e(route('dashboard')); ?>" method="get">
               <?php echo csrf_field(); ?>
               <div class="form-group">
                  <label for="start_date">Start Date:</label>
                  <input type="date" class="form-control" id="start_date" name="start_date">
               </div>
         </div>
         <div class="col-md-3 form-group">
            <div class="form-group">
               <label for="end_date">End Date:</label>
               <input type="date" class="form-control" id="end_date" name="end_date">
            </div>
         </div>
         <div class="col-md-2 form-group mt-4">
            <button type="submit" class="btn btn-success">Search</button>
            <a href="https://root.winzy.app/dashboard" class=" btn btn-secondary">Reset</a>
         </div>
         </form>
      </div>
      
      <div class="row column1">
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_">
               <div class="couter_icon">
                  <div><i class="fa fa-users yellow_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->totaluser); ?></p>
                     <p class="head_couter">Total Users</p>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_">
               <div class="couter_icon">
                  <div><i class="fa fa-user-check green_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->activeuser); ?></p>
                     <p class="head_couter">Active Users</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-user-plus blue1_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->todayuser); ?></p>
                     <p class="head_couter">Today User</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-chart-line red_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->todayturnover); ?></p>
                     <p class="head_couter">Today Turnover</p>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="row column1">
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-chart-pie green_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->total_turnover); ?></p>
                     <p class="head_couter">Total Turnover</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-wallet blue1_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->totaldeposit); ?></p>
                     <p class="head_couter">Total Deposit</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-coins green_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->tdeposit); ?></p>
                     <p class="head_couter">Today Deposit</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-hand-holding-usd red_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->totalwithdraw); ?></p>
                     <p class="head_couter">Total Withdrawal</p>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="row column1">
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-money-bill-wave blue1_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->tamount); ?></p>
                     <p class="head_couter">Today Withdrawal</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-comments yellow_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->totalfeedback); ?></p>
                     <p class="head_couter">Feedback</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-gamepad blue1_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                    <!-- <p class="total_no"><?php echo e($users[0]->totalgames); ?></p>-->
					  <p class="total_no">3</p>

                     <p class="head_couter">Total Games</p>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-md-6 col-lg-3">
            <div class="full counter_section margin_bottom_30">
               <div class="couter_icon">
                  <div><i class="fa fa-percentage green_color"></i></div>
               </div>
               <div class="counter_no">
                  <div>
                     <p class="total_no"><?php echo e($users[0]->commissions); ?></p>
                     <p class="head_couter">Total Commission</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

 <?php else: ?>
       <div class="text-center">Not allowed to view dashboard</div>
   <?php endif; ?>
<!-- end dashboard inner -->
<style>
	.counter_section {
    color: #fff;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s, box-shadow 0.3s;
    font-weight: bold;
    font-size: 18px;
}

/* Hover Effect */
.counter_section:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

/* Gradient Colors for Different Cards */
.counter_section.blue-gradient {
    background: linear-gradient(135deg, #06b6d4, #3b82f6); /* Cyan to Blue */
}

.counter_section.green-gradient {
    background: linear-gradient(135deg, #4ade80, #22c55e); /* Light Green */
}

.counter_section.orange-gradient {
    background: linear-gradient(135deg, #fb923c, #f97316); /* Orange */
}

.counter_section.red-gradient {
    background: linear-gradient(135deg, #ef4444, #b91c1c); /* Red */
}

.counter_section.purple-gradient {
    background: linear-gradient(135deg, #a855f7, #7e22ce); /* Purple */
}

.counter_section.yellow-gradient {
    background: linear-gradient(135deg, #facc15, #f59e0b); /* Yellow-Orange */
}

</style>



<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.body.adminmaster', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/root.winbhai.in/resources/views/admin/index.blade.php ENDPATH**/ ?>