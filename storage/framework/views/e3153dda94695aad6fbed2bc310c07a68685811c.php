<section class="layout-pt-md layout-pb-md bravo-list-event layout_<?php echo e($style_list); ?>">
    <div data-anim-wrap class="container">
        <div data-anim-child="slide-up delay-1" class="row  y-gap-20 justify-center text-center">
            <div class="col-auto">
                <div class="sectionTitle -md">
                    <h2 class="sectionTitle__title"><?php echo e($title ?? ''); ?></h2>
                    <p class=" sectionTitle__text mt-5 sm:mt-0"><?php echo e($desc ?? ''); ?></p>
                </div>
            </div>
        </div>
        <div class="row y-gap-30 pt-40 sm:pt-20">
            <?php if($rows->count()): ?>
                <?php $itemClass = 'col-xl-3 col-lg-3 col-sm-6';
                    if ($rows->count() == 5) $itemClass = 'col-xl col-md-4 col-sm-6 is-in-view';
                ?>

                <?php $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div data-anim-child="slide-up delay-<?php echo e($k); ?>" class="<?php echo e($itemClass); ?>">
                        <?php echo $__env->make('Event::frontend.layouts.search.loop-grid', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
        </div>
    </div>
</section><?php /**PATH C:\wamp64\www\chufir\themes/GoTrip/Event/Views/frontend/blocks/list-event/index.blade.php ENDPATH**/ ?>