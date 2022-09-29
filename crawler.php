#!/usr/bin/env php
<?php


function getLinks (string $page): void
{
    $page = file_get_contents($page);
   if ((preg_match_all('/<a.*?href="(?P<links>[^"]*)".*?\/?>/mi', $page, $regs, PREG_PATTERN_ORDER))){
       foreach ($regs[0] as $v) ;
   }
    var_dump($v);


}
getLinks($argv[1]);


//function getCountOfTags(string $url): void
//{
//    $html = file_get_contents($url);
//
//    preg_match_all('/<img/',$html,$matches);
//    if ($matches)
//    file_put_contents('results.html',"\n\n".count($matches[0])."\n\n",FILE_APPEND);
//}
//getCountOfTags($argv[1]);
