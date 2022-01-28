<?php

declare(strict_types=1);

namespace Vectorface\DocBuilder\CommonMark;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Extension\ExtensionInterface;

final class PreprocessorExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        $environment->addEventListener(DocumentPreParsedEvent::class, new PreprocessorListener());
    }
}
