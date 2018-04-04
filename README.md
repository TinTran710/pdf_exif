# Composer package for extracting information from PDF files
This package make use of the PDFlib package to interact and extract information from PDF files

## Requirements
You should have PDFlib installed properly to your system. Refer at this [link](https://www.pdflib.com/download/pdflib-family/pdflib/)

## Installation
The package can be installed via composer:
``` bash
$ composer require tintran/pdf_exif
```

## Usage
Read and extract information from a PDF file
```php
$p = new PDFlib();
$read = new PDFLibrary($file, $p);
var_dump($read->get_pdf_prop());
var_dump($read->get_pdf_title());
var_dump($read->get_pdf_author());
```


