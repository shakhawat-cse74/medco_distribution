<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class Sliders extends Model
{
    protected $fillable = [
    	'title','link','image1','image2','image3','order'
    ];
}
