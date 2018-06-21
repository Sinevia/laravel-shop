<?php

namespace Sinevia\Shop\Http\Controllers;

/**
 * Contains simple Shop functionality
 */
class ShopController extends \Illuminate\Routing\Controller {

    function anyIndex() {
        return $this->getProductManager();
    }

    function getHome() {
        return $this->getProductManager();
    }

    function orderDelete() {
        // START: Data
        $order_id = isset($_REQUEST['order_id']) == false ? '' : trim($_REQUEST['order_id']);
        $order = ShopModel::getOrderById($order_id);
        // END: Data

        if ($order == null) {
            return $this->flashMessage('Order with ID ' . $order_id . ' DOES NOT exist', ADMIN_URL . '?cmd=orders-manager');
        }
        $result = ShopModel::updateOrderById($order_id, array('status' => 'Deleted'));
        if ($result == false) {
            return $this->flashMessage('Order FAILED to delete', ADMIN_URL . '?cmd=orders-manager');
        }

        //END: Delete
        Sinevia\Utils::redirect('?cmd=orders-manager');
    }

    function orderUpdate() {
        // START: Data
        $order_id = isset($_REQUEST['id']) == false ? '' : trim($_REQUEST['id']);
        $order = ShopModel::getOrderById($order_id);
        // END: Data

        if ($order == null) {
            return $this->flashMessage('Order with ID ' . $order_id . ' DOES NOT exist', ADMIN_URL . '?cmd=orders-manager');
        }

        // START: Data
        $action = isset($_REQUEST['action']) == false ? '' : trim($_REQUEST['action']);
        $status = isset($_REQUEST['status']) == false ? $order['status'] : trim($_REQUEST['status']);
        $customer_notes = isset($_REQUEST['customer_notes']) == false ? $order['customer_notes'] : trim($_REQUEST['customer_notes']);
        $price = isset($_REQUEST['price']) == false ? $order['price'] : trim($_REQUEST['price']);
        $memo = isset($_REQUEST['memo']) == false ? $order['memo'] : trim($_REQUEST['memo']);
        $sid = isset($_POST['sid']) == false ? '' : trim($_POST['sid']);
        $customer = UsersModel::getUserById($order['customer_id']);
        $items = ShopModel::getOrderItemsByOrderId($order_id);
        $message = '';
        // END: Data
        // START: Update Order
        if ($sid == session_id()) {
            if ($status === '') {
                $message = 'You must enter order status';
            }

            if ($message == '') {
                $order_update = array();
                $order_update['status'] = $status;
                $order_update['customer_notes'] = $customer_notes;
                $order_update['price'] = $price;
                $order_update['memo'] = $memo;
                $result = ShopModel::updateOrderById($order_id, $order_update);
                if ($result !== false) {
                    if ($action === 'save') {
                        return Sinevia\Utils::redirect('?cmd=order-update&id=' . $order_id);
                    }
                    return Sinevia\Utils::redirect('?cmd=orders-manager');
                } else {
                    $message = 'Saving order FAILED...';
                }
            }
        }
        // END: Update Order

        $template_order = \Sinevia\Template::fromFile($this->templates_directory . 'order-update.phtml', array(
                    'message' => $message,
                    'order_id' => $order_id,
                    'status' => $status,
                    'price' => $price,
                    'customer_notes' => $customer_notes,
                    'memo' => $memo,
                    'customer' => $customer,
                    'items' => $items,
        ));
        $template = \Sinevia\Template::fromFile($this->templates_directory . 'layout.phtml', array(
                    'webpage_title' => 'Edit Order',
                    'content' => $template_order
        ));
        return $template;
    }

