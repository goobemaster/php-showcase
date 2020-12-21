<?php

ini_set('display_errors', '0');
require_once('autoload.php');

use News\FeedManifest as FeedManifest;

/**
 * The Feeder is a singleton class, the centerpiece of this micro project.
 * 
 * An instance is fetched upon every page load in index.php.
 * 
 * This in turn kicks off a series of things, from which most notable are:
 * - The feeder looks for cached news, and grabs news as needed from RSS Feeds.
 * - It maintains an internal index to fasten up the serving of search results.
 * - It purges old news
 * 
 * The class is used by index.php to serve up results as well.
 */
final class Feeder {
    /**
     * @var string[]
     * @see Find more at https://blog.feedspot.com/technology_rss_feeds/
     */
    const RSS_FEEDS = [
        'https://www.techmeme.com/feed.xml?x=1',
        'https://feeds.feedburner.com/TechCrunch/',
        'https://www.technologyreview.com/topnews.rss',
        'http://feeds.arstechnica.com/arstechnica/technology-lab',
        'https://www.wired.com/feed/rss',
        'http://rss.nytimes.com/services/xml/rss/nyt/Technology.xml',
        'http://feeds.bbci.co.uk/news/technology/rss.xml'
    ];
    const DATA_DIR = __DIR__ . '/data';
    const UPDATE_INTERVAL_SEC = 900; // 15 minutes
    const PURGE_OLDER_THAN_SEC = 86400; // 1 day

    /** @var Feeder */
    private static $instance;

    /** @var FeedManifest */
    private $manifest;

    public function __construct() {
        $manifest = new FeedManifest(self::DATA_DIR);
        if ($manifest->lastUpdated() < (new Datetime())->getTimestamp() - self::UPDATE_INTERVAL_SEC) {
            $manifest->update(self::RSS_FEEDS, self::PURGE_OLDER_THAN_SEC, self::DATA_DIR);
        }
        $this->manifest = $manifest;
    }

    /**
     * Returns the one and only instance of this class.
     *
     * @return Feeder
     */
    public static function getInstance(): Feeder {
        if (self::$instance === null) self::$instance = new Feeder();
        return self::$instance;
    }

    /**
     * Returns search results for the given query, ordered by relevancy.
     * 
     * For type ahead results, less data is returned to speed up things.
     *
     * @param string $query
     * @return array
     */
    public function getTypeAheadResults($query): array {
        return $this->manifest->getResults($query . ' ', FeedManifest::LESS, self::DATA_DIR);
    }

    /**
     * Returns full search results for the given query, ordered by relevancy.
     *
     * @param string $query
     * @return array
     */
    public function getResults($query): array {
        return $this->manifest->getResults($query . ' ', FeedManifest::MORE, self::DATA_DIR);
    }
}
