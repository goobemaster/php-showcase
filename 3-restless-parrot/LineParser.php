<?php

namespace Mock;

/**
 * A simple parser/iterator for extracting lines from a long string,
 * but its custom built for the Request class so its devoid of any
 * unecessary features.
 */
final class LineParser {
    /** @var string[] */
    private $subject;
    /** @var int */
    private $lineIndex;
    /** @var int */
    private $segment;
    /** @var string[] */
    private $matches;

    /**
     * @param string $subject
     */
    public function __construct($subject, $initialSegment = 0) {
        $this->subject = explode(PHP_EOL, (string) $subject);
        $this->lineIndex = null;
        $this->segment = $initialSegment;
        $this->matches = [];
    }

    /**
     * @return boolean
     */
    public function hasNext(): bool {
        return count($this->subject) > $this->lineIndex;
    }

    /**
     * @return void
     */
    public function next(): void {
        $this->lineIndex === null ? $this->lineIndex = 0 : $this->lineIndex++;
    }

    /**
     * @return void
     */
    public function advanceSegment(): void {
        $this->segment++;
    }

    /**
     * @return int
     */
    public function getSegment() {
        return $this->segment;
    }

    /**
     * @return string
     */
    public function getCurrentLine(): string {
        return isset($this->subject[$this->lineIndex]) ?
            $this->subject[$this->lineIndex] : '';
    }

    /**
     * @param string $pattern
     * @return boolean
     */
    public function matches($pattern): bool {
        return preg_match((string) $pattern, $this->getCurrentLine(), $this->matches) === 1;
    }

    /**
     * @return array
     */
    public function getMatches(): array {
        return $this->matches;
    }

    /**
     * Note: I prefer zero based indexes for matches.
     * 
     * @param int $index
     * @return string
     */
    public function getMatch($index): string {
        return isset($this->matches[$index + 1]) ? $this->matches[$index + 1] : '';
    }
}