
<?php $__env->startSection('content'); ?>
    <div class="row y-gap-20 justify-between items-end pb-60 lg:pb-40 md:pb-32">
        <div class="col-auto">
            <h1 class="text-30 lh-14 fw-600"><?php echo e(__("Booking History")); ?></h1>
            <div class="text-15 text-light-1"><?php echo e(__("Lorem ipsum dolor sit amet, consectetur.")); ?></div>
        </div>
        <div class="col-auto"></div>
    </div>
    <?php echo $__env->make('admin.message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="py-30 px-30 rounded-4 bg-white shadow-3 booking-history-manager">
        <div class="tabs -underline-2 js-tabs">
            <div class="tabs__controls row x-gap-40 y-gap-10 lg:x-gap-20 js-tabs-controls">
                <?php $status_type = Request::query('status'); ?>
                <div class="col-auto">
                    <a href="<?php echo e(route("user.booking_history")); ?>" class="tabs__button text-18 lg:text-16 text-light-1 fw-500 pb-5 lg:pb-0 <?php if(empty($status_type)): ?> is-tab-el-active <?php endif; ?>">
                        <?php echo e(__("All Booking")); ?>

                    </a>
                </div>
                <?php if(!empty($statues)): ?>
                    <?php $__currentLoopData = $statues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-auto">
                            <a href="<?php echo e(route("user.booking_history",['status'=>$status])); ?>" class="tabs__button text-18 lg:text-16 text-light-1 fw-500 pb-5 lg:pb-0 <?php if(!empty($status_type) && $status_type == $status): ?> is-tab-el-active <?php endif; ?>" >
                                <?php echo e(booking_status_to_text($status)); ?>

                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            </div>
            <div class="tabs__content pt-30 js-tabs-content">
                <div class="tabs__pane -tab-item-1 is-tab-el-active">
                    <div class="overflow-scroll scroll-bar-1">
                        <?php if(!empty($bookings) and $bookings->total() > 0): ?>
                            <table class="table-3 -border-bottom col-12">
                                <thead class="bg-light-2">
                                    <tr>
                                        <th width="2%"><?php echo e(__("Type")); ?></th>
                                        <th><?php echo e(__("Title")); ?></th>
                                        <th class="a-hidden"><?php echo e(__("Order Date")); ?></th>
                                        <th class="a-hidden"><?php echo e(__("Execution Time")); ?></th>
                                        <th><?php echo e(__("Total")); ?></th>
                                        <th><?php echo e(__("Paid")); ?></th>
                                        <th><?php echo e(__("Remain")); ?></th>
                                        <th class="a-hidden"><?php echo e(__("Status")); ?></th>
                                        <th><?php echo e(__("Action")); ?></th>
                                    </tr>
                                </thead>
                                <div class="tbody">
                                    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php echo $__env->make(ucfirst($booking->object_model).'::frontend.bookingHistory.loop', ['key' => $key], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                            <div class="bravo-pagination pt-30">
                                <?php echo e($bookings->appends(request()->query())->links()); ?>

                            </div>
                        <?php else: ?>
                            <?php echo e(__("No Booking History")); ?>

                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('js'); ?>
    <script>
        jQuery(function ($){
            $('#modal_booking_detail').on('show.bs.modal',function (e){
                var btn = $(e.relatedTarget);
                $(this).find('.user_id').html(btn.data('id'));
                $(this).find('.modal-body').html('<div class="d-flex justify-content-center"><?php echo e(__("Loading...")); ?></div>');
                var modal = $(this);
                $.get(btn.data('ajax'), function (html){
                        modal.find('.modal-body').html(html);
                    }
                )
            })
        })
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.user', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\chufir\themes/GoTrip/User/Views/frontend/bookingHistory.blade.php ENDPATH**/ ?>