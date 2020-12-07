<?php

namespace News;

use News\FeedSource as FeedSource;
use News\RSSReader as RSSReader;
use News\Indexer as Indexer;

/**
 * The feed manifest is used for storing and retrieving application related
 * data, especially when it comes to RSS feeds. This class is responsible for
 * generating search results as well.
 * 
 * The actual RSS related logic is delegated down the line, as well as the indexing.
 */
final class FeedManifest {
    const FILENAME = 'manifest.json';
    const LESS = 0;
    const MORE = 1;
    const KEY_CREATED = 'creation_date';
    const KEY_LAST_UPDATE = 'last_update';
    const KEY_SOURCES = 'sources';

    /** @var object */
    private $manifest;

    /**
     * Loads the manifest file if it exists, or re-creates the default one as needed.
     * Ultimately a working manifest is going to be available for use.
     *
     * @param string $dataDir path to the directory designated for data storage
     */
    public function __construct($dataDir) {
        $filename = sprintf('%s/%s', $dataDir, self::FILENAME);
        file_exists($filename) ? $this->loadManifest($filename) : $this->createManifest($filename);
    }

    /**
     * Creates the default manifest file.
     *
     * @param string $filename path to the manifest json file
     * @return void
     */
    private function createManifest($filename): void {
        $this->manifest = (object) [
            self::KEY_CREATED => (new \DateTime())->getTimestamp(),
            self::KEY_LAST_UPDATE => 0,
            self::KEY_SOURCES => (object) []
        ];
        file_put_contents($filename, json_encode($this->manifest, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT));
    }

    /**
     * Attempts to load the manifest from a file.
     * 
     * If the file does not exists, or is malformed it re-creates the
     * default one.
     *
     * @param string $filename path to the manifest json file
     * @return void
     */
    private function loadManifest($filename): void {
        $this->manifest = json_decode(file_get_contents($filename));
        if ($this->manifest !== null) return;
        unlink($filename);
        $this->createManifest();
    }

    /**
     * Returns a timestamp of the last update.
     *
     * @return integer unix timestamp of the last update
     */
    public function lastUpdated(): int {
        return $this->manifest->{self::KEY_LAST_UPDATE};
    }

    /**
     * @param string[] $rssFeedUrls
     * @return void
     */
    public function update($rssFeedUrls, $purgeOlderThan, $dataDir): void {
        // Adding new sources (feed urls)
        $knownSources = array_keys(get_object_vars($this->manifest->{self::KEY_SOURCES}));
        foreach (array_diff($rssFeedUrls, $knownSources) as $url) {
            $this->manifest->{self::KEY_SOURCES}->{$url} = (object) [];
        }

        // Fetching the breaking news
        $reader = new RSSReader();
        foreach ($this->manifest->{self::KEY_SOURCES} as $url => &$source) {
            if (!$reader->setUrl($url)) continue; // Malformed url, skipping this source...

            $feedSource = FeedSource::from($source);
            $knownStories = array_map(function ($item) { return $item->guid; }, $feedSource->items);
            $feed = $reader->getStoriesAndMeta($knownStories);
            $feedSource->title = $feed['title'];
            $feedSource->link = $feed['link'];
            $feedSource->description = $feed['description'];
            $feedSource->published = (new \DateTime($feed['published']))->getTimestamp();
            $feedSource->items = array_merge($feedSource->items, $feed['stories']);

            // Purging old stories
            $now = (new \Datetime())->getTimestamp();
            if ((int) $feedSource->published < $now - $purgeOlderThan) {
                // These are really musty, lets mow them all...
                $feedSource->items = [];
            }
            foreach ($feedSource->items as &$item) {
                if ((int) $item->published < $now - $purgeOlderThan) {
                    unset($item);
                }
            }

            $source = $feedSource;
        }

        // We need to re-run the indexer
        Indexer::update($this->manifest->{self::KEY_SOURCES}, $dataDir);

        // And save out the manifest to the json file for persistence
        $this->manifest->{self::KEY_LAST_UPDATE} = (new \DateTime())->getTimestamp();
        $filename = sprintf('%s/%s', $dataDir, self::FILENAME);
        unlink($filename);
        file_put_contents($filename, json_encode($this->manifest, JSON_PRETTY_PRINT));
    }

    /**
     * Generates ranked results for the given query.
     *
     * @param string $query
     * @param int $mode 
     * @return array
     */
    public function getResults($query, $mode, $dataDir): array {
        $results = Indexer::search($query, $dataDir, $this->manifest->{self::KEY_SOURCES});

        foreach ($this->manifest->{self::KEY_SOURCES} as $url => &$source) {
            $feedSource = FeedSource::from($source);
            foreach ($feedSource->items as $item) {
                if (array_key_exists($item->guid, $results)) {
                    if ($mode === FeedManifest::LESS) {
                        $results[$item->guid] = (object) [
                            'title' => $item->title,
                            'link' => $item->link,
                            'published' => $item->published
                        ];
                    } else {
                        $results[$item->guid] = $item;
                        // Descriptions with HTML creates a lot of noise, ruining my Zen...
                        // Though having an image on the side looks nice, so we'll keep them.
                        $results[$item->guid]->description = strip_tags(
                            $item->description,
                            '<img><p><span><strong><i><small><ul><li><ol>'
                        );
                    }
                }
            }
        }

        return $results;
    }
}