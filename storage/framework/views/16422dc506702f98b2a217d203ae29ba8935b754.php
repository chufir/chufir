
<?php $__env->startPush('css'); ?>
    <link href="<?php echo e(asset('module/booking/css/checkout.css?_ver='.config('app.asset_version'))); ?>" rel="stylesheet">
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="bravo-booking-page padding-content padding-content pt-40 pb-40" >
        <div class="container">
            <div class="row booking-success-notice">
                <div class="col-lg-8 col-md-8">
                    <div class="d-flex align-items-center">
                        <img src="<?php echo e(url('images/ico_success.svg')); ?>" alt="Payment Success">
                        <div class="notice-success">
                            <p class="line1"><span><?php echo e($booking->first_name); ?>,</span>
                                <?php echo e(__('your booking was submitted successfully!')); ?>

                            </p>
                            <p class="line2"><?php echo e(__('Booking details has been sent to:')); ?> <span><?php echo e($booking->email); ?></span></p>
                            <?php if(!empty($gateway)): ?>
                                <?php if($note = $gateway->getOption("payment_note")): ?>
                                    <div class="line2"><?php echo clean($note); ?></div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <ul class="booking-info-detail ps-sm-0 ps-md-3">
                        <li><span><?php echo e(__('Booking Number')); ?>:</span> <?php echo e($booking->id); ?></li>
                        <li><span><?php echo e(__('Booking Date')); ?>:</span> <?php echo e(display_date($booking->created_at)); ?></li>
                        <?php if(!empty($gateway)): ?>
                        <li><span><?php echo e(__('Payment Method')); ?>:</span> <?php echo e($gateway->name); ?></li>
                        <?php endif; ?>
                        <li><span><?php echo e(__('Booking Status')); ?>:</span> <?php echo e($booking->status_name); ?></li>
                    </ul>
                </div>
            </div>
            <div class="row booking-success-detail">
                <div class="col-md-8">
                    <?php echo $__env->make($service->booking_customer_info_file ?? 'Booking::frontend/booking/booking-customer-info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <div class="d-flex justify-content-center pt-sm-4 pt-md-0">
                        <div class="col-auto"></div>
                        <div class="col-auto">
                            <a href="<?php echo e(route('user.booking_history')); ?>" class="button h-60 px-24 -dark-1 bg-blue-1 text-white">
                                <?php echo e(__('Booking History')); ?>

                                <div class="icon-arrow-top-right ml-15"></div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php echo $__env->make($service->checkout_booking_detail_file ?? '',['disable_lazyload'=>1], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\chufir\themes/GoTrip/Booking/Views/frontend/detail.blade.php ENDPATH**/ ?>