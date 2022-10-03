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
        print('Please enter a valid url.' . PHP_EOL);

        die();
    }

    $urlParts = parse_url($url);

    $result = [];
    $result = crawler($url, $urlParts['host'] ?? '', $result);

    $endTime = microtime(true) - $start;

    print('Success.'. PHP_EOL);
    print("Total time: $endTime" . PHP_EOL);
}

function crawler(string $url, string $host, array &$result)
{
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

        $result[$url] = $countImages;

        foreach ($links as $link) {
            $hrefLink = $link->getAttribute('href');
            $urlParts = parse_url($hrefLink);

            if (filter_var($hrefLink, FILTER_VALIDATE_URL) && isset($urlParts['host']) ? $urlParts['host'] : '' === $host && array_key_exists($hrefLink, $result) === false) {
                print("Parse ulr in eac. $hrefLink" . PHP_EOL);
                $html = @file_get_contents($hrefLink, false, stream_context_create($headers));
                if ($html) {
                    [$countImages] = parser($html, $dom);
                    $result[$hrefLink] = $countImages;
                }
            }
        }
    }
    return $result;
}

function parser($html, $dom): array
{
    @$dom->loadHTML($html);
    $dom->preserveWhiteSpace = false;
    $countImages = $dom->getElementsByTagName('img')->count();
    $links = $dom->getElementsByTagName('a');

    return [$countImages, $links];
}
run($argv);
