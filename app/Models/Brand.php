<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;


    public function products()
    {
        return $this->hasMany(Product::class);
    }

    

    public function getBrandImage()
    {
        if(!empty($this->image) && file_exists('uploads/brands/'.$this->image))
        {
            return url('uploads/brands/'.$this->image);
        }
        else
        {
            return '';
        }

    }


    

    

}
