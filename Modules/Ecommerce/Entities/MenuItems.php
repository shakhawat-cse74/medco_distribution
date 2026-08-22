<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class MenuItems extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */   
    protected $table = 'menu_items'; 
    protected $fillable = ['title','name','slug','type','target','menu_id','created_at','updated_at'];
}
