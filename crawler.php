#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

/**
 * @param array $arguments
 * @return void
 */
function run(array $arguments): void
{
    $start = microtime(true);

    print('Start searching images tags.' . PHP_EOL);

    // This validation url.
    if (isset($arguments[1]) && filter_var($arguments[1], FILTER_VALIDATE_URL)) {
        $url = $arguments[1];
    } else {
        print('Incorrect url.' . PHP_EOL);

        die();
    }

    $urlParts = parse_url($url);

    $result = [];
    crawler($url, $urlParts['host'] ?? '', $result);

    uasort($result, function ($first, $second) {
        return $first <=> $second;
    });

    $endTime = microtime(true) - $start;
    makeReport($result, $endTime);

    print('Success.'. PHP_EOL);
    print("Total time: $endTime" . PHP_EOL);
}

/**
 * @param string $url
 * @param string $host
 * @param array $result
 * @return array
 */
function crawler(string $url, string $host, array &$result): array
{
     $startTime = microtime(true);

    $headers = [
        'ssl' => [
            "allow_self_signed" => true,
            "verify_peer" => false,
            "verify_peer_name" => false,
        ]
    ];
    $pageData = file_get_contents($url, false, stream_context_create($headers));

    if (empty($pageData) === false) {
        $dom = new DomDocument();

        [$countImages, $links] = parser($pageData, $dom);

        $result[$url]['count_images'] = $countImages;

        foreach ($links as $link) {
            $hrefLink = $link->getAttribute('href');
            $urlParts = parse_url($hrefLink);

            if (array_key_exists($hrefLink, $result)) {
                continue;
            }

            if (filter_var($hrefLink, FILTER_VALIDATE_URL) && ($urlParts['host'] ?? '') === $host) {
                print("Parse ulr in eac. $hrefLink" . PHP_EOL);

                $html = @file_get_contents($hrefLink, false, stream_context_create($headers));
                if ($html) {
                    crawler($hrefLink, $host, $result);
                }
            }
        }
    }

    $endTime = microtime(true) - $startTime;
    $result[$url]['total_time'] = $endTime;

    return $result;
}

/**
 * @param string $html
 * @param DOMDocument $dom
 * @return array
 */
function parser(string $html, DOMDocument $dom): array
{
    @$dom->loadHTML($html);
    $dom->preserveWhiteSpace = false;
    $countImages = $dom->getElementsByTagName('img')->count();
    $links = $dom->getElementsByTagName('a');

    return [$countImages, $links];
}

/**
 * @param array $result
 * @param float $totalTime
 * @return void
 */
function makeReport(array $result, float $totalTime): void
{
    $html = '<!DOCTYPE html>
      <html lang="uk">
            <head>  
                <title>Report</title>
            </head>
            <style>
                table, th, tr, td {
                   border: 1px solid black;
                   border-collapse: collapse;
                }
            </style>
        <body>
        <table>
                <tbody>
                <tr>
                        <td>Number of link</td>
                        <td>Link</td>
                        <td>Count of images</td>
                        <td>Total seconds</td>
                    </tr>
	            ';

    $i = 1;
    foreach ($result as $key => $item) {
        $html .= "<tr>
                        <td>" . $i++ ."</td>
                        <td>" . $key. "</td>
                        <td>" . $item['count_images'] ."</td>
                        <td>" . round($item['total_time'], 2) ."</td>
                    </tr>
	            ";
    }

    $html .= "<tr>
                        <td colspan='4'>Total time: $totalTime</td>
                    </tr>
                </tbody>
            </table>
         </body>
      </html>";

    file_put_contents('./Reports/report_'. date('Y-m-d_H:i:s') . '.html',  $html);
}

run($argv);
