<?php

namespace App\Http\Controllers;

use App\Models\CrawlerJob;
use App\Models\Webpage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CrawlerJobController extends Controller
{
    //

    /**
     * Start crawler
     * 
     * @param Request $request
     */
    public function initCrawler( Request $request )
    {
        Log::info($request->input('url'), ['Init Crawler URL']);
        $url = $request->input('url');
        if ( !$url ) {
            return ['success' => false];
        }

        $crawledUrls = [rtrim($url, '/')];

        // Crawl entry point
        $crawlerJob = new CrawlerJob();
        $crawlerJob->entry_point = $url;
        $crawlerJob->start_time = microtime(true);
        $context = stream_context_create(['http' => ['ignore_errors' => true]]);
        $content = file_get_contents($url, false, $context);
        $crawlerJob->response_code = $http_response_header[0];
        $crawlerJob->end_time = microtime(true);

        if ( !str_contains($crawlerJob->response_code, '200 OK') ) {
            $crawlerJob->status = 0;
            $crawlerJob->save();
            
            return back()->with('error', 'Failed to Crawl');
        }

        $crawlerJob->end_time = microtime(true);
        $crawlerJob->status = 1;
        $timeLoad = $crawlerJob->end_time - $crawlerJob->start_time;
        $crawlerJob->save();

        $page = [
            'crawler_id' => $crawlerJob->id,
            'url' => $url,
            'content' => $content,
            'loads' => $timeLoad,
            'level' => 0,
            'response_code' => $http_response_header[0]
        ];

        $firstPage = Webpage::create($page);
        $firstPage = Webpage::find($firstPage->id);
        $internalLinks = $firstPage->getInternalLinks();
        $internalLinksCount = count($internalLinks);

        for ($i=0; $i < 6; $i++) { 
            if ( $i > $internalLinksCount ) break;
            $url = rtrim($internalLinks[$i], '/');
            if ( !in_array( $url, $crawledUrls) ) {
                $context = stream_context_create(['http' => ['ignore_errors' => true]]);
                $startTime = microtime(true);
                $content = file_get_contents($url, false, $context);
                $endTime = microtime(true);
                $response_code = $http_response_header[0];
                $loads = $endTime - $startTime;
                $level = 1;
                $crawledUrls[] = $url;

                $page = Webpage::create([
                    'crawler_id' => $crawlerJob->id,
                    'url' => $internalLinks[$i],
                    'content' => $content,
                    'loads' => $loads,
                    'level' => $level,
                    'response_code' => $response_code
                ]);
            }


        }

        return redirect()->route('results', [$crawlerJob]);
    }

    /**
     * Show Results
     * 
     * @param Request $request
     * @param int $id
     */
    public function showResult( Request $request, $id )
    {
        Log::info($id, ['Id of crawljob']);

        $crawler = CrawlerJob::find($id);
        $uniqueExternalLinks = 0;
        $uniqueInternalLinks = 0;
        $uniqueImages = 0;
        $pageLoad = [];
        $wordCount = [];
        $titleCount = [];
        $pagesCrawled = [];

        foreach ($crawler->webpages as $webpage) {
            if ( $webpage->level === 0 ) { // entry page
                $uniqueExternalLinks =  count($webpage->getExternalLinks());
                $uniqueInternalLinks = count($webpage->getInternalLinks());
                $uniqueImages = count($webpage->uniqueImages());
            }
            $pagesCrawled[$webpage->url] = $webpage->response_code;
            $pageLoad[] = $webpage->loads;
            $wordCount[] = $webpage->totalWordCount();
            $titleCount[] = strlen($webpage->getTitle());
        }
        $data = [
            'totalPagesCrawled' => $crawler->webpagesCount(),
            'uniqueExternalLinks' => $uniqueExternalLinks,
            'uniqueInternalLinks' => $uniqueInternalLinks,
            'pageLoad' => array_sum($pageLoad) / count($pageLoad),
            'wordCount' => array_sum($wordCount) / count($wordCount),
            'averageTitleLength' => array_sum($titleCount) / count($titleCount),
            'uniqueImages' => $uniqueImages,
            'pagesCrawled' => $pagesCrawled
        ];

        Log::info($data, ['Results Data']);

        return view('results', $data);

    }
    
}
