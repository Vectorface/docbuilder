# docbuilder

docbuilder is a simple tool that allows you to generate PDF documentation from markdown & css.
This tool generates pdfs through intermediary HTML that can be styled with custom CSS rules (see usage).

## Requirements

* PHP >= 7.4
* mbstring
* gd (for image support)
* zlib

## Installation

```shell
$ git clone https://github.com/Vectorface/docbuilder.git
$ cd docbuilder
$ composer update
```

## Usage

```shell
$ ./bin/docbuilder -h
Usage: docbuilder [OPTION]... [INFILE] [OUTFILE]
Converts Markdown files to pdf.
Options:
  -c, --css=FILE   provide css file for styling (overrides default styling)
  -p, --printhtml  output intermediate html file (accepts optional filename argument)
  -h, --help       display this help and exit
  -v, --version    output version number and exit
```

## Build Phar

A standalone .phar archive can be built with [box](https://github.com/box-project/box2 "Box Project").
