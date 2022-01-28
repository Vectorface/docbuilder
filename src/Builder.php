<?php

namespace Vectorface\DocBuilder;

use Mpdf\Mpdf;
use League\CommonMark\CommonMarkConverter;
use Vectorface\DocBuilder\BuilderStyle;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\MarkdownConverter;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;
use Spatie\CommonMarkHighlighter\IndentedCodeRenderer;

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
     * Build the PDF from the user provided data.
     * Exists with status 0 upon completion.
     */
    public function buildPDF()
    {
        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer());
        $environment->addRenderer(IndentedCode::class, new IndentedCodeRenderer());

        $converter = new MarkdownConverter($environment);
        $mpdf = new Mpdf();

        $html = "<!doctype html><html><head><style>".$this->css."</style></head><body>";
        $html .= $converter->convertToHtml($this->markdown);
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
}
