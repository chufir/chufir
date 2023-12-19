<?php $location_name = ""; $location_id = ''; $list_json = [];
$traverse = function ($locations, $prefix = '') use (&$traverse, &$list_json , &$location_name, &$location_id) {
    foreach ($locations as $location) {
        $translate = $location->translate();
        if (Request::query('location_id') == $location->id){
            $location_name = $translate->name;
            $location_id = $location->id;
        }
        $list_json[] = [
            'id' => $location->id,
            'title' => $prefix . ' ' . $translate->name,
        ];
        $traverse($location->children, $prefix . '-');
    }
};
$traverse($list_location ?? $tour_location);
if (empty($inputName)){
    $inputName = 'location_id';
}
$type = $search_style ?? "normal";
?>
<div class="searchMenu-loc js-form-dd js-liverSearch item">
    <span class="clear-loc absolute bottom-0 text-12"><i class="icon-close"></i></span>
    <div data-x-dd-click="searchMenu-loc">
        <h4 class="text-15 fw-500 ls-2 lh-16"><?php echo e($field['title']); ?></h4>
        <div class="text-15 text-light-1 ls-2 lh-16  <?php if( $type == "autocomplete"): ?> smart-search  <?php endif; ?> ">
            <input type="hidden" name="<?php echo e($inputName); ?>" class="js-search-get-id" value="<?php echo e($location_id ?? ''); ?>">
            <input type="text" autocomplete="off" <?php if( $type == "normal"): ?> readonly  <?php endif; ?> class="smart-search-location parent_text js-search js-dd-focus" placeholder="<?php echo e(__("Where are you going?")); ?>" value="<?php echo e($location_name); ?>" data-onLoad="<?php echo e(__("Loading...")); ?>" data-default="<?php echo e(json_encode($list_json)); ?>">
        </div>
    </div>
    <div class="searchMenu-loc__field shadow-2 js-popup-window <?php if($type!='normal'): ?> d-none <?php endif; ?>" data-x-dd="searchMenu-loc" data-x-dd-toggle="-is-active">
        <div class="bg-white px-30 py-30 sm:px-0 sm:py-15 rounded-4">
            <div class="y-gap-5 js-results">
                <?php $__currentLoopData = $list_json; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="-link d-block col-12 text-left rounded-4 px-20 py-15 js-search-option" data-id="<?php echo e($location['id']); ?>">
                        <div class="d-flex align-items-center">
                            <div class="icon-location-2 text-light-1 text-20 pt-4"></div>
                            <div class="ml-10">
                                <div class="text-15 lh-12 fw-500 js-search-option-target"><?php echo e($location['title']); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH /home/brincite/public_html/themes/GoTrip/Layout/common/search/fields/location.blade.php ENDPATH**/ ?>