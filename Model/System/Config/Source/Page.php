<?php

namespace Targetbay\Tracking\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Page implements ArrayInterface
{	
	CONST ALL_PAGES = 'all';
	CONST PAGE_VISIT = 'page-visit';
	CONST PRODUCT_VIEW = 'product-view';
	CONST CATEGORY_VIEW = 'category-view';
	CONST DELETE_PRODUCT = "delete-product";
	CONST UPDATE_PRODUCT = 'update-product';
	CONST ADD_PRODUCT = 'add-product';
	CONST CREATE_ACCOUNT = 'create-account';
	CONST ADMIN_ACTIVATE_ACCOUNT = 'admin-activate-customer-account';
	CONST LOGIN = 'login';
	CONST LOGOUT = 'logout';
	CONST ADDTOCART = 'add-to-cart';
	CONST REMOVECART = 'remove-to-cart';
	CONST UPDATECART = 'update-cart';
	CONST ORDER_ITEMS = 'ordered-items';
	CONST BILLING = 'billing';
	CONST SHIPPING = 'shipping';
	CONST PAGE_REFERRAL = 'referrer';
	CONST CHECKOUT = 'checkout';
	CONST CATALOG_SEARCH = 'searched';
	CONST WISHLIST = 'wishlist';
	CONST UPDATE_WISHLIST = 'update-wishlist';
	CONST REMOVE_WISHLIST = 'remove-wishlist';

    /**
     * Page Options configurations
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => self::ALL_PAGES,
                'label' => __('All Pages')
            ),
            array(
                'value' => self::ADD_PRODUCT,
                'label' => __('Add Product')
            ),
            array(
                'value' => self::DELETE_PRODUCT,
                'label' => __('Delete Product')
            ),
            array(
                'value' => self::UPDATE_PRODUCT,
                'label' => __('Update Product')
            ),
            array(
                'value' => self::PAGE_VISIT,
                'label' => __('Page Visit')
            ),
            array(
                'value' => self::CATEGORY_VIEW,
                'label' => __('Category View')
            ),
            array(
                'value' => self::PRODUCT_VIEW,
                'label' => __('Product View')
            ),
            array(
                'value' => self::CATALOG_SEARCH,
                'label' => __('Search Page')
            ),
            array(
                'value' => self::CREATE_ACCOUNT,
                'label' => __('Create Account')
            ),
            array(
                'value' => self::LOGIN,
                'label' => __('Login')
            ),
            array(
                'value' => self::LOGOUT,
                'label' => __('Logout')
            ),
            array(
                'value' => self::ADDTOCART,
                'label' => __('Add to cart')
            ),
            array(
                'value' => self::UPDATECART,
                'label' => __('Update cart')
            ),
            array(
                'value' => self::REMOVECART,
                'label' => __('Remove Cart')
            ),
            array(
                'value' => self::CHECKOUT,
                'label' => __('Checkout')
            ),
            array(
                'value' => self::BILLING,
                'label' => __('Billing page')
            ),
            array(
                'value' => self::SHIPPING,
                'label' => __('Shipping page')
            ),
            array(
                'value' => self::ORDER_ITEMS,
                'label' => __('Order page')
            ),
            array(
                'value' => self::PAGE_REFERRAL,
                'label' => __('Referrer page')
            ),
	    array (
		'value' => self::WISHLIST,
		'label' => __ ( 'Wishlist page' ) 
	    ),
	    array (
		'value' => self::UPDATE_WISHLIST,
		'label' => __ ( 'Update Wishlist' ) 
	    ),
	    array (
		'value' => self::REMOVE_WISHLIST,
		'label' => __ ( 'Delete Wishlist' ) 
	    )
        );
    }
}
