<?php

namespace News;

use News\FeedManifest as FeedManifest;
use News\FeedSource as FeedSource;
use News\Normalizer as Normalizer;

/**
 * A very rudimentary full text indexer and search engine.
 * 
 * As of now on every page load the program hits either of the
 * static methods (update/search), but never both, so we wouldn't
 * gain anything by making this a singleton. Actually that's true
 * for the Feeder as well, but oh well you came here to learn eh? :)
 */
final class Indexer {
    private static $index = [];

    private function __construct() {}

    /**
     * @param string[]FeedSource[] $sources
     * @param string $dataDir
     * @return void
     */
    public static function update(&$sources, $dataDir): void {
        foreach ($sources as $url => $source) {
            $filename = sprintf('%s/%s.index', $dataDir, base64_encode($url));
            unlink($filename); // When we update we must start from scratch!
            self::createOrLoad($url, $dataDir);

            $i = 0;
            $feedSource = FeedSource::from($source);
            foreach ($feedSource->items as $article) {
                foreach (explode(' ', $article->title) as $word) {
                    $wordNormal = (new Normalizer($word))
                    ->noDiacritics()
                    ->toLowerCase()
                    ->noPunctuation()
                    ->toStem()
                    ->get();
                    if (!array_key_exists($wordNormal, self::$index)) {
                        self::$index[$wordNormal] = array();
                    }
                    if (!in_array($article->guid, self::$index[$wordNormal])) {
                        self::$index[$wordNormal][]= $article->guid;
                    }
                }
            }

            self::save($url, $dataDir);
        }
    }

    /**
     * @param string $query
     * @param string $dataDir
     * @param string[]FeedSource[] $sources
     * @return array
     */
    public static function search($query, $dataDir, $sources): array {
        $results = [];
        foreach (explode(' ', $query) as $queryWord) $results[$queryWord] = [];

        // Finding matches in index word by word
        foreach ($sources as $url => $source) {
            self::createOrLoad($url, $dataDir);
            foreach ($results as $queryWord => &$articles) {
                $queryWordNormal = (new Normalizer($queryWord))
                ->noDiacritics()
                ->toLowerCase()
                ->noPunctuation()
                ->toStem()
                ->get();

                if (array_key_exists($queryWordNormal, self::$index)) {
                    foreach (self::$index[$queryWordNormal] as $indexArticleGuid) {
                        if (!in_array($indexArticleGuid, $articles)) {
                            $articles[]= $indexArticleGuid;
                        }
                    }
                }
            }
        }

        // Ranking results
        $wordIndex = count($results);
        $rankedResults = [];
        foreach ($results as $word => $articles) {
            foreach ($articles as $articleGuid) {
                if (!array_key_exists($articleGuid, $rankedResults)) {
                    $rankedResults[$articleGuid] = $wordIndex;
                } else {
                    $rankedResults[$articleGuid] += 1;
                }
            }
            $wordIndex--;
        }
        arsort($rankedResults);

        return $rankedResults;
    }

    /**
     * Loads the index of news originating from a particular news source.
     *
     * @param string $dataDir
     * @return void
     */
    private static function createOrLoad($sourceUrl, $dataDir): void {
        $filename = sprintf('%s/%s.index', $dataDir, base64_encode($sourceUrl));
        self::$index = [];
        if (file_exists($filename)) {
            self::$index = unserialize(file_get_contents($filename));
            if (self::$index === false) self::$index = [];
            return;
        }
        self::save($sourceUrl, $dataDir);
    }

    private static function save($sourceUrl, $dataDir) {
        $filename = sprintf('%s/%s.index', $dataDir, base64_encode($sourceUrl));
        unlink($filename);
        file_put_contents($filename, serialize(self::$index));
    }
}