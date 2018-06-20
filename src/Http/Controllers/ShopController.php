<?php

namespace Sinevia\Shop\Http\Controllers;

/**
 * Contains simple Shop functionality
 */
class ShopController extends \Illuminate\Routing\Controller {

    function anyIndex() {
        return $this->getProductManager();
    }
    
    function getProductManager(){
        return 'ProductManager';
    }
}
