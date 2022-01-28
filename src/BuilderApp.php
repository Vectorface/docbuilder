<?php

namespace Vectorface\DocBuilder;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Operand;
use Vectorface\DocBuilder\Builder;

/**
 * Class BuilderApp
 * Contains runtime functions for the builder tool0
 */
class BuilderApp
{
    public const VERSION = '2.0.0';
    /**
     * Function processes arguments and runs the builder (or exits)
     * from the provided data.
     */
    public function run()
    {
        $getopt = (new GetOpt())
            ->addOptions([
                new Option('c', 'css', GetOpt::REQUIRED_ARGUMENT),
                new Option('p', 'printhtml', GetOpt::OPTIONAL_ARGUMENT),
                new Option('h', 'help'),
                new Option('t', 'toc'),
                new Option('v', 'version'),
                new Option(null, 'header', GetOpt::REQUIRED_ARGUMENT),
                new Option(null, 'footer', GetOpt::REQUIRED_ARGUMENT),
                new Option(null, 'prepend', GetOpt::REQUIRED_ARGUMENT),
                new Option(null, 'append', GetOpt::REQUIRED_ARGUMENT),
        ])
        ->addOperand(Operand::create('input', Operand::OPTIONAL))
        ->addOperand(Operand::create('output', Operand::OPTIONAL));

        /* Get option values */
        try {
            $getopt->process();
            if ($getopt['version']) {
                echo "docbuilder v" . self::VERSION . "\n";
                exit(0);
            }

            if ($getopt['help']) {
                echo "\ndocbuilder: Convert Markdown files to pdf.\n\n";
                echo $getopt->getHelpText();
                exit(0);
            }

            if ($getopt['css']) {
                $css = $getopt['css'];
            } else {
                /* Use default styling */
                $css = __DIR__.'/../defaults/style.css';
            }

            $input = $getopt->getOperand('input');
            if (empty($input)) {
                echo "docbuilder: no markdown file provided\n\n";
                echo $getopt->getHelpText();
                exit(1);
            }

            $output = $getopt->getOperand('output');
            if (empty($output)) {
                $output = getcwd().'/'.preg_replace('/.[^.]*$/', '', $input).".pdf";
            }

            if ($getopt['printhtml'] === 1) {
                $printhtml = dirname($output) . '/' . basename($output, ".pdf") . ".html";
            } elseif (!empty($getopt['printhtml']) && is_string($getopt['printhtml'])) {
                $printhtml = $getopt['printhtml'];
            } else {
                $printhtml = false;
            }

            /* Run the builder tool */
            $builder = (new Builder($printhtml))
                ->withContent($input)
                ->withCSS($css)
                ->generateTOC((bool)$getopt['toc'])
                ->prepend($getopt['prepend'])
                ->append($getopt['append'])
                ->withPageHeaders($getopt['header'])
                ->withPageFooters($getopt['footer']);
            $builder->outputPDF($output);
        } catch (\Exception $e) {
            echo "docbuilder: ".$e->getMessage()."\n";
            exit(1);
        }
    }
}
