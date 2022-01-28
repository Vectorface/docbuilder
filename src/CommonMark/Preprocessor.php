<?php

namespace Vectorface\DocBuilder\CommonMark;

use League\CommonMark\Parser\Cursor;

/**
 * Basic support for preprocessor directives in markdown
 *
 * !include "[uri]" will be replaced by the content at the given URI
 *
 * !include "[uri].md" 3 will also increase '#' header indent level by 3 within the included markdown file
 */
class Preprocessor
{
    const REGEX_INCLUDE = '/^!INCLUDE\s+".*"(\s+[\w\d,\s]+)?$/im';
    const REGEX_HEADER = '/^#/m';

    /**
     * Run the preprocessor, and return the result
     *
     * @param string $markdown
     * @return string
     */
    public function __invoke(string $markdown): string
    {
        $cursor = new Cursor($markdown);

        $preparsed = $cursor->getPreviousText();
        while ($include = $cursor->match(self::REGEX_INCLUDE)) {
            $matchLen = mb_strlen($include, 'UTF-8');
            $preparsed .= mb_substr($cursor->getPreviousText(), 0, -$matchLen);

            $quote1 = mb_strpos($include, '"');
            $quote2 = mb_strrpos($include, '"');
            $args = array_map('trim', explode(",", mb_substr($include, $quote2 + 1)));
            $preparsed .= $this->fetch(mb_substr($include, $quote1 + 1, $quote2 - $quote1 - 1), $args);
        }
        $preparsed .= $cursor->getRemainder();

       return $preparsed;
    }

    /**
     * Fetch an included file, recursively preparsing and header-shifting included markdown files
     *
     * @param string $file
     * @param array $args
     * @return string
     */
    private function fetch(string $file, array $args): string
    {
        $contents = @file_get_contents($file);

        if (substr($file, -3) === ".md") {
            $contents = $this->shiftHeaders((new Preprocessor())($contents), (int)$args[0] ?? 0);
        }

        return $contents;
    }

    /**
     * Shift headers in sub-markdown by a number
     *
     * @param string $markdown
     * @param int $shift
     * @return string
     */
    private function shiftHeaders(string $markdown, int $shift): string
    {
        $cursor = new Cursor($markdown);

        $shifted = $cursor->getPreviousText();
        while ($header = $cursor->match(self::REGEX_HEADER)) {
            $shifted .= $cursor->getPreviousText() . str_repeat('#', $shift);
        }
        $shifted .= $cursor->getRemainder();

        return $shifted;
    }
}