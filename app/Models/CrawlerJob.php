<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrawlerJob extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['entry_point', 'start_time', 'end_time', 'status', 'response_code'];

   /**
    * return webpages
    *
    * @return Webpage[]
    */
    public function webpages()
    {
        return $this->hasMany(Webpage::class, 'crawler_id');
    }

    /**
     * return number of pages crawled
     * 
     * @return int
     */
    public function webpagesCount()
    {
        return $this->webpages()->count();
    }
}
