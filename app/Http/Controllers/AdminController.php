<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;



class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }


    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }


    public function addBrand()
    {
        return view('admin.brand_add');
    }


    public function brandStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug',
            'image' => 'mimes:png,jpg,jpeg,webp|max:2048'
        ]);

        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);


        if(!empty($request->file('image')))
        {
            $image = $request->file('image');
            $file_extension = $request->file('image')->getClientOriginalExtension();
            // $file_name = Carbon::now()->timestamp;

            $file = $request->file('image');
            $file_name = date('Ymdhis');
            $filename = strtolower($file_name).'.'.$file_extension;

            $file = Image::read($request->file('image'));  
            $file->resize(124, 124);
            $file->save('uploads/brands/'.$filename); 

            $brand->image = $filename; 

        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand Added Successfully!');

    }



    public function brandEdit($id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brand_edit', compact('brand'));
    }


    public function brandUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg,webp|max:2048'
        ]);

        $brand = Brand::find($request->id);

        // $brand = Brand::findOrFail($id);

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);


        if(!empty($request->file('image')))
        {
            if(!empty($brand->getBrandImage()))
            {
                unlink('uploads/brands/' . $brand->image);
            }

            $file_extension = $request->file('image')->getClientOriginalExtension();

            $file = $request->file('image');
            $file_name = date('Ymdhis');
            $filename = strtolower($file_name).'.'.$file_extension;

            $file = Image::read($request->file('image'));  
            $file->resize(124, 124);
            $file->save('uploads/brands/'.$filename); 

            $brand->image = $filename; 
        }

        $brand->save();

        return redirect()->route('admin.brands')->with('status', 'Brand Updated Successfully!');



    }


    public function brandDelete($id)
    {
        $brand = Brand::findOrFail($id);

        if(File::exists(public_path('uploads/brands') . '/' . $brand->image))
        {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }

        $brand->delete();

        return redirect()->route('admin.brands')->with('status', 'Brand Deleted Successfully!!!');
    }


    public function categories()
    {
        $categories = Category::orderBy('id', 'desc')->paginate(10);
        return view('admin.categories', compact('categories'));
    }


    public function addCategory()
    {
        return view('admin.category_add');
    }


    public function categoryStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg,webp|max:2048'
        ]);

        $categories = new Category();
        $categories->name = $request->name;
        $categories->slug = Str::slug($request->name);


        if(!empty($request->file('image')))
        {
            $image = $request->file('image');
            $file_extension = $request->file('image')->getClientOriginalExtension();
            // $file_name = Carbon::now()->timestamp;

            $file = $request->file('image');
            $file_name = date('Ymdhis');
            $filename = strtolower($file_name).'.'.$file_extension;

            $file = Image::read($request->file('image'));  
            $file->resize(124, 124);
            $file->save('uploads/categories/'.$filename); 

            $categories->image = $filename; 

        }

        $categories->save();

        return redirect()->route('admin.categories')->with('status', 'Category Added Successfully!');

    }


    public function categoryEdit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.category_edit', compact('category'));

    }


    public function categoryUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $request->id,
            'image' => 'mimes:png,jpg,jpeg,webp|max:2048'
        ]);

        $category = Category::find($request->id);

        // $category = Brand::findOrFail($id);

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);


        if(!empty($request->file('image')))
        {
            if(!empty($category->getCategoryImage()))
            {
                unlink('uploads/categories/' . $category->image);
            }

            $file_extension = $request->file('image')->getClientOriginalExtension();

            $file = $request->file('image');
            $file_name = date('Ymdhis');
            $filename = strtolower($file_name).'.'.$file_extension;

            $file = Image::read($request->file('image'));  
            $file->resize(124, 124);
            $file->save('uploads/categories/'.$filename); 

            $category->image = $filename; 
        }

        $category->save();

        return redirect()->route('admin.categories')->with('status', 'Category Updated Successfully!');


    }


    public function categoryDelete($id)
    {
        $category = Category::findOrFail($id);

        if(File::exists(public_path('uploads/categories') . '/' . $category->image))
        {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }

        $category->delete();

        return redirect()->route('admin.categories')->with('status', 'Category Deleted Successfully!!!');
    }


    public function products()
    {
        $products = Product::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.products', compact('products'));
    
    }


    public function addProduct()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();

        $brands = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.add_product', compact('categories', 'brands'));
    }


    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        $product = new Product();

        $product->name                  =   $request->name;
        $product->slug                  =   Str::slug($request->name);
        $product->short_description     =   $request->short_description;
        $product->description           =   $request->description;
        $product->regular_price         =   $request->regular_price;
        $product->sale_price            =   $request->sale_price;
        $product->SKU                   =   $request->SKU;
        $product->stock_status          =   $request->stock_status;
        $product->featured              =   $request->featured;
        $product->quantity              =   $request->quantity;
        $product->image                 =   $request->image;
        $product->category_id           =   $request->category_id;
        $product->brand_id              =   $request->brand_id;



        $current_timestamp = Carbon::now()->timestamp;



        if($request->hasFile('image'))
        {
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }

        // if(!empty($request->file('image')))
        // {
        //     $file_extension = $request->file('image')->getClientOriginalExtension();

        //     $file = $request->file('image');
        //     $file_name = date('Ymdhis');
        //     $filename = strtolower($file_name).'.'.$file_extension;

        //     $file = Image::read($request->file('image'));  
        //     $file->resize(540, 689);
        //     $file->save('uploads/products/'.$filename); 
        //     $file->resize(104, 104);
        //     $file->save('uploads/products/thumbnails/'.$filename); 

        //     $product->image = $filename; 

        // }

        $galleryArray = array();
        $galleryImages = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            $allowedfileExtension = ['jpg', 'jpeg', 'png', 'webp'];
            $files = $request->file('images');
            foreach($files as $file)
            {
                $getExtension = $file->getClientOriginalExtension();
                $check = in_array($getExtension, $allowedfileExtension);
                if($check)
                {
                    $getFileName = $current_timestamp . '-' . $counter . '.' . $getExtension;
                    $this->GenerateProductThumbnailImage($file, $getFileName);
                    array_push($galleryArray, $getFileName);
                    $counter = $counter + 1;
                }
            }

            $galleryImages = implode(',', $galleryArray);
        }
        
        $product->images = $galleryImages;

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product Added Successfully!');


    }


    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());

        $img->cover(540, 689, "top");
        
        $img->resize(540, 689, function($constraint){
            $constraint->aspectRation();
        })->save($destinationPath . '/' . $imageName);
        
        $img->resize(104, 104, function($constraint){
            $constraint->aspectRation();
        })->save($destinationPathThumbnail . '/' . $imageName);
    }









}
