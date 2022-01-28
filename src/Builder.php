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
    private $markdown;
    private $css;
    private $filename;
    private $printhtml;
    private $output;

    /** @var bool */
    private $toc = false;

    private $header;
    private $footer;

    public function __construct($markdown, $css, $printhtml, $output)
    {
        $this->markdown = @file_get_contents($markdown);
        $this->css = @file_get_contents($css);
        $this->filename = $markdown;
        $this->printhtml = $printhtml;
        $this->output = $output;

        if ($this->markdown === false) {
            echo "docbuilder: markdown file does not exist: ".$markdown."\n";
            exit(1);
        }

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

    public function withHeader(?string $header): self
    {
        $this->header = $header;
        return $this;
    }

    public function withFooter(?string $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    /**
     * Build the PDF from the user provided data.
     * Exists with status 0 upon completion.
     */
    public function buildPDF()
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new PreprocessorExtension());
        if ($this->toc) {
            $environment->addExtension(new HeadingPermalinkExtension());
            $environment->addExtension(new TableOfContentsExtension());
        }
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer());
        $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer());

        $converter = new MarkdownConverter($environment);
        $mpdf = new Mpdf();

        $html = "<!doctype html><html><head><style>".$this->css."</style></head><body>";
        $html .= $converter->convertToHtml($this->markdown);
        $html .= "</body></html>";

        $mpdf->WriteHTML($html);
        if ($this->header) {
            $mpdf->SetHTMLHeader(@file_get_contents($this->header));
        }
        if ($this->footer) {
            $mpdf->SetHTMLFooter(@file_get_contents($this->footer));
        }
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
}
