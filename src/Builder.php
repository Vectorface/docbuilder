<?php

namespace Vectorface\DocBuilder;

use \mPDF;
use League\CommonMark\CommonMarkConverter;
use Vectorface\DocBuilder\BuilderStyle;

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
        $converter = new CommonMarkConverter();
        $mpdf = new mPDF();

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
