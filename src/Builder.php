<?php

namespace Vectorface\DocBuilder;

use Mpdf\Mpdf;
use League\CommonMark\CommonMarkConverter;
use Vectorface\DocBuilder\BuilderStyle;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;
use Vectorface\DocBuilder\CommonMark\PreprocessorExtension;

/**
 * Class Builder
 * Contains MD -> PDF conversion and user provided data
 */
class Builder
{
    /** @var string */
    private $css;

    private $printhtml;

    /** @var bool */
    private $toc = false;

    /** @var string */
    private $content = '';

    /** @var string */
    private $header = '';

    /** @var string */
    private $footer = '';

    /** @var string */
    private $prepend = '';

    /** @var string */
    private $append = '';

    /** @var MarkdownConverter */
    private $converter;

    public function __construct($printhtml)
    {
        $this->printhtml = $printhtml;
    }

    /**
     * Tell the underlying Markdown converter to generate a Table of Contents
     *
     * @param bool $toc
     * @return self
     */
    public function generateTOC(bool $toc = true): self
    {
        $this->toc = $toc;
        return $this;
    }

    /**
     * Retrieve CSS stylesheet from the given URI
     *
     * @param string $cssURI
     * @return $this
     */
    public function withCSS(string $cssURI): self
    {
        $this->css = $cssURI ? $this->fetch($cssURI) : '';
        return $this;
    }

    /**
     * Retrieve page header content from the given URI
     *
     * @param string|null $header
     * @return $this
     */
    public function withPageHeaders(?string $headerURI): self
    {
        $this->header = $headerURI ? $this->fetch($headerURI) : '';
        return $this;
    }

    /**
     * Retrieve page footer content from the given URI
     *
     * @param string|null $footer
     * @return $this
     */
    public function withPageFooters(?string $footerURI): self
    {
        $this->footer = $footerURI ? $this->fetch($footerURI) : '';
        return $this;
    }

    /**
     * Retrieve content from the given URI
     *
     * @param string $content
     * @return $this
     */
    public function content(string $contentURI): self
    {
        $this->content = $contentURI ? $this->fetch($contentURI) : '';
        return $this;
    }


    /**
     * Prepend content from the given URI (e.g. a title page)
     *
     * @param string|null $prependURI
     * @return $this
     */
    public function prepend(?string $prependURI): self
    {
        $this->prepend = $prependURI ? $this->fetch($prependURI) : '';
        return $this;
    }

    public function append(?string $appendURI): self
    {
        $this->append = $appendURI ? $this->fetch($appendURI) : '';
        return $this;
    }

    /**
     * Build the PDF from the user provided data.
     * Exists with status 0 upon completion.
     */
    public function outputPDF($output)
    {
        $mpdf = new Mpdf();
        $mpdf->WriteHTML("<!doctype html><html><head><style>".$this->css."</style></head><body>");
        $mpdf->WriteHTML($this->prepend);
        if ($this->header) {
            $mpdf->SetHTMLHeader($this->header);
        }
        if ($this->footer) {
            $mpdf->SetHTMLFooter($this->footer);
        }
        $mpdf->WriteHTML($this->content);
        $mpdf->WriteHTML($this->append);
        $mpdf->WriteHTML("</body></html>");
        $mpdf->Output($output, 'F');

        if ($this->printhtml) {
            $html = "<!doctype html><html><head><style>{$this->css}</style></head><body>{$this->header}{$this->content}{$this->footer}</body></html>";
            if ($this->printhtml === '-') {
                echo $html;
            } else {
                file_put_contents($this->printhtml, $html);
            }
        }

        exit(0);
    }

    private function fetch($uri)
    {
        $content = @file_get_contents($uri);

        if (strtolower(substr($uri, -3)) === '.md') {
            $content = $this->convert($content);
        }

        return $content;
    }

    private function convert(string $markdown): string
    {
        if (!isset($this->converter)) {
            $extensions = [
                new CommonMarkCoreExtension(),
                new PreprocessorExtension(),
            ];
            $config = [];

            if ($this->toc) {
                $extensions[] = new HeadingPermalinkExtension();
                $extensions[] = new TableOfContentsExtension();
                $config['table_of_contents'] = [
                    'position'    => 'placeholder',
                    'placeholder' => '!TOC', // matches style of !VARIABLE and !INCLUDE
                ];
                $config['heading_permalink'] = ['symbol' => '​']; // Zero-width space; don't show
            }
            $environment = new Environment($config);
            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new PreprocessorExtension());
            if ($this->toc) {
                $environment->addExtension(new HeadingPermalinkExtension());
                $environment->addExtension(new TableOfContentsExtension());
            }
            $environment->addRenderer(FencedCode::class, new FencedCodeRenderer());
            $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer());

            $this->converter = new MarkdownConverter($environment);
        }

        return $this->converter->convert($markdown);
    }
}
