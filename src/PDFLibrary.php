<?php
/* $Id: starter_pcos.php,v 1.5 2013/02/22 21:39:25 rp Exp $
 *
 * pCOS starter:
 * Dump information from an existing PDF document
 *
 * required software: PDFlib+PDI/PPS 9
 * required data: PDF input file
 */
namespace TinTran\PDFLibrary;

class PDFLibrary {

    private $output = Array();
    private $p;
    
    public function __construct($file, $p) {
        try {
            $this->p = $p;
            # This means we must check return values of load_font() etc.
            $this->p->set_option("errorpolicy=return");
            $this->p->set_option("stringformat=utf8");

            // $this->p->set_option("searchpath={" . $searchpath . "}");

            /* We do not create any output document, so no call to
             * begin_document() is required.
             */

            /* Open the input document */
            $doc = $this->p->open_pdi_document(realpath($file), "");
            if ($doc == 0) {
        		die("Error: " . $this->p->get_errmsg());
            }

            /* --------- general information (always available) */

            $this->pcosmode = $this->p->pcos_get_number($doc, "pcosmode");

            // printf("   File name: %s\n", $this->p->pcos_get_string($doc,"filename"));echo"<br>";
        	$this->output['filename'] = $this->p->pcos_get_string($doc,"filename");

            // printf(" PDF version: %s\n", $this->p->pcos_get_string($doc, "pdfversionstring"));echo"<br>";
        	$this->output['pdfversionstring'] = $this->p->pcos_get_string($doc,"pdfversionstring");

            // printf("  Encryption: %s\n", $this->p->pcos_get_string($doc, "encrypt/description"));echo"<br>";
        	$this->output['encrypt/description'] = $this->p->pcos_get_string($doc,"encrypt/description");

            // printf("   Master pw: %s\n", (($this->p->pcos_get_number($doc, "encrypt/master") != 0) ? "yes":"no"));echo"<br>";
        	$this->output['encrypt/master'] = $this->p->pcos_get_string($doc,"encrypt/master");

            // printf("     User pw: %s\n", (($this->p->pcos_get_number($doc, "encrypt/user") != 0) ? "yes" : "no"));echo"<br>";
        	$this->output['encrypt/user'] = $this->p->pcos_get_string($doc,"encrypt/user");

            // printf("Text copying: %s\n", (($this->p->pcos_get_number($doc, "encrypt/nocopy") != 0) ? "no":"yes"));echo"<br>";
        	$this->output['encrypt/nocopy'] = $this->p->pcos_get_string($doc,"encrypt/nocopy");

            // printf("  Linearized: %s\n\n", (($this->p->pcos_get_number($doc, "linearized") != 0) ? "yes" : "no"));echo"<br>";
        	$this->output['linearized'] = $this->p->pcos_get_string($doc,"linearized");

            if ($this->pcosmode == 0) {
        		printf("Minimum mode: no more information available\n\n");
        		$this->p->delete();
        		exit(0);
            }

            /* --------- more details (requires at least user password) */
            // printf("PDF/X status: %s\n", $this->p->pcos_get_string($doc, "pdfx"));echo"<br>";
            $this->output['pdfx'] = $this->p->pcos_get_string($doc,"pdfx");

            // printf("PDF/A status: %s\n", $this->p->pcos_get_string($doc, "pdfa"));echo"<br>";
            $this->output['pdfa'] = $this->p->pcos_get_string($doc,"pdfa");

            $xfa_present = $this->p->pcos_get_number($doc, "type:/Root/AcroForm/XFA") != 0;
            // printf("    XFA data: %s\n", $xfa_present ? "yes" : "no");echo"<br>";
            $this->output['xfa_present'] = $this->p->pcos_get_number($doc,"type:/Root/AcroForm/XFA");

            // printf("  Tagged PDF: %s\n", (($this->p->pcos_get_number($doc, "tagged") != 0) ? "yes" : "no"));echo"<br>";
            $this->output['tagged'] = $this->p->pcos_get_number($doc,"tagged");

            // printf("No. of pages: %s\n", $this->p->pcos_get_number($doc, "length:pages"));echo"<br>";
            $this->output['pages'] = $this->p->pcos_get_number($doc,"length:pages");

            // printf(" Page 1 size: width=%.3f, height=%.3f\n", $this->p->pcos_get_number($doc, "pages[0]/width"), $this->p->pcos_get_number($doc, "pages[0]/height")); echo"<br>";

            $count = $this->p->pcos_get_number($doc, "length:fonts");
            // printf("No. of fonts: %s\n",  $count);echo"<br>";

            for ($i=0; $i < $count; $i++) {
        		$fonts = "fonts[" . $i . "]/embedded";
        		if ($this->p->pcos_get_number($doc, $fonts) != 0)
        		    // print("embedded ");
        		    print("");
        		else
        		    // print("unembedded ");
        			print("");
        		// echo"<br>";
        		$fonts = "fonts[" . $i . "]/type";
        		// print($this->p->pcos_get_string($doc, $fonts) . " font ");echo"<br>";
        		$fonts = "fonts[" . $i . "]/name";
        		// printf("%s\n", $this->p->pcos_get_string($doc, $fonts));echo"<br>";
            }

            printf("\n");

            $this->plainmetadata = $this->p->pcos_get_number($doc, "encrypt/plainmetadata") != 0;
            
            if ($this->pcosmode == 1 && !$this->plainmetadata && $this->p->pcos_get_number($doc, "encrypt/nocopy") != 0) {
        		print("Restricted mode: no more information available");
        		$this->p->delete();
        		exit(0);
            }

            /* ----- document $info keys and XMP metadata (requires master pw) */

            $count = $this->p->pcos_get_number($doc, "length:/Info");

            for ($i=0; $i < $count; $i++) {
        		$info = "type:/Info[" . $i . "]";
        		$objtype = $this->p->pcos_get_string($doc, $info);

        		$info = "/Info[" . $i . "].key";
        		$key = $this->p->pcos_get_string($doc, $info);
        		$len = 12 - strlen($key);
        		while ($len-- > 0) print(" ");

        		// print($key . ": ");

        		/* $info entries can be stored as string or name objects */
        		if ($objtype == "name" || $objtype == "string") {
        		    $info = "/Info[" . $i . "]";
        		    // printf("'" . $this->p->pcos_get_string($doc, $info) .  "'\n");echo"<br>";
        		    $this->output[$key] = $this->p->pcos_get_string($doc,$info);
                } else {
        		    $info = "type:/Info[" . $i . "]";
        		    // printf("(" . $this->p->pcos_get_string($doc, $info) .  " object)\n");
        		    $this->output[$key] = $this->p->pcos_get_string($doc,$info);
        		}
            }

            // print("\n" . "XMP metadata: ");


            $objtype = $this->p->pcos_get_string($doc, "type:/Root/Metadata");
            if ($objtype == "stream") {
        		$contents = $this->p->pcos_get_stream($doc, "", "/Root/Metadata");
        		$this->output['xmp-metadata'] = $this->p->pcos_get_stream($doc, "", "/Root/Metadata");
        		// print(strlen($contents) . " bytes \n");
        		// printf("");
            } else {
        		// printf("not present");
        		$this->output['xmp-metadata'] = 'not present';
            }

            $this->p->close_pdi_document($doc);

            // var_dump($this->output);

        }
        catch (PDFlibException $e) {
            die("PDFlib exception occurred in starter_pcos sample:\n" .
        	"[" . $e->get_errnum() . "] " . $e->get_apiname() . ": " .
        	$e->get_errmsg() . "\n");
        }
        catch (Exception $e) {
            die($e);
        }

        $this->p = 0;
    }

    public function get_pdf_prop() {
        return $this->output;
    }

    public function get_pdf_title() {
        return $this->output['Title'];
    }

    public function get_pdf_author() {
        return $this->output['Author'];
    }

}

?>