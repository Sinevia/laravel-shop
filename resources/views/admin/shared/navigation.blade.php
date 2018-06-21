<?php
$productCount = \Sinevia\Shop\Models\Product::where('Status', '<>', 'Deleted')->count();
?>
<div class="panel panel-default">
    <div class="panel-body" style="padding: 2px;">
        <ul class="nav nav-pills">
            <li>
                <a href="<?php echo \Sinevia\Shop\Helpers\Links::adminHome(); ?>">Dashboard</a>
            </li>
            <li>
                <a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductManager(); ?>">
                    Products
                    <span class="badge"><?php echo $productCount; ?></span>
                </a>
            </li>
        </ul>
    </div>
</div>
