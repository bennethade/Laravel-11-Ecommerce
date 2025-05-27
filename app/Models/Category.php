<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;


    public function products()
    {
        return $this->hasMany(Product::class);
    }



    public function getCategoryImage()
    {
        if(!empty($this->image) && file_exists('uploads/categories/'.$this->image))
        {
            return url('uploads/categories/'.$this->image);
        }
        else
        {
            return '';
        }

    }



}
