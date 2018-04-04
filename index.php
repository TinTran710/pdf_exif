<?php
	require_once "src/PDFLibrary.php";

	use TinTran\PDFLibrary\PDFLibrary;

	$file = "resources/hello.pdf";
	$p = new PDFlib();
	$read = new PDFLibrary($file, $p);

	var_dump($read->get_pdf_prop());
	var_dump($read->get_pdf_title());
	var_dump($read->get_pdf_author());