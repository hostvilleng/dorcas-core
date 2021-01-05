<?php $__env->startComponent('mail::message', ['partner'  => $partner]); ?>
    
    <?php if(! empty($greeting)): ?>
        <h1><?php echo e($greeting); ?></h1>
    <?php else: ?>
        <?php if($level == 'error'): ?>
            <h1>Whoops!</h1>
        <?php else: ?>
            <h1>Hello!</h1>
        <?php endif; ?>
    <?php endif; ?>

    
    <?php $__currentLoopData = $introLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <p><?php echo e($line); ?></p>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <?php if(isset($actionText)): ?>
        <?php
        switch ($level) {
            case 'success':
                $color = 'green';
                break;
            case 'error':
                $color = 'red';
                break;
            default:
                $color = 'blue';
        }
        ?>
        <?php $__env->startComponent('mail::button', ['url' => $actionUrl, 'color' => $color]); ?>
            <?php echo e($actionText); ?>

        <?php echo $__env->renderComponent(); ?>
    <?php endif; ?>

    
    <?php $__currentLoopData = $outroLines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <p><?php echo e($line); ?></p>

    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <p>
        <?php if(! empty($salutation)): ?>
            <?php echo e($salutation); ?>

        <?php else: ?>
            The <?php echo e(!empty($app['product_name']) ? $app['product_name'] : 'Dorcas'); ?> Team<br>
        <?php endif; ?>
    </p>

    
    <?php if(isset($actionText)): ?>
        <?php $__env->startComponent('mail::subcopy'); ?>
            If you’re having trouble clicking the "<?php echo e($actionText); ?>" button, copy and paste the URL below
            into your web browser: [<?php echo e($actionUrl); ?>](<?php echo e($actionUrl); ?>)
        <?php echo $__env->renderComponent(); ?>
    <?php endif; ?>
<?php echo $__env->renderComponent(); ?>
