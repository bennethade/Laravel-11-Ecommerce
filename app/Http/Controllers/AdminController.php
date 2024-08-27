<?php

namespace App\Http\Controllers;

use App\Models\Brand;
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











}
