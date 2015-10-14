<?php

namespace Vectorface\DocBuilder;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;
use Vectorface\DocBuilder\Builder;

/**
 * Class BuilderApp
 * Contains runtime functions for the builder tool0
 */
class BuilderApp
{
    /**
     * Function processes arguments and runs the builder (or exits)
     * from the provided data.
     */
    public function run()
    {
        $getopt = new Getopt(array(
            new Option('c', 'css', Getopt::REQUIRED_ARGUMENT),
            new Option('p', 'printhtml', Getopt::OPTIONAL_ARGUMENT),
            new Option('h', 'help'),
            new Option('v', 'version')
        ));

        /* Get option values */
        try {
            $getopt->parse();

            if ($getopt['version']) {
                echo "docbuilder v1.0.0\n";
                exit(0);
            }

            if ($getopt['help']) {
                $this->showUsage(0);
            }

            if ($getopt['css']) {
                $css = $getopt['css'];
            } else {
                /* Use default styling */
                $css = __DIR__.'/../defaults/style.css';
            }

            $markdown = $getopt->getOperand(0);

            if (empty($markdown)) {
                echo "docbuilder: no markdown file provided\n\n";
                $this->showUsage(1);
            }

            $output = $getopt->getOperand(1);

            if (empty($output)) {
                $output = getcwd().'/'.preg_replace('/.[^.]*$/', '', $markdown).".pdf";
            }

            if ($getopt['printhtml'] === 1) {
                $printhtml = dirname($output) . '/' . basename($output, ".pdf") . ".html";
            } elseif (!empty($getopt['printhtml']) && is_string($getopt['printhtml'])) {
                $printhtml = $getopt['printhtml'];
            } else {
                $printhtml = false;
            }

            /* Run the builder tool */
            $builder = new Builder($markdown, $css, $printhtml, $output);
            $builder->buildPDF();
        } catch (\Exception $e) {
            echo "docbuilder: ".$e->getMessage()."\n";
            exit(1);
        }
    }

    /**
     * Helper function that shows tool usage and exits with the provided code
     * @param $code int return code for showUsage() to exit with
     */
    private function showUsage($code)
    {
        echo "Usage: docbuilder [OPTION]... [INFILE] [OUTFILE]\n";
        echo "Converts Markdown files to pdf.\n\n";
        echo "Options:\n";
        echo "  -c, --css=FILE   provide css file for styling (overrides default styling)\n";
        echo "  -p, --printhtml  output intermediate html file (accepts optional filename argument)\n";
        echo "  -h, --help       display this help and exit\n";
        echo "  -v, --version    output version number and exit\n";

        exit($code);
    }
}
