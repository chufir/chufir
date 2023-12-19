<?php $style = $style ?? "style1" ?>
<?php switch($style):
    case ('style2'): ?> <?php echo $__env->make('Tour::frontend.blocks.list-featured-item.style2', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> <?php break; ?>
    <?php case ('style3'): ?> <?php echo $__env->make('Tour::frontend.blocks.list-featured-item.style3', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> <?php break; ?>
    <?php case ('style4'): ?> <?php echo $__env->make('Tour::frontend.blocks.list-featured-item.style4', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> <?php break; ?>
    <?php case ('style5'): ?> <?php echo $__env->make('Tour::frontend.blocks.list-featured-item.style5', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> <?php break; ?>
    <?php case ('style6'): ?> <?php echo $__env->make('Tour::frontend.blocks.list-featured-item.style6', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> <?php break; ?>
    <?php default: ?> <?php echo $__env->make('Tour::frontend.blocks.list-featured-item.style1', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endswitch; ?>
<?php /**PATH /home/brincite/public_html/themes/GoTrip/Tour/Views/frontend/blocks/list-featured-item/index.blade.php ENDPATH**/ ?>