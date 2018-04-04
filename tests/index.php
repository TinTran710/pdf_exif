<?php
	require_once "../src/PDFLibrary.php";

	use TinTran\PDFLibrary\PDFLibrary;

	$file = "resources/hello.pdf";
	$read = new PDFLibrary($file);

	var_dump($read->get_pdf_all());
	var_dump($read->get_pdf_title());
	var_dump($read->get_pdf_author());