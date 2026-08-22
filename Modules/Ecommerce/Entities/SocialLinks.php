<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class SocialLinks extends Model
{
    protected $fillable = [
    	'title','link','icon','order'
    ];
}
