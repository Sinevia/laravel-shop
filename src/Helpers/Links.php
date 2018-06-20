<?php

namespace Sinevia\Shop\Helpers;

class Links {

    public static function adminMediaManager($queryData = []) {
        return config('shop.urls.media-manager') . self::buildQueryString($queryData);
    }

    public static function adminHome($queryData = []) {
        return action('\Sinevia\Shop\Http\Controllers\ShopController@anyIndex') . self::buildQueryString($queryData);
    }

    public static function adminProductManager($queryData = []) {
        return action('\Sinevia\Shop\Http\Controllers\ShopController@getProductManager') . self::buildQueryString($queryData);
    }

    private static function buildQueryString($queryData = []) {
        $queryString = '';
        if (count($queryData)) {
            $queryString = '?' . http_build_query($queryData);
        }
        return $queryString;
    }

}
