<div class="row">
    <?php if($rows->total() > 0): ?>
        <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-lg-12">
                <?php echo $__env->make('Flight::frontend.layouts.search.loop-grid',['wrap_class'=>'item-loop-wrap inner-loop-wrap'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <div class="col-lg-12">
            <?php echo e(__("Flight not found")); ?>

        </div>
    <?php endif; ?>
</div>

<div class="bravo-pagination">
    <?php echo e($rows->appends(request()->query())->links()); ?>

    <?php if($rows->total() > 0): ?>
        <div class="text-center mt-30 md:mt-10">
            <div class="text-14 text-light-1"><?php echo e(__("Showing :from - :to of :total flights",["from"=>$rows->firstItem(),"to"=>$rows->lastItem(),"total"=>$rows->total()])); ?></div>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\wamp64\www\chufir\themes/GoTrip/Flight/Views/frontend/ajax/search-result.blade.php ENDPATH**/ ?>