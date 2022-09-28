#!/usr/bin/env php
<?php
function crawl(string $url, int $dept): void
{
//    if($dept > 0){
        $html = file_get_contents($url);
        preg_match_all('/<img/',$html,$matches);
//            foreach ($matches[1] as $newurl)
//            {
//                crawl($newurl, $dept-1);
//            }
            var_dump($matches);

        file_put_contents('results.html',"\n\n".$matches."\n\n",FILE_APPEND);
//    }
}
crawl($argv[1],1);
