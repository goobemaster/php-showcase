<?php

namespace News;

use News\FeedSource as FeedSource;

final class RSSReader {
    /** @var string */
    private $url;

    public function setUrl($url): bool {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) return false;
        $this->url = $url;
        return true;
    }

    public function getStoriesAndMeta($knownStories) {
        $xmlContent = file_get_contents($this->url);
        if ($xmlContent === false) return false;
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) return false;

        $root = $xml->{'channel'};
        return array_merge($this->getMeta($root), ['stories' => $this->getStories($root, $knownStories)]);
    }

    private function getMeta($root) {
        return [
            'title' => (string) $root->{'title'},
            'link' => (string) $root->{'link'},
            'description' => (string) $root->{'description'},
            'published' => (string) $root->{'pubDate'}
        ];
    }

    private function getStories($root, $knownStories) {
        $stories = [];
        foreach ($root->{'item'} as $item) {
            if (in_array($item->{'guid'}, $knownStories)) continue;
            $stories[] = new FeedSource(
                (string) $item->{'title'},
                (string) $item->{'link'},
                (string) $item->{'description'},                                
                (string) $item->{'published'},
                (string) $item->{'guid'}
            );
        }
        return $stories;
    }
}