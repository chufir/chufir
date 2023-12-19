
<?php $__env->startSection('content'); ?>
    <div class="row y-gap-20 justify-between items-end pb-60 lg:pb-40 md:pb-32">
        <div class="col-auto">
            <h1 class="text-30 lh-14 fw-600"> <?php echo e(__("Wallet")); ?></h1>
            <div class="text-15 text-light-1"><?php echo e(__('Lorem ipsum dolor sit amet, consectetur.')); ?></div>
        </div>
        <div class="col-auto">
            <a href="<?php echo e(route('user.wallet.buy')); ?>" class="button h-50 px-24 -dark-1 bg-blue-1 text-white"><?php echo e(__("Buy credits")); ?></a>
        </div>
    </div>
    <?php echo $__env->make('admin.message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="bravo-user-dashboard">
        <div class="row dashboard-price-info row-eq-height mb-5">
            <div class="col-lg-3 col-md-3">
                <div class="dashboard-item">
                    <div class="wrap-box">
                        <div class="title">
                            <?php echo e("Credit balance"); ?>

                        </div>
                        <div class="details">
                            <div class="number"><?php echo e(__(':amount',['amount'=>$row->balance])); ?></div>
                        </div>
                        <?php if($row->balance): ?>
                        <div class="desc">~ <?php echo e(format_money(credit_to_money($row->balance))); ?> </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel-title"><strong ><?php echo e(__("Latest Transactions")); ?></strong></div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col"><?php echo e(__('Type')); ?></th>
                            <th scope="col"><?php echo e(__('Amount')); ?></th>
                            <th scope="col"><?php echo e(__('Gateway')); ?></th>
                            <th scope="col"><?php echo e(__('Status')); ?></th>
                            <th scope="col"><?php echo e(__("Description")); ?></th>
                            <th scope="col"><?php echo e(__("Date")); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php if(count($transactions)): ?>
                                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($transaction->id); ?></td>
                                        <td><?php echo e(ucfirst($transaction->type)); ?></td>
                                        <td><?php echo e($transaction->amount); ?></td>
                                        <td>
                                            <?php if($transaction->payment->gateway_obj): ?>
                                                <?php echo e($transaction->payment->gateway_obj->getDisplayName() ?? ''); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge badge-<?php echo e($transaction->status_class); ?>"><?php echo e($transaction->status_name ?? ''); ?></span></td>
                                        <td>
                                            <?php if(!empty($transaction->meta['admin_deposit'])): ?>
                                                <?php echo e(__("Deposit by :name",['name'=>$transaction->author->display_name ?? ''])); ?>

                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e(display_datetime($transaction->created_at)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <tr><td colspan="5"><?php echo e(__("No data found")); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                        <?php echo e($transactions->links()); ?>

                    </table>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\chufir\themes/GoTrip/User/Views/frontend/wallet/index.blade.php ENDPATH**/ ?>