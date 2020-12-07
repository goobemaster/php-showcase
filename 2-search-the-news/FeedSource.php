<?php

namespace News;

/**
 * A simple POPO (Plain old PHP object) that stores the data of a specific
 * RSS news source.
 * 
 * @see News\FeedManifest
 */
final class FeedSource {
    /** @var string */
    public $title;
    /** @var string */
    public $link;
    /** @var string */
    public $description;
    /** @var int */
    public $published;
    /** @var string */
    public $guid;
    /** FeedSource[] */
    public $items = [];

    /**
     * Undocumented function
     *
     * @param string $title
     * @param string $link
     * @param string $description
     * @param integer $published
     */
    public function __construct($title, $link, $description, $published, $guid, $items = []) {
        $this->title = (string) $title;
        $this->link = (string) $link;
        $this->description = (string) $description;
        $this->published = is_int($published) ? $published : (new \DateTime($published))->getTimestamp();
        $this->guid = $guid;
        $this->items = $items;
    }

    public static function from($obj) {
        return new FeedSource(
            property_exists($obj, 'title') ? $obj->title : '',
            property_exists($obj, 'link') ? $obj->link : '',
            property_exists($obj, 'description') ? $obj->description : '',
            property_exists($obj, 'published') ? $obj->published : '',
            property_exists($obj, 'guid') ? $obj->guid : '',
            property_exists($obj, 'items') ? $obj->items : []
        );
    }
}