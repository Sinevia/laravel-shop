<?php if (View::exists(config('shop.layout-master'))) { ?>
    @extends(config('shop.layout-master'))
<?php } ?>

@section('webpage_title', 'New Product')

@section('webpage_header')
<section class="content-header">
    <h1>
        New
        <small>Product</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="<?php echo \Sinevia\Shop\Helpers\Links::adminHome(); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductManager(); ?>">Shop</a></li>
        <li class="active"><a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductManager(); ?>">Products</a></li>
        <li>New Product</li>
    </ol>
</section>
@stop

@section('webpage_content')

@include('shop::shared.navigation')

<div class="box box-primary">
    <link href="//cdn.ckeditor.com/4.5.3/standard/plugins/codesnippet/lib/highlight/styles/default.css" rel="stylesheet">
    <script src="//cdn.ckeditor.com/4.5.3/standard/ckeditor.js"></script>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script>
        $(function () {
            $(".datepicker").datepicker({dateFormat: 'yy-mm-dd'});
        });
    </script>
    <!-- START: Date time picker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker-standalone.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker-standalone.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment-with-locales.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <!-- END: Date time picker -->

    <div class="box-header with-border">
        <a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductManager(); ?>" class="btn btn-primary pull-left">
            <span class="fa fa-chevron-left"></span>
            Cancel
        </a>
        <button class="btn btn-success pull-right" onclick="FORM_PRODUCT_EDIT.submit();">
            <span class="fa fa-save"></span>
            Save
            </a>
        </button>
    </div>

    <div class="box-body">

        <form method="POST" name="FORM_PRODUCT_EDIT">
            <div class="form-group well" style="display: table;width: 100%;">
                <label class="col-sm-1 col-xs-2 control-label" style="padding:8px 0px 0px 0px;">Status</label>
                <div class="col-sm-11 col-xs-10">
                    <select class="form-control" name="Status">
                        <?php foreach ($statuses as $key => $value) { ?>
                            <?php $selected = $status == $key ? ' selected="selected"' : '' ?>
                            <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="category_id">
                    Category
                </label>
                <select class="form-control" name="CategoryId">
                    <?php $selected = ($categoryId == '') ? 'selected="selected"' : ''; ?>
                    <option value="" <?php echo $selected ?> >Main Category</option>
                    <optgroup label="Categories">
                        <?php foreach ($roots as $root) { ?>
                            <?php $nodes = $root->traverse(); ?>
                            <?php foreach ($nodes as $node) { ?>
                                <?php
                                $nodeId = $node['Id'];
                                $path = $node->getPath();
                                $selected = ($categoryId != $nodeId) ? '' : ' selected="selected"';
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
            <div class="form-group">
                <label>
                    Product Title
                </label>
                <input class="form-control" name="Title" id="Product" type="text" value="<?php echo htmlentities($title); ?>" />
            </div>
            <div class="form-group" style="width:100%;">
                <label>
                    Summary
                </label>
                <textarea class="form-control" name="Summary" id="Summary" style="height:100px;width:100%;"><?php echo htmlentities($summary); ?></textarea>

                <script>
                    CKEDITOR.replace('Summary', {
                        // Define the toolbar groups as it is a more accessible solution.

                        toolbarGroups: [
                            {"name": "clipboard", "groups": ["clipboard", 'undo']},
                            {"name": "basicstyles", "groups": ["basicstyles"]},
                            {"name": "links", "groups": ["links"]},
                            {"name": "paragraph", "groups": ["list", "blocks"]},
                            {"name": "insert", "groups": ["insert"]},
                            {"name": "styles", "groups": ["styles"]},
                            {"name": "document", "groups": ["mode"]},
                            {"name": "tools", "groups": ["tools", 'UIColor']}
                        ],
                        // Remove the redundant buttons from toolbar groups defined above.
                        //removeButtons: 'Strike,Subscript,Superscript,Anchor,Styles,Image',
                        title: false
                    });
                </script>
            </div>
            <div class="form-group" style="width:100%;">
                <label>
                    Description
                </label>
                <textarea class="form-control" name="Description" id="Description" style="height:300px;width:100%;"><?php echo htmlentities($description); ?></textarea>

                <script>
                    CKEDITOR.replace('Description', {
                        // Define the toolbar groups as it is a more accessible solution.

                        toolbarGroups: [
                            {"name": "clipboard", "groups": ["clipboard", 'undo']},
                            {"name": "basicstyles", "groups": ["basicstyles"]},
                            {"name": "links", "groups": ["links"]},
                            {"name": "paragraph", "groups": ["list", "blocks"]},
                            {"name": "insert", "groups": ["insert"]},
                            {"name": "styles", "groups": ["styles"]},
                            {"name": "document", "groups": ["mode"]},
                            {"name": "tools", "groups": ["tools", 'UIColor']}
                        ],
                        // Remove the redundant buttons from toolbar groups defined above.
                        //removeButtons: 'Strike,Subscript,Superscript,Anchor,Styles,Image',
                        title: false
                    });
                </script>
            </div>


            <div class="form-group">
                <label>
                    Price
                </label>
                <input class="form-control" name="Price" id="Price" type="text" value="<?php echo htmlentities($price); ?>" />
            </div>


            <div class="form-group">
                <label>
                    Quantity
                </label>
                <input class="form-control" name="Quantity" id="Quantity" type="text" value="<?php echo htmlentities($quantity); ?>" />
            </div>

            <?php echo csrf_field(); ?>
        </form>

    </div>

    <div class="box-footer with-border">
        <a href="<?php echo \Sinevia\Shop\Helpers\Links::adminProductManager(); ?>" class="btn btn-primary pull-left">
            <span class="fa fa-chevron-left"></span>
            Cancel
        </a>
        <button class="btn btn-success pull-right" onclick="FORM_PRODUCT_EDIT.submit();">
            <span class="fa fa-save"></span>
            Save
            </a>
        </button>
    </div>

</div>

<br />
<br />

@endsection

<script type="text/javascript">
    $(window).keypress(function (event) {
        if (!(event.which === 115 && event.ctrlKey) && !(event.which === 19)) {
            return true;
        }
        $('#button_save').trigger('click');
        event.preventDefault();
        return false;
    });
</script>

