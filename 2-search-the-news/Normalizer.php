<?php

namespace News;

use News\PorterStemmer as PorterStemmer;

final class Normalizer {
    /** @var string */
    private $subject;

    /**
     * @param string $subject
     */
    public function __construct($subject) {
        $this->subject = $subject;
    }

    public function noDiacritics() {
        $this->subject = iconv('UTF-8','ASCII//TRANSLIT', $this->subject);
        return $this;
    }

    public function toLowerCase() {
        $this->subject = strtolower($this->subject);
        return $this;
    }

    public function noPunctuation() {
        $this->subject = preg_replace('#[[:punct:]]#', '', $this->subject);
        return $this;
    }

    public function toStem() {
        $this->subject = PorterStemmer::Stem($this->subject);
        return $this;
    }

    public function get() {
        return $this->subject;
    }
}