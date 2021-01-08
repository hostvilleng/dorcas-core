<?php $__env->startComponent('mail::layout'); ?>
    
    <?php $__env->slot('header'); ?>
        <?php $__env->startComponent('mail::header', ['url' => '#']); ?>
            <img width="137" src="<?php echo e(!empty($partner) && !empty($partner->logo) ? $partner->logo : cdn('images/dorcas.jpeg')); ?>" alt="logo">
        <?php echo $__env->renderComponent(); ?>
    <?php $__env->endSlot(); ?>

    
    <?php echo $slot; ?>


    
    <?php if(isset($subcopy)): ?>
        <?php $__env->slot('subcopy'); ?>
            <?php $__env->startComponent('mail::subcopy'); ?>
                <?php echo e($subcopy); ?>

            <?php echo $__env->renderComponent(); ?>
        <?php $__env->endSlot(); ?>
    <?php endif; ?>

    
    <?php $__env->slot('footer'); ?>
        <?php $__env->startComponent('mail::footer'); ?>
            <p class="sub align-center">
                The Dorcas Hub is an all-in-one productivity software platform that helps you run your entire business better.
            </p>
            <!-- <p class="sub align-center">
                <br>
                E: <a href="mailto:<?php echo e(config('dorcas-api.support.email')); ?>"><?php echo e(config('dorcas-api.support.email')); ?></a> or T: <a href="tel:<?php echo e(config('dorcas-api.support.phone')); ?>"><?php echo e(config('dorcas-api.support.phone')); ?></a> (9am-5pm, WAT)
            </p>
            <p class="sub align-center">
                <?php echo e(config('dorcas-api.info.address')); ?>

                <br/><?php echo e(config('dorcas-api.info.registration')); ?>

            </p>
            <p class="sub align-center">&copy; <?php echo e(date('Y')); ?> <?php echo e(!empty($app['product_name']) ? $app['product_name'] : 'Dorcas'); ?>. All rights reserved.</p> -->
        <?php echo $__env->renderComponent(); ?>
    <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>
