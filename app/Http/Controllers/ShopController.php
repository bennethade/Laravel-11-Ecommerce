<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(12);

        return view('shop', compact('products'));
    }

    public function productDetails($product_slug)
    {
        $product = Product::where('slug', $product_slug)->first();

        $rproducts = Product::where('slug', '<>', $product_slug)->get()->take(8);

        return view('details', compact('product', 'rproducts'));
    }

}
