<!-- Results count and sort -->
<div class="row y-gap-10 items-center justify-between">
    <div class="col-auto">
        <div class="text-18 fw-500 result-count">
            <?php if($rows->total() > 1): ?>
                <?php echo __(":count boats found",['count'=>$rows->total()]); ?>

            <?php else: ?>
                <?php echo __(":count boat found",['count'=>$rows->total()]); ?>

            <?php endif; ?>
        </div>
    </div>

    <div class="col-auto">
        <div class="row x-gap-20 y-gap-20">
            <div class="col-auto bc-form-order">
                <?php echo $__env->make('Layout::global.search.orderby',['routeName'=>'boat.search'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </div>
    </div>
</div>
<!-- End Results count and sort -->

<?php if(is_mobile()): ?>
<!--Filter mobile-->
<div class="filterPopup bg-white" data-x="filterPopup" data-x-toggle="-is-active">
    <aside class="sidebar -mobile-filter">
        <div data-x-click="filterPopup" class="-icon-close">
            <i class="icon-close"></i>
        </div>

        <?php echo $__env->make('Boat::frontend.layouts.search.sidebar-form-search', ['layout' => $layout], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- Mobile form -->
    </aside>
</div>
<?php endif; ?>

<!--End Filter mobile-->
<div class="ajax-search-result">
    <?php echo $__env->make('Boat::frontend.ajax.search-result', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>

<?php /**PATH C:\wamp64\www\chufir\themes/GoTrip/Boat/Views/frontend/layouts/search/list-item.blade.php ENDPATH**/ ?>