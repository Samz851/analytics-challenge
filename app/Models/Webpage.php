<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use \DOMDocument;

class Webpage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['crawler_id', 'url', 'content', 'loads', 'level', 'response_code'];

    /**
     * Store parsed HTML
     *
     * @var DOMDocument
     */
    public $dom;

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::retrieved(function ($webpage) {
            $DOM = new DOMDocument();
            @$DOM->loadHTML($webpage->content);
            $webpage->dom = $DOM;
        });
    }

    /**
     * Return parent CrawlerJob
     * 
     * @return CrawlerJob
     */
    public function crawlerjob()
    {
        return $this->belongsTo(CrawlerJob::class, 'crawler_id');
    }

    /**
     * Get internal Links Attr
     * 
     * @return array
     */
    public function getInternalLinks()
    {
        return array_unique($this->getLinks()['internal']);
    }

    /**
     * Get external Links Attr
     * 
     * @return array
     */
    public function getExternalLinks()
    {
        return array_unique($this->getLinks()['external']);
    }

    /**
     * Return number of unique images
     * 
     * @return array
     */
    public function uniqueImages()
    {

        $imgs = $this->dom->getElementsByTagName('img');
        $uniqueImages = [];
        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            if ( !in_array($src, $uniqueImages) ) {
                $uniqueImages[] = $src;
            }
        }
        return $uniqueImages;
    }

    /**
     * Return unique links
     * 
     * @return array
     */
    private function getLinks()
    {
        $links = [
            'internal' => [],
            'external' => []
        ];
        // Extract the host name from ur
        $host = parse_url($this->url, PHP_URL_HOST);

        $a = $this->dom->getElementsByTagName('a');
        foreach ($a as $url) {

            // Check if internal or external
            if ( false !== strpos($url->getAttribute('href'), $host) ) {

                $links['internal'][] = $url->getAttribute('href');

            } else if ( 0 === strpos($url->getAttribute('href'), '/') ) {

                $links['internal'][] = 'https://' . $host . $url->getAttribute('href');

            } else if ( 0 !== strpos($url->getAttribute('href'), '#') ) {

                $links['external'][] = $url->getAttribute('href');

            }
        }


        return $links;
    }

    /**
     * Return word count
     * 
     * @return int
     */
    public function totalWordCount()
    {
        $text = strip_tags($this->content);
        return str_word_count($text);
    }

    /**
     * Return title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->dom->getElementsByTagName('title')->item(0)->nodeValue;
    }

}
