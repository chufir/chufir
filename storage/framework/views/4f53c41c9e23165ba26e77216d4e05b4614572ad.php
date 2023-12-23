<?php if($row->getGallery()): ?>
    <?php echo $__env->make('Layout::common.detail.gallery5',['galleries' => $row->getGallery()], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>
<div class="row x-gap-80 y-gap-40 pt-40">
    <div class="col-12 gotrip-overview">
        <h3 class="text-22 fw-500"><?php echo e(__("Overview")); ?></h3>
        <div class="text-dark-1 text-15 mt-20">
            <?php echo clean($translation->content); ?>

        </div>
    </div>
    <?php echo $__env->make('Boat::frontend.layouts.details.specs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->make('Boat::frontend.layouts.details.attributes', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<?php /**PATH C:\wamp64\www\chufir\themes/GoTrip/Boat/Views/frontend/layouts/details/detail.blade.php ENDPATH**/ ?>