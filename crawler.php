#!/usr/bin/env php
<?php
function crawl($url,$dept = 2) {
    if($dept > 0){
        $html = file_get_contents($url);
        preg_match_all('~<a.*?href=:(.*?)".*?>',$html,$matches);
        foreach ($matches[1] as $newurl)
        {
            crawl($newurl, $dept-1);
        }
        file_put_contents('results.html',"\n\n".$html."\n\n",FILE_APPEND);
    }
}
crawl('https://www.php.net/manual/en/function.file-put-contents.php',2);
