<?php

namespace Vectorface\DocBuilder\CommonMark;

use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Input\MarkdownInput;

class PreprocessorListener
{
    public function __invoke(DocumentPreParsedEvent $event): void
    {
        $event->replaceMarkdown(new MarkdownInput((new Preprocessor())($event->getMarkdown()->getContent())));
    }
}