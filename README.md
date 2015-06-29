# docbuilder

docbuilder is a simple tool that allows you to generate PDF documentation from markdown & css.
This tool generates pdfs through intermediary HTML that can be styled with custom CSS rules (see usage).

## Requirements

* PHP >= 5.4
* mbstring
* gd (for image support)
* zlib

## Installation

```shell
$ composer global require vectorface/docbuilder
```

## Usage

```shell
$ docbuilder -h
Usage: docbuilder [OPTION]... [INFILE] [OUTFILE]
Converts Markdown files to pdf.
Options:
  -c, --css=FILE   provide css file for styling (overrides default styling)
  -p, --printhtml  output intermediate html file
  -h, --help       display this help and exit
  -v, --version    output version number and exit
```

## Build Phar

A standalone .phar archive can be built with [box](http://box-project.org/ "Box Project").