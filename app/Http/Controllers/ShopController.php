<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $size = $request->query('size') ? $request->query('size') : 12;

        $orderColumn = "";

        $o_order = "";

        $order = $request->query('order') ? $request->query('order') : -1;

        $filterBrands = $request->query('brands');

        $filterCategories = $request->query('categories');

        $minPrice = $request->query('min') ? $request->query('min') : 1;
        $maxPrice = $request->query('max') ? $request->query('max') : 5000;

        switch($order)
        {
            case 1:
                $orderColumn = 'created_at';
                $o_order = 'DESC';
                break;
            case 2:
                $orderColumn = 'created_at';
                $o_order = 'ASC';
                break;
            case 3:
                $orderColumn = 'sale_price';
                $o_order = 'ASC';
                break;
            case 4:
                $orderColumn = 'sale_price';
                $o_order = 'DESC';
                break;
            default:
                $orderColumn = 'id';
                $o_order = 'DESC';
        }

        $brands = Brand::orderBy('name', 'ASC')->get();

        $categories = Category::orderBy('name', 'ASC')->get();

        $products = Product::where(function($query) use($filterBrands){
                $query->whereIn('brand_id', explode(',',$filterBrands))->orWhereRaw("'".$filterBrands."'=''");
            })
            ->where(function($query) use($filterCategories){
                $query->whereIn('category_id', explode(',',$filterCategories))->orWhereRaw("'".$filterCategories."'=''");
            })

            ->where(function($query) use($minPrice, $maxPrice){
                $query->whereBetween('regular_price', [$minPrice, $maxPrice])
                        ->orWhereBetween('sale_price', [$minPrice, $maxPrice]);
            })
            ->orderBy($orderColumn, $o_order)->paginate($size);

        return view('shop', compact('products', 'size', 'order', 'brands', 'filterBrands', 'categories', 'filterCategories', 'minPrice', 'maxPrice'));
    }



    public function productDetails($product_slug)
    {
        $product = Product::where('slug', $product_slug)->first();

        $rproducts = Product::where('slug', '<>', $product_slug)->get()->take(8);

        return view('details', compact('product', 'rproducts'));
    }

}
