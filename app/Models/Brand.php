<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

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

    public function getProfileDirect()
    {
        if(!empty($this->profile_picture) && file_exists('upload/profile/'.$this->profile_picture))
        {
            return url('upload/profile/'.$this->profile_picture);
        }
        else
        {
            return url('upload/profile/user.jpg');
        }

    }

}
