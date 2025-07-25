<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Contact;
use App\Models\Order;
use App\Models\Slide;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;



class AdminController extends Controller
{
    public function index()
    {
        $orders = Order::orderBy('created_at', 'DESC')->get()->take(10);
        $dashboardDatas = DB::select("Select sum(total) As TotalAmount,
                            sum(if(status='ordered', total, 0)) As TotalOrderedAmount,
                            sum(if(status='delivered', total, 0)) As TotalDeliveredAmount,
                            sum(if(status='canceled', total, 0)) As TotalCanceledAmount,
                            Count(*) as Total,
                            sum(if(status='ordered', 1, 0)) As TotalOrdered,
                            sum(if(status='delivered', 1, 0)) As TotalDelivered,
                            sum(if(status='canceled', 1, 0)) As TotalCanceled
                            From Orders
                        ");

        $monthlyDatas = DB::select("SELECT M.id As MonthNo, M.name As MonthName, 
                        IFNULL(D.TotalAmount,0) As TotalAmount,
                        IFNULL(D.TotalOrderedAmount,0) As TotalOrderedAmount,
                        IFNULL(D.TotalDeliveredAmount,0) As TotalDeliveredAmount,
                        IFNULL(D.TotalCanceledAmount,0) As TotalCanceledAmount FROM month_names M
                        LEFT JOIN (Select DATE_FORMAT(created_at, '%b') As MonthName,
                        MONTH(created_at) As MonthNo,
                        sum(total) As TotalAmount,
                        sum(if(status='ordered',total,0)) As TotalOrderedAmount,
                        sum(if(status='delivered',total,0)) As TotalDeliveredAmount,
                        sum(if(status='canceled',total,0)) As TotalCanceledAmount
                        From Orders WHERE YEAR(created_at)=YEAR(NOW()) GROUP BY YEAR(created_at), MONTH(created_at) , DATE_FORMAT(created_at, '%b')
                        Order By MONTH(created_at)) D On D.monthNo=M.id");

        $AmountM = implode(',', collect($monthlyDatas)->pluck('TotalAmount')->toArray());
        $OrderedAmountM = implode(',', collect($monthlyDatas)->pluck('TotalOrderedAmount')->toArray());
        $DeliveredAmountM = implode(',', collect($monthlyDatas)->pluck('TotalDeliveredAmount')->toArray());
        $CanceledAmountM = implode(',', collect($monthlyDatas)->pluck('TotalCanceledAmount')->toArray());

        $TotalAmount = collect($monthlyDatas)->sum('TotalAmount');
        $TotalOrderedAmount = collect($monthlyDatas)->sum('TotalOrderedAmount');
        $TotalDeliveredAmount = collect($monthlyDatas)->sum('TotalDeliveredAmount');
        $TotalCanceledAmount = collect($monthlyDatas)->sum('TotalCanceledAmount');
        

        return view('admin.index', compact(
            'orders', 
            'dashboardDatas', 
            'AmountM',
            'OrderedAmountM', 
            'DeliveredAmountM', 
            'CanceledAmountM',
            'TotalAmount',
            'TotalOrderedAmount',
            'TotalDeliveredAmount',
            'TotalCanceledAmount'
        ));
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

        return view('admin.product_add', compact('categories', 'brands'));
    }


    public function storeProduct(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            // 'sale_price' => 'required',
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
        // $product->image                 =   $request->image;
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



    public function productEdit($id)
    {
        $data['product'] = Product::find($id);

        $data['categories'] = Category::select('id', 'name')->orderBy('name')->get();

        $data['brands'] = Brand::select('id', 'name')->orderBy('name')->get();

        return view('admin.product_edit', $data);

    }


    public function productUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,'.$request->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            // 'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'category_id' => 'required',
            'brand_id' => 'required'
        ]);

        $product = Product::find($request->id);

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
        // $product->image                 =   $request->image;
        $product->category_id           =   $request->category_id;
        $product->brand_id              =   $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            if(File::exists(public_path('uploads/products') . '/' . $product->image))
            {
                File::delete(public_path('uploads/products') . '/' . $product->image);
            }

            if(File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image))
            {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
            }
            
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }


        $galleryArray = array();
        $galleryImages = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            foreach(explode(',', $product->images) as $oldFile)
            {
                if(File::exists(public_path('uploads/products') . '/' . $oldFile))
                {
                    File::delete(public_path('uploads/products') . '/' . $oldFile);
                }

                if(File::exists(public_path('uploads/products/thumbnails') . '/' . $oldFile))
                {
                    File::delete(public_path('uploads/products/thumbnails') . '/' . $oldFile);
                }
            }

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

            $product->images = $galleryImages;

        }
        

        $product->save();

        return redirect()->route('admin.products')->with('status', 'Product Updated Successfully!');

    }


    public function productDelete($id)
    {
        $product = Product::find($id);

        if(File::exists(public_path('uploads/products') . '/' . $product->image))
        {
            File::delete(public_path('uploads/products') . '/' . $product->image); 
        }

        if(File::exists(public_path('uploads/products/thumbnails') . '/' . $product->image))
        {
            File::delete(public_path('uploads/products/thumbnails') . '/' . $product->image);
        }

        foreach(explode(',', $product->images) as $oldFile)
        {
            if(File::exists(public_path('uploads/products') . '/' . $oldFile))
            {
                File::delete(public_path('uploads/products') . '/' . $oldFile);
            }

            if(File::exists(public_path('uploads/products/thumbnails') . '/' . $oldFile))
            {
                File::delete(public_path('uploads/products/thumbnails') . '/' . $oldFile);
            }
        }

        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Product Deleted Successfully!');
    }


    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }


    public function couponAdd()
    {
        return view('admin.coupon_add');
    }


    public function couponStore(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        
        return redirect()->route('admin.coupons')->with('status', 'Coupon has been added successfully!');
    }


    public function couponEdit($id)
    {
        $coupon = Coupon::find($id);

        return view('admin.coupon_edit', compact('coupon'));
    }


    public function couponUpdate(Request $request)
    {
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = Coupon::find($request->id);
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        
        return redirect()->route('admin.coupons')->with('status', 'Coupon has been updated successfully!');
    }


    public function couponDelete($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status', 'Coupon has been deleted successfully!');
    }


    public function orders()
    {
       $orders = Order::orderBy('created_at', 'DESC')->paginate(12);
       return view('admin.orders', compact('orders'));
    }


    public function orderDetails($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id', $order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id)->first();
        return view('admin.order_details', compact('order', 'orderItems', 'transaction'));
    }


    public function updateOrderStatus(Request $request)
    {
        $order = Order::find($request->order_id);
        $order->status = $request->order_status;
        if($request->order_status == 'delivered')
        {
            $order->delivered_date = Carbon::now();
        }
        elseif($request->order_status == 'canceled')
        {
            $order->canceled_date = Carbon::now();
        }

        $order->save();

        if($request->order_status == 'delivered')
        {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            $transaction->status = 'approved';
            $transaction->save();
        }
        // elseif($request->order_status == 'canceled')
        // {
        //     $transaction = Transaction::where('order_id', $request->order_id)->first();
        //     $transaction->status = 'declined';
        //     $transaction->save();
        // }

        return back()->with('status', 'Order Status changed successfulyl!');
    }


    public function slides()
    {
        $slides = Slide::orderBy('id', 'DESC')->paginate(12);
        return view('admin.slides', compact('slides'));
    }


    public function slideAdd()
    {
        return view('admin.slide_add');
    }


    public function slideStore(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg|max:3096'
        ]);

        $slide = new Slide();
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extension = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extension;
        $this->generateSlideThumbnailsImage($image, $file_name);
        $slide->image = $file_name;

        $slide->save();

        return redirect()->route('admin.slides')->with('status', 'Slide added successfully!');

    }


    public function generateSlideThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400, 690,"top");
        $img->resize(400, 690, function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath . '/' . $imageName);
    }


    public function slideEdit($id)
    {
        $slide = Slide::find($id);
        return view('admin.slide_edit', compact('slide'));
    }


    public function slideUpdate(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'mimes:png,jpg,jpeg|max:3096'
        ]);

        $slide = Slide::find($request->id);
        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/slides') . '/' . $slide->image))
            {
                File::delete(public_path('uploads/slides') . '/' . $slide->image);
            }
            $image = $request->file('image');
            $file_extension = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extension;
            $this->generateSlideThumbnailsImage($image, $file_name);
            $slide->image = $file_name;
        }

        $slide->save();

        return redirect()->route('admin.slides')->with('status', 'Slide updated successfully!');

    }


    public function slideDelete($id)
    {
        $slide = Slide::find($id);

        if(File::exists(public_path('uploads/slides') . '/' . $slide->image))
        {
            File::delete(public_path('uploads/slides') . '/' . $slide->image);
        }
        $slide->delete();

        return redirect()->route('admin.slides')->with('status', 'Slide deleted Successfully!');
    }


    public function contacts()
    {
        $contacts = Contact::orderBy('created_at', 'DESC')->paginate(10);

        return view('admin.contacts', compact('contacts'));
    }


    public function contactDelete($id)
    {
        $contact = Contact::find($id)->delete();

        return redirect()->back()->with('status', 'Contact deleted successfully!');
    }


    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = Product::where('name', 'LIKE', "%{$query}%")->get()->take(8);
        return response()->json($results);
    }










}