    function ordersManager() {
        //START: Data
        $view = isset($_REQUEST['view']) == false ? '' : trim($_REQUEST['view']);
        $filter_status = isset($_REQUEST['filter_status']) == false ? 'not_deleted' : trim($_REQUEST['filter_status']);
        $filter_id = isset($_REQUEST['filter_id']) == false ? '' : trim($_REQUEST['filter_id']);
        if ($view == 'trash') {
            $filter_status = 'Deleted';
        }
        if ($filter_status == 'Deleted') {
            $view = 'trash';
        }
        $page = isset($_REQUEST['page']) == false ? 0 : trim($_REQUEST['page']);
        $orderby = isset($_REQUEST['by']) == false ? 'id' : trim($_REQUEST['by']);
        $sort = isset($_REQUEST['sort']) == false ? 'asc' : trim($_REQUEST['sort']);
        $results_per_page = 20;
        $message = '';
        //END: Data
        // DEBUG: Application::getDatabase()->debug = true;
        // START: Get Menus
        $orders = ShopModel::getOrders(array(
                    'id' => $filter_id,
                    'status' => $filter_status,
                    'orderby' => $orderby,
                    'sort' => $sort,
                    'limit_from' => $page * $results_per_page,
                    'limit_to' => $results_per_page,
                    'append_count' => true,
        ));
        $total_count = array_pop($orders);
        // END: Get Menus
        // DEBUG: var_dump($orders);
        // START: Pagination
        $url = ADMIN_URL . '?cmd=orders_manager&amp;page=';
        $pagination = \Sinevia\Utils::pagination($total_count, $results_per_page, $page, $url);
        //END: Pagination
        // START: View
        $template_orders = \Sinevia\Template::fromFile($this->templates_directory . 'orders-manager.phtml', array(
                    'message' => $message,
                    'orders' => $orders,
                    'filter_id' => $filter_id,
                    'filter_status' => $filter_status,
                    'pagination' => $pagination,
                    'view' => $view,
                    'orderby' => $orderby,
                    'sort' => $sort,
        ));
        $template = \Sinevia\Template::fromFile($this->templates_directory . 'layout.phtml', array(
                    'webpage_title' => 'Orders Manager',
                    'content' => $template_orders
        ));
        return $template;
        // END: View
    }

    function getCategoryCreate() {
        $title = request('Title', old('Title', ''));
        $parentId = request('ParentId', old('ParentId', ''));
        $status = request('Status', old('Status', 'Draft'));
        $statuses = [
            \App\Models\Shop\Category::STATUS_PUBLISHED => 'Published',
            \App\Models\Shop\Category::STATUS_UNPUBLISHED => 'Unpublished',
                //\App\Models\Shop\Category::STATUS_DELETED => 'Deleted',
        ];
        $roots = \App\Models\Shop\Category::where('ParentId', '=', '')
                ->orderBy('Sequence', 'ASC')
                ->get();
        return view('admin.shop.category-create', get_defined_vars());
    }

    /**
     * Manage product categories
     */
    function getCategoryManager() {
        $orderby = isset($_REQUEST['by']) == false ? 'Sequence' : trim($_REQUEST['by']);
        $sort = isset($_REQUEST['sort']) == false ? 'asc' : trim($_REQUEST['sort']);
        $roots = \App\Models\Shop\Category::where('ParentId', '=', '')
                ->orderBy($orderby, $sort)
                ->get();
        return view('admin.shop.category-manager', get_defined_vars());
    }

    function getCategoryMoveDown() {
        $categoryId = request('CategoryId', old('CategoryId', ''));
        $category = \App\Models\Shop\Category::find($categoryId);

        if ($category == null) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->withErrors('Category with ID: ' . $categoryId . ' DOES NOT exist');
        }

