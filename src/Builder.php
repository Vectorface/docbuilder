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
    private $css;
    private $filename;
    private $printhtml;
    private $output;

    /** @var bool */
    private $toc = false;

    private $content;
    private $header;
    private $footer;

    /** @var MarkdownConverter */
    private $converter;

    public function __construct($printhtml, $output)
    {
        $this->printhtml = $printhtml;
        $this->output = $output;

        if ($this->css === false) {
            echo "docbuilder: css file does not exist: ".$css."\n";
            exit(1);
        }
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
     * s
     * @param string $cssURI
     * @return $this
     */
    public function withCSS(string $cssURI)
    {
        $this->css = $cssURI ? $this->fetch($cssURI) : '';
        return $this;
    }

    /**
     * Retrieve content from the given URI
     * @param string $content
     * @return $this
     */
    public function withContent(string $contentURI)
    {
        $this->content = $contentURI ? $this->fetch($contentURI) : '';
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
     * Build the PDF from the user provided data.
     * Exists with status 0 upon completion.
     */
    public function buildPDF()
    {
        $mpdf = new Mpdf();

        if ($this->header) {
            $mpdf->SetHTMLHeader($this->header);
        }
        if ($this->footer) {
            $mpdf->SetHTMLFooter($this->footer);
        }

        $html = "<!doctype html><html><head><style>".$this->css."</style></head><body>";
        $html .= $this->content;
        $html .= "</body></html>";

        $mpdf->WriteHTML($html);
        $mpdf->Output($this->output, 'F');

        if ($this->printhtml) {
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
        var_dump("Fetching ", $uri);
        $content = @file_get_contents($uri);

        if (strtolower(substr($uri, -3)) === '.md') {
            $content = $this->convert($content);
        }

        return $content;
    }

    private function convert(string $markdown): string
    {
        if (!isset($this->converter)) {
            $environment = new Environment();
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
