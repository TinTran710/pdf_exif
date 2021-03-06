<?php

use TinTran\PDFLibrary\PDFLibrary;
use PHPUnit\Framework\TestCase;

class SampleTest extends TestCase {

	private $file = "resources/hello.pdf";
	private $p;

	public function testTitleProperty() {
		$expectedFile = new PDFLibrary($this->file);
		$expectedResult = $expectedFile->get_pdf_title();

		$data = $this->getBasicData();
		$title = $data['Title'];
		$this->assertEquals($expectedResult, $title);
	}

	public function testAuthorProperty() {
		$expectedFile = new PDFLibrary($this->file);
		$expectedResult = $expectedFile->get_pdf_author();

		$data = $this->getBasicData();
		$author = $data['Author'];
		$this->assertEquals($expectedResult, $author);
	}	

	/*------------------------ Helper function below ----------------------------*/

	public function getBasicData() {
		$data = Array();
		$this->p = new PDFlib();	
        $doc = $this->p->open_pdi_document(realpath($this->file), "");
        if ($doc == 0) {
    		printf("Error: " . $this->p->get_errmsg());
        }

        $count = $this->p->pcos_get_number($doc, "length:/Info");
        for ($i=0; $i < $count; $i++) {
    		$info = "type:/Info[" . $i . "]";
    		$objtype = $this->p->pcos_get_string($doc, $info);

    		$info = "/Info[" . $i . "].key";
    		$key = $this->p->pcos_get_string($doc, $info);
    		$len = 12 - strlen($key);
    		while ($len-- > 0) print(" ");

    		// $info entries can be stored as string or name objects
    		if ($objtype == "name" || $objtype == "string") {
    		    $info = "/Info[" . $i . "]";
    		    $data[$key] = $this->p->pcos_get_string($doc,$info);
            } else {
    		    $info = "type:/Info[" . $i . "]";
    		    $data[$key] = $this->p->pcos_get_string($doc,$info);
    		}
        }
        return $data;
	}

}