        $thislevels = \App\Models\Shop\Category::where('ParentId', '=', $category->ParentId)->orderBy('Sequence', 'ASC')->get();
        for ($i = 0; $i < count($thislevels); $i++) {
            if ($thislevels[$i]['Id'] == $category['Id']) {
                $position = $i;
                break;
            }
        }
        if ($position == (count($thislevels) - 1)) {
            return redirect(action('Admin\ShopController@getCategoryManager'));
        } else {
            $temp = $category->Sequence;
            $sibling = $thislevels[$position + 1];
            $category->Sequence = $sibling->Sequence;
            $category->save();
            $sibling->Sequence = $temp;
            $sibling->save();
            return redirect(action('Admin\ShopController@getCategoryManager'));
        }
    }

    function getCategoryMoveUp() {
        $categoryId = request('CategoryId', old('CategoryId', ''));
        $category = \App\Models\Shop\Category::find($categoryId);

        if ($category == null) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->withErrors('Category with ID: ' . $categoryId . ' DOES NOT exist');
        }

        $thislevels = \App\Models\Shop\Category::where('ParentId', '=', $category->ParentId)->orderBy('Sequence', 'ASC')->get();
        for ($i = 0; $i < count($thislevels); $i++) {
            if ($thislevels[$i]['Id'] == $category['Id']) {
                $position = $i;
                break;
            }
        }

        if ($position == 0) {
            return redirect(action('Admin\ShopController@getCategoryManager'));
        } else {
            $temp = $category->Sequence;
            $sibling = $thislevels[$position - 1];
            $category->Sequence = $sibling->Sequence;
            $category->save();
            $sibling->Sequence = $temp;
            $sibling->save();
            return redirect(action('Admin\ShopController@getCategoryManager'));
        }
    }

    function getCategoryUpdate() {
        $categoryId = request('CategoryId', old('CategoryId', ''));
        $category = \App\Models\Shop\Category::find($categoryId);

        if ($category == null) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->withErrors('Category with ID: ' . $categoryId . ' DOES NOT exist');
        }

        $title = request('Title', old('Title', $category->Title));
        $parentId = request('ParentId', old('ParentId', $category->ParentId));
        $status = request('Status', old('Status', $category->Status));
        $statuses = [
            \App\Models\Shop\Category::STATUS_PUBLISHED => 'Published',
            \App\Models\Shop\Category::STATUS_UNPUBLISHED => 'Unpublished',
                //\App\Models\Shop\Category::STATUS_DELETED => 'Deleted',
        ];
        $roots = \App\Models\Shop\Category::where('ParentId', '=', '')
                ->orderBy('Sequence', 'ASC')
                ->get();
        return view('admin.shop.category-update', get_defined_vars());
    }

    function getProductCreate() {
        $title = request('Title', old('Title', ''));
        $summary = request('Summary', old('Summary', ''));
        $description = request('Description', old('Description', ''));
        $status = request('Status', old('Status', 'Draft'));
        $price = request('Price', old('Price', 0));
        $quantity = request('Quantity', old('Quantity', 0));
        $categoryId = request('CategoryId', old('CategoryId', ''));
        $featured = request('Featured', old('Featured', ''));
        $frontpage = request('Frontpage', old('Frontpage', ''));

        $statuses = [
            \Sinevia\Shop\Models\Product::STATUS_DRAFT => 'Draft',
            \Sinevia\Shop\Models\Product::STATUS_PUBLISHED => 'Published',
            \Sinevia\Shop\Models\Product::STATUS_UNPUBLISHED => 'Unpublished',
            //\Sinevia\Shop\Models\Product::STATUS_DELETED => 'Deleted',
        ];

        $roots = \Sinevia\Shop\Models\Category::where('ParentId', '=', '')
                ->orderBy('Sequence', 'ASC')
                ->get();

        return view('shop::admin/product-create', get_defined_vars());
    }

    function getProductManager() {
        $view = request('view', '');
        $filterStatus = request('filter_status', '');
        if ($view == 'trash') {
            $filterStatus = 'Deleted';
        }
        if ($filterStatus == 'Deleted') {
            $view = 'trash';
        }
        $filterCategory = request('filter_category', '');
        $session_order_by = \Session::get('shop_product_manager_by', 'Id');
        $session_order_sort = \Session::get('shop_product_manager_sort', 'asc');
        $orderby = request('by', $session_order_by);
        $sort = request('sort');
        $page = request('page', 0);
        $results_per_page = 20;
        \Session::put('shop_product_manager_by', $orderby); // Keep for session
        \Session::put('shop_prduct_manager_sort', $sort);  // Keep for session

        $q = \Sinevia\Shop\Models\Product::getModel();

        if ($filterStatus == "") {
            $q = $q->where('Status', '<>', 'Deleted');
        }
        if ($filterStatus != "") {
            $q = $q->where('Status', '=', $filterStatus);
        }
        if ($orderby == "Title") {
            $orderby = 'Id';
        }
        $q = $q->orderBy($orderby, $sort);
        $products = $q->paginate($results_per_page);

//        foreach ($pages as $i => $page) {
//            $default_translation = $page->translation('en');
//            $pages[$i]->Title = $default_translation->Title;
//        }
        $roots = \Sinevia\Shop\Models\Category::where('ParentId', '=', '')
                ->orderBy('Sequence', 'ASC')
                ->get();
        
        return view('shop::admin/product-manager', get_defined_vars());
    }

    function getProductManagerV1() {
        $filter_id = request('filter_id', '');
        $filter_status = request('filter_status', 'not_deleted');
        $filter_category = request('filter_category', '');
        $view = request('view', '');
        if ($view == 'trash') {
            $filter_status = 'Deleted';
        }
        $sort = request('sort', 'DESC');
        $orderby = request('orderby', 'Id');
        $results_per_page = 20;

        $q = new \App\Models\Shop\Product;
        if ($filter_id) {
            $q = $q->where('Id', '=', $filter_id);
        }
        if ($filter_category) {
            $q = $q->where('CategoryId', '=', $filter_category);
        }
        if ($filter_status == 'not_deleted') {
            $q = $q->where('Status', '<>', 'Deleted');
        } elseif ($filter_status != '') {
            $q = $q->where('Status', '=', $filter_status);
        }
        $q = $q->orderBy($orderby, $sort);
        $products = $q->paginate($results_per_page);

        $roots = \App\Models\Shop\Category::where('ParentId', '=', '')
                ->orderBy('Sequence', 'ASC')
                ->get();

        return view('admin.shop.product-manager', get_defined_vars());
    }

    function getProductUpdate() {
        $productId = request('ProductId', old('ProductId', ''));
        $product = \App\Models\Shop\Product::findOrFail($productId);

        $categoryId = request('CategoryId', old('CategoryId', $product->CategoryId));
        $description = request('Description', old('Description', $product->Description));
        $price = request('Price', old('Price', $product->Price));
        $quantity = request('Quantity', old('Quantity', $product->Quantity));
        $status = request('Status', old('Status', $product->Status));
        $summary = request('Summary', old('Summary', $product->Summary));
        $title = request('Title', old('Title', $product->Title));

        $statuses = [
            \App\Models\Shop\Product::STATUS_DRAFT => 'Draft',
            \App\Models\Shop\Product::STATUS_PUBLISHED => 'Published',
            \App\Models\Shop\Product::STATUS_UNPUBLISHED => 'Unpublished',
            \App\Models\Shop\Product::STATUS_DELETED => 'Deleted',
        ];

        $roots = \App\Models\Shop\Category::where('ParentId', '=', '')
                ->orderBy('Sequence', 'ASC')
                ->get();

        return view('admin.shop.product-update', get_defined_vars());
    }

    function postCategoryCreate() {
        $rules = [
            'Title' => 'required',
                //'ParentId' => 'required',
        ];

        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }

        $title = trim(request('Title', ''));
        $parentId = trim(request('ParentId', ''));
        $status = trim(request('Status', 'Draft'));


        $category = new \App\Models\Shop\Category;
        $category->Status = $status;
        $category->Title = $title;
        $category->ParentId = $parentId;
        $category->Sequence = substr(\Sinevia\Uid::microUid(), 0, 19);

        if ($category->save()) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->with('success', 'Category successfully created');
        }

        return redirect()->back()->withErrors('Creating news category failed.')->withInput(\Request::all());
    }

    function postCategoryDelete() {
        $rules = [
            'CategoryId' => 'required',
        ];

        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }

        $categoryId = request('CategoryId', old('CategoryId', ''));
        $category = \App\Models\Shop\Category::find($categoryId);

        if ($category == null) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->withErrors('Category with ID: ' . $categoryId . ' DOES NOT exist');
        }

        if (count($category->getChildren()) > 0) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->withErrors('Category has child categories. Please, delete before proceeding');
        }

        $products = \App\Models\Shop\Product::where('CategoryId', '=', $categoryId)->get();


        if (count($products) > 0) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->withErrors('Category has products assigned. Please, delete before proceeding');
        }

        /*
          $category->Status = 'Deleted';
          $category->UpdatedAt = date('Y-m-d H:i:s');

          if ($category->save()) {
          return redirect(action('Admin\ShopController@getCategoryManager'))->with('success', 'Category successfully deleted');
          }
         * 
         */

        if ($category->delete()) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->with('success', 'Category successfully deleted');
        }

        return redirect()->back()->withErrors('Deleteing category failed.')->withInput(\Request::all());
    }

    function postCategoryUpdate() {
        $rules = [
            'CategoryId' => 'required',
            'Title' => 'required',
        ];

        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }

        $categoryId = trim(request('CategoryId', ''));
        $category = \App\Models\Shop\Category::find($categoryId);

        if ($category == null) {
            return redirect(action('Admin\ShopController@getCategoryManager'))->withErrors('Category with ID: ' . $categoryId . ' DOES NOT exist');
        }

        $title = trim(request('Title', $category->Title));
        $parentId = trim(request('ParentId', $category->ParentId));
        $status = trim(request('Status', $category->Status));

        $category->Status = $status;
        $category->Title = $title;
        $category->ParentId = $parentId;

        if ($category->save()) {
            return redirect()->back()->with('success', 'Category successfully saved');
        }

        return redirect()->back()->withErrors('Saving category failed.')->withInput(\Request::all());
    }

    function postProductCreate() {
        $rules = [
            'CategoryId' => 'required',
            'Title' => 'required',
            'Summary' => 'required',
            'Description' => 'required',
            'Price' => 'required|numeric',
            'Quantity' => 'numeric',
        ];

        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()
                    ->withErrors($validator->errors())
                    ->withInput(\Request::all());
        }

        $categoryId = trim(request('CategoryId', ''));
        $description = trim(request('Description', ''));
        $price = trim(request('Price', ''));
        $quantity = trim(request('Quantity', ''));
        $status = trim(request('Status', \Sinevia\Shop\Models\Product::STATUS_DRAFT));
        $summary = trim(request('Summary', ''));
        $title = trim(request('Title', ''));
        $published = strtotime(request('Published', date('Y-m-d H:i:s')));

        \DB::beginTransaction();

        try {
            $product = new \Sinevia\Shop\Models\Product();
            $product->Title = $title;
            $product->Summary = $summary;
            $product->Description = $description;
            $product->Price = $price;
            $product->Quantity = $quantity;
            $product->CategoryId = $categoryId;
            $product->Status = $status;
            $product->save();

            \DB::commit();
            return redirect(\Sinevia\Shop\Helpers\Links::adminProductManager())
                    ->with('success', 'Products successfully created');
        } catch (Exception $e) {
            
        }

        \DB::rollBack();

        return redirect()->back()->withErrors('Creating product failed.')->withInput(\Request::all());
    }

    function postProductDelete() {
        $rules = [
            'ProductId' => 'required',
        ];

        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }

        $postId = request('ProductId', '');

        $post = \App\Models\Shop\Product::find($postId);
        if ($post == null) {
            return redirect()->back()->withErrors('Product with ID:' . $postId . ' NOT FOUND')->withInput(\Request::all());
        }

        if ($post->Status == 'Deleted') {
            if ($post->delete()) {
                return redirect(action('Admin\ShopController@getProductManager'))->with('success', 'Product successfully deleted');
            }
            return redirect()->back()->withErrors('Deleteing post failed.')->withInput(\Request::all());
        }

        $post->Status = 'Deleted';
        $post->UpdatedAt = date('Y-m-d H:i:s');

        if ($post->save()) {
            return redirect(action('Admin\ShopController@getProductManager'))->with('success', 'Product successfully moved to trash');
        }

        return redirect()->back()->withErrors('Deleteing post failed.')->withInput(\Request::all());
    }

    function postProductUpdate() {
        $rules = [
            'CategoryId' => 'required',
            'ProductId' => 'required',
            'Status' => 'required',
            'Title' => 'required',
            'Summary' => 'required',
            'Description' => 'required',
            'Price' => 'required|numeric',
            'Quantity' => 'numeric',
        ];

        $validator = \Validator::make(\Request::all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator->errors())->withInput(\Request::all());
        }

        $productId = request('ProductId', '');

        $p = \App\Models\Shop\Product::find($productId);
        if ($p == null) {
            return redirect()->back()->withErrors('Product with ID:' . $productId . ' NOT FOUND')->withInput(\Request::all());
        }

        $categoryId = request('CategoryId', old('CategoryId', $p->CategoryId));
        $description = request('Description', old('Description', $p->Description));
        $price = request('Price', old('Price', $p->Price));
        $quantity = request('Quantity', old('Quantity', $p->Quantity));
        $status = request('Status', old('Status', $p->Status));
        $summary = request('Summary', old('Summary', $p->Summary));
        $title = request('Title', old('Title', $p->Title));

        \DB::beginTransaction();

        try {
            $p->Title = $title;
            $p->Summary = $summary;
            $p->Description = $description;
            $p->Price = $price;
            $p->Quantity = $quantity;
            $p->CategoryId = $categoryId;
            $p->UpdatedAt = date('Y-m-d H:i:s');
            $p->Status = $status;
            $p->save();

            \DB::commit();
            return redirect(action('Admin\ShopController@getProductManager'))->with('success', 'Product successfully saved');
        } catch (Exception $e) {
            
        }

        \DB::rollBack();

        return redirect()->back()->withErrors('Saving product failed.')->withInput(\Request::all());
    }

    /**
     * Lists product images
     * @return string
     */
    function productImagesAjax() {
        // START: Data
        $product_id = isset($_REQUEST['ProductId']) == false ? '' : trim($_REQUEST['ProductId']);
        $images = ShopModel::getProductImagesByProductId($product_id);
        // END: Data

        $images_list = array();
        foreach ($images as $image) {
            $images_list[] = array(
                'UniqueId' => $image['UniqueId'],
                'Url' => DATA_URL . 'products/' . $image['Filename'],
                'Filename' => $image['Filename']
            );
        }

        return json_encode($images_list);
    }

    function productImageDeleteAjax() {
        // START: Data
        $image_id = isset($_REQUEST['ImageId']) == false ? '' : trim($_REQUEST['ImageId']);
        $image = ShopModel::getProductImageById($image_id);
        // END: Data

        if ($image == null) {
            return '';
        }

        $image_path = DATA_DIR . 'products' . DIRECTORY_SEPARATOR . $image['Filename'];
        $result = ShopModel::deleteProductImageById($image_id);
        if ($result !== false) {
            @unlink($image_path);
        }
    }

    function productImageUpload() {
        // START: Data
        $product_id = isset($_REQUEST['ProductId']) == false ? '' : trim($_REQUEST['ProductId']);
        $product = ShopModel::getProductById($product_id);
        // END: Data

        if ($product == null) {
            return $this->flashMessage('Product with ID ' . $product_id . ' DOES NOT exist', ADMIN_URL . '?cmd=products-manager');
        }


        // START: Data
        $products_image_dir = DATA_DIR . 'products';
        $allowed_extensions = array("bmp", "gif", "jpg", "jpeg", "png", "tiff");
        $sid = (isset($_POST["sid"]) === false) ? "" : trim($_POST["sid"]);
        //$error = "";
        // END: Data
        // START: Upload file
        if ($sid == session_id()) {
            if (count($_FILES) > 0 && $_FILES['file_upload']['name'] != '') {
                ini_set('memory_limit', '128M');
                ini_set('post_max_size', '10M');
                ini_set('upload_max_filesize', '20M');

                $file_name = $_FILES['file_upload']['name'];
                $file_parts = explode('.', $file_name);
                $file_ext = strtolower(array_pop($file_parts));

                $product_image_name = $product_id . '-' . date('Ymd-His', time()) . '.' . $file_ext;

                if (in_array($file_ext, $allowed_extensions) === false) {
                    $message = 'The chosen file (.' . $file_ext . ') is of not allowed file type.';
                    return $this->flashMessage($message, ADMIN_URL . '?cmd=product-update&id=' . $product_id, 15);
                }

                $file_path = rtrim($products_image_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $product_image_name;

                if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $file_path)) {
                    // DEBUG: ShopModel::getDatabase()->debug = true;
                    $status = '<script>parent.images_refresh();</script>';
                    $product_image = array();
                    $product_image['ProductId'] = $product_id;
                    $product_image['Filename'] = $product_image_name;
                    $product_image['Created'] = date('Y-m-d H:i:s');
                    $product_image['Updated'] = date('Y-m-d H:i:s');
                    $image_id = ShopModel::createProductImage($product_image);
                    ShopModel::updateProductImageById($image_id, array('Sequence' => $image_id));
                } else {
                    if (count($_FILES) == 0 || $_FILES['file_upload']['error'] == 1) {
                        $status = 'File too big!';
                        $url = ADMIN_URL . '?cmd=product-update&id=' . $product_id;
                        //return $this->flashMessage($status, $url, 3);
                    } else if ($_FILES['file_upload']['error'] == 6 || $_FILES['upload_file']['error'] == 7) {
                        $status = 'Unable to upload file to server!';
                        $url = ADMIN_URL . '?cmd=product-update&id=' . $product_id;
                        //return $this->flashMessage($status, $url, 3);
                    } else {
                        $status = 'File upload FAILED!';
                        $url = ADMIN_URL . '?cmd=product-update&id=' . $product_id;
                        //return $this->flashMessage($status, $url, 3);
                    }
                    $status = '<script>alert("' . $status . '")</script>';
                }
            }
        }
        // END: Upload file

        echo $status;

        //return \Sinevia\Utils::redirect(ADMIN_URL . '?cmd=product-update&id=' . $product_id);
    }

}
