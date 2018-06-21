<?php if (View::exists(config('shop.layout-master'))) { ?>
    @extends(config('shop.layout-master'))
<?php } ?>

@section('webpage_title', 'Product Manager')

@section('webpage_header')
<h1>
    Product Manager
    <button type="button" class="btn btn-primary pull-right" onclick="showProductCreateModal();">
        <span class="glyphicon glyphicon-plus-sign"></span>
        Add Product
    </button>
</h1>
<ol class="breadcrumb">
    <li><a href="<?php echo \Sinevia\Shop\Helpers\Links::adminHome(); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductManager(); ?>">Shop</a></li>
    <li class="active"><a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductManager(); ?>">Products</a></li>
</ol>
@stop

@section('webpage_content')

@include('shop::shared.navigation')

<div class="box box-primary">
    <div class="box-header with-border">
        <!-- START: Filter -->
        <div class="well hidden-sm hidden-xs">
            <form class="form-inline" name="form_filter" method="get" style="margin:0px;">
                Filter:
                <div class="form-group">
                    <label class="sr-only">Status</label>
                    <select id="filter_status" name="filter_status" class="form-control" onchange="form_filter.submit();">
                        <option value="">- Status -</option>
                        <?php $selected = ($filterStatus != 'Draft') ? '' : ' selected="selected"'; ?>
                        <option value="Draft" <?php echo $selected; ?>>Draft</option>
                        <?php $selected = ($filterStatus != 'Published') ? '' : ' selected="selected"'; ?>
                        <option value="Published" <?php echo $selected; ?>>Published</option>
                        <?php $selected = ($filterStatus != 'Unpublished') ? '' : ' selected="selected"'; ?>
                        <option value="Unpublished" <?php echo $selected; ?>>Unpublished</option>
                    </select>
                </div>

                <button class="btn btn-primary">
                    <span class="glyphicon glyphicon-search"></span>
                </button>

                <button type="button" class="btn btn-primary pull-right" onclick="showProductCreateModal();">
                    <span class="glyphicon glyphicon-plus-sign"></span>
                    Add Product
                </button>
            </form>
        </div>
        <!-- END: Filter -->

    </div>

    <div class="box-body">

        <ul class="nav nav-tabs" style="margin-bottom: 3px;">
            <li class="<?php if ($view == '') { ?>active<?php } ?>">
                <a href="?view=all">
                    <span class="glyphicon glyphicon-list"></span> Live
                </a>
            </li>
            <li class="<?php if ($view == 'trash') { ?>active<?php } ?>">
                <a href="?&view=trash">
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
                    <a href="?cmd=products-manager&amp;by=Title&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Title&nbsp;<?php
                        if ($orderby === 'Title') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>,
                    <a href="?cmd=products-manager&amp;by=Alias&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Alias&nbsp;<?php
                        if ($orderby === 'Alias') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>,
                    <a href="?cmd=products-manager&amp;by=id&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        ID&nbsp;<?php
                        if ($orderby === 'Id') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>
                </th>
                <th style="text-align:center;width:100px;">
                    <a href="?cmd=products-manager&amp;by=Status&amp;sort=<?php if ($sort == 'asc') { ?>desc<?php } else { ?>asc<?php } ?>">
                        Status&nbsp;<?php
                        if ($orderby === 'Status') {
                            if ($sort == 'asc') {
                                ?>&#8595;<?php } else { ?>&#8593;<?php
                            }
                        }
                        ?>
                    </a>
                </th>
                <th style="text-align:center;width:160px;">Action</th>
            </tr>

            <?php foreach ($products as $product) { ?>
                <tr>
                    <td>
                        <div style="color:#333;font-size: 14px;font-weight:bold;">
                            <?php echo $product->Title; ?>
                        </div>                        
                        <div style="color:#333;font-size: 12px;font-style:italic;">
                            <?php echo $product->Alias; ?>
                        </div>
                        <div style="color:#999;font-size: 10px;">
                            ref. <?php echo $product->Id; ?>
                        </div>
                    <td style="text-align:center;vertical-align: middle;">
                        <?php echo $product['Status']; ?><br>
                    </td>
                    <td style="text-align:center;vertical-align: middle;">
                        <a href="<?php // echo $product->url(); ?>" class="btn btn-sm btn-success" target="_blank">
                            <span class="glyphicon glyphicon-eye-open"></span>
                            View
                        </a>
                        <a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductUpdate(['ProductId' => $product['Id']]); ?>" class="btn btn-sm btn-warning">
                            <span class="glyphicon glyphicon-edit"></span>
                            Edit
                        </a>

                        <?php if ($product->Status == 'Deleted') { ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmProductDelete('<?php echo $product->Id; ?>');">
                                <span class="glyphicon glyphicon-remove-sign"></span>
                                Delete
                            </button>
                        <?php } ?>

                        <?php if ($product->Status != 'Deleted') { ?>
                            <button class="btn btn-sm btn-danger" onclick="confirmProductMoveToTrash('<?php echo $product->Id; ?>');">
                                <span class="glyphicon glyphicon-trash"></span>
                                Trash
                            </button>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <!-- END: Categories -->

        <!-- START: Pagination -->    
        {!! $products->render() !!}
        <!-- END: Pagination -->
    </div>

</div>

<!-- START: Product Create Modal Dialog -->
<div class="modal fade" id="ModalProductCreate">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>New Product</h3>
            </div>
            <div class="modal-body">
                <form name="FormProductCreate" method="post" action="<?php echo \Sinevia\Shop\Helpers\Links::adminProductCreate(); ?>">
                    <div class="form-group">
                        <label>Title</label>
                        <input name="Title" value="" class="form-control" />
                    </div>
                    <?php echo csrf_field(); ?>
                </form>
            </div>
            <div class="modal-footer">
                <a id="modal-close" href="#" class="btn btn-info pull-left" data-dismiss="modal">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                    Cancel
                </a>
                <a id="modal-close" href="#" class="btn btn-success" data-dismiss="modal" onclick="FormProductCreate.submit();">
                    <span class="glyphicon glyphicon-ok-circle"></span>
                    Create product
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    function showProductCreateModal() {
        $('#ModalProductCreate').modal('show');
    }
</script>
<!-- END: Product Create Modal Dialog -->


<!-- START: Product Delete Modal Dialog -->
<div class="modal fade" id="ModalProductDelete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Confirm Product Delete</h3>
            </div>
            <div class="modal-body">
                <div>
                    Are you sure you want to delete this product?
                </div>
                <div>
                    Note! This action cannot be undone.
                </div>

                <form name="FormProductDelete" method="post" action="<?php echo \Sinevia\Shop\Helpers\Links::adminProductDelete(); ?>">
                    <input type="hidden" name="ProductId" value="">
                    <?php echo csrf_field(); ?>
                </form>
            </div>
            <div class="modal-footer">
                <a id="modal-close" href="#" class="btn btn-info pull-left" data-dismiss="modal">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                    Cancel
                </a>
                <a id="modal-close" href="#" class="btn btn-danger" data-dismiss="modal" onclick="FormProductDelete.submit();">
                    <span class="glyphicon glyphicon-remove-sign"></span>
                    Delete Product
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    function confirmProductDelete(product_id) {
        $('#ModalProductDelete input[name=ProductId]').val(product_id);
        $('#ModalProductDelete').modal('show');
    }
</script>
<!-- END: Product Delete Modal Dialog -->

<!-- START: Product Move to Trash Modal Dialog -->
<div class="modal fade" id="ModalProductMoveToTrash">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Confirm Product Move to Trash</h3>
            </div>
            <div class="modal-body">
                <div>
                    Are you sure you want to move this product to trash?
                </div>

                <form name="FormProductMoveToTrash" method="post" action="<?php echo \Sinevia\Shop\Helpers\Links::adminProductMoveToTrash(); ?>">
                    <input type="hidden" name="ProductId" value="">
                    <?php echo csrf_field(); ?>
                </form>
            </div>
            <div class="modal-footer">
                <a id="modal-close" href="#" class="btn btn-info pull-left" data-dismiss="modal">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                    Cancel
                </a>
                <a id="modal-close" href="#" class="btn btn-danger" data-dismiss="modal" onclick="FormProductMoveToTrash.submit();">
                    <span class="glyphicon glyphicon-trash"></span>
                    Move to Trash
                </a>
            </div>
        </div>
    </div>
</div>
<script>
    function confirmProductMoveToTrash(productId) {
        $('#ModalProductMoveToTrash input[name=ProductId]').val(productId);
        $('#ModalProductMoveToTrash').modal('show');
    }
</script>
<!-- END: Product Move to Trash Modal Dialog -->


@stop
