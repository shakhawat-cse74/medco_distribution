<?php

namespace Modules\Ecommerce\Entities;

use Illuminate\Database\Eloquent\Model;

class Widgets extends Model
{
    protected $fillable = [
    	'name','location','order','feature_title','feature_secondary_title','feature_icon','site_info_name','site_info_description','site_info_address','site_info_phone','site_info_email','site_info_hours','newsletter_title','newsletter_text','quick_links_title','quick_links_menu','text_title','text_content',
    ];
}
