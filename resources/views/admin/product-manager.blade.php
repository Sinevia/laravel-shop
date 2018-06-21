@extends('admin.layout')

@section('webpage_title', 'Products')

@section('webpage_header')
<section class="content-header">
    <h1>
        Products
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo action('Admin\HomeController@anyIndex'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo action('Admin\ShopController@anyIndex'); ?>">Shop</a></li>
        <li class="active">Products</li>
    </ol>
</section>
@stop

@section('webpage_content')
<div class="box box-primary">
    <div class="box-header with-border">
        <a href="<?php echo action('Admin\ShopController@getProductCreate'); ?>" class="btn btn-success pull-right">
            <span class="glyphicon glyphicon-plus-sign"></span>
            New Product
        </a>
    </div>

    <div class="box-body">

        <!-- START: Filter -->
        <div class="well hidden-sm hidden-xs">
            <form class="form-inline" name="form_filter" method="get" style="margin:0px;">
                Filter:
                <div class="form-group">
                    <label class="sr-only" for="filter_category">Status</label>
                    <select id="filter_status" name="filter_status" class="form-control" onchange="form_filter.submit();">
                        <option value="">- Status -</option>
                        <?php $selected = ($filter_status != 'Draft') ? '' : ' selected="selected"'; ?>
                        <option value="Draft" <?php echo $selected; ?>>Draft</option>
                        <?php $selected = ($filter_status != 'Published') ? '' : ' selected="selected"'; ?>
                        <option value="Published" <?php echo $selected; ?>>Published</option>
                        <?php $selected = ($filter_status != 'Unpublished') ? '' : ' selected="selected"'; ?>
                        <option value="Unpublished" <?php echo $selected; ?>>Unpublished</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="sr-only" for="filter_category">Category</label>

                    <select id="filter_category" name="filter_category" class="form-control" onchange="form_filter.submit();">
                        <?php $selected = ($filter_category == '') ? 'selected="selected"' : ''; ?>
                        <option value="" <?php echo $selected ?> >- Category -</option>
                        <optgroup label="Categories">
                            <?php foreach ($roots as $root) { ?>
                                <?php $nodes = $root->traverse(); ?>
                                <?php foreach ($nodes as $node) { ?>
                                    <?php
                                    $nodeId = $node['Id'];
                                    $path = $node->getPath();
                                    $selected = ($filter_category != $nodeId) ? '' : ' selected="selected"';
                                    if (count($path) != 1) {
                                        $node_name = str_repeat("&nbsp;&nbsp;", count($path) - 1) . '- ' . $node["Title"];
                                    } else {
                                        $node_name = $node["Title"];
                                    }
                                    ?>
                                    <option value="<?php echo $node['Id']; ?>" <?php echo $selected; ?>><?php echo $node_name; ?></option>
                                <?php } ?>
                            <?php } ?>
                        </optgroup>
                    </select>
                </div>

                <button class="btn btn-primary">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
                <input type="hidden" name="cmd" value="products_manager">
            </form>
        </div>
        <!-- END: Filter -->

        <ul class="nav nav-tabs" style="margin-bottom: 3px;">
            <li class="<?php if ($view == '') { ?>active<?php } ?>">
                <a href="?cmd=products_manager">
                    <span class="glyphicon glyphicon-list"></span> Live
                </a>
            </li>
            <li class="<?php if ($view == 'trash') { ?>active<?php } ?>">
                <a href="?cmd=products_manager&view=trash">
                    <span class="glyphicon glyphicon-trash"></span> Trash
                </a>
            </li>
        </ul>

        <!--START: Categories -->
        <style scoped="scoped">
            .table-striped > tbody > tr:nth-child(2n+1) > td{
                background-color: transparent !important;
            }
            .table-striped > tbody > tr:nth-child(2n+1){
                background-color: #F9F9F9 !important;
            }
            #table_articles tr:hover {
                background-color: #FEFF8F !important;
            }
        </style>
        <table id="table_articles" class="table table-striped">
            <tr>
                <th style="text-align:center;">
                    <a href="?cmd=products-manager&amp;by=product&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Product&nbsp;<?php
                        if ($orderby === 'product') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>,
                    <a href="?cmd=products-manager&amp;by=id&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Ref&nbsp;<?php
                        if ($orderby === 'id') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>
                </th>
                <th style="text-align:center;width:100px;">
                    <a href="?cmd=products-manager&amp;by=status&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Status&nbsp;<?php
                        if ($orderby === 'status') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>
                </th>
                <th style="text-align:center;width:100px;">
                    <a href="?cmd=products-manager&amp;by=status&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Price&nbsp;<?php
                        if ($orderby === 'status') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>
                </th>
                <th style="text-align:center;width:100px;">
                    <a href="?cmd=products-manager&amp;by=status&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Quantity&nbsp;<?php
                        if ($orderby === 'status') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>
                </th>
                <th style="text-align:center;width:100px;">Category</th>
                <th style="text-align:center;width:200px;">Action</th>
            </tr>

            <?php foreach ($products as $product) { ?>
                <tr>
                    <td style="text-align:left;vertical-align: middle;">
                        <?php echo $product['Title']; ?>
                        <div style="font-size:10px;color: #999;">
                            Ref. <?php echo $product['Id']; ?><br> 
                        </div>
                    </td>
                    <td style="text-align:center;vertical-align: middle;">
                        <?php echo $product['Status']; ?><br>
                    </td>
                    <td style="text-align:center;vertical-align: middle;">
                        <?php echo $product['Price']; ?><br>
                    </td>
                    <td style="text-align:center;vertical-align: middle;">
                        <?php echo $product['Quantity']; ?><br>
                    </td>
                    <td style="text-align:center;vertical-align: middle;">
                        <?php $category = App\Models\Shop\Category::find($product['CategoryId']); ?>
                        <?php if ($category != null) { ?>
                            <?php echo $category['Title']; ?>
                        <?php } else { ?>
                            Unassigned
                        <?php } ?>
                    </td>
                    <td style="text-align:center;vertical-align: middle;">
                        <a href="<?php echo action('Admin\ShopController@getProductUpdate'); ?>?ProductId=<?php echo $product['Id']; ?>" class="btn btn-sm btn-warning">
                            <span class="glyphicon glyphicon-edit"></span>
                            Edit
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="confirm_product_delete('<?php echo $product['Id']; ?>');">
                            <?php if ($view == "") { ?>
                                <span class="glyphicon glyphicon-trash"></span>
                                Trash
                            <?php } else { ?>
                                <span class="glyphicon glyphicon-remove-sign"></span>
                                Delete
                            <?php } ?>
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <!-- END: Categories -->

        <!-- START: Pagination -->
        <?php echo $products->render(); ?>
        <!-- END: Pagination -->
    </div>

    <div class="box-footer with-border">
    </div>

</div>

@include('admin/shop/product-delete-modal')

@endsection
