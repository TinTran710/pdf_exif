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
    private $doc;
    
    public function __construct($file) {
        $this->p = new \PDFlib();
        try {
            // Check return values of load_font() etc.
            $this->p->set_option("errorpolicy=return");
            $this->p->set_option("stringformat=utf8");

            // Open the input document
            $this->doc = $this->p->open_pdi_document(realpath($file), "");
            if ($this->doc == 0) {
        		printf("Error: " . $this->p->get_errmsg());
            } else {
                $this->set_general_info();
                $this->set_detail_info();
                $this->set_key_info();
                $this->set_XMP_metadata_info();
            }
            $this->p->close_pdi_document($this->doc);
        }
        catch (Exception $e) {
            print($e);
        }
    }

    /* --------- general information (always available) */
    public function set_general_info() {
        $this->output['filename'] = $this->p->pcos_get_string($this->doc,"filename");
        $this->output['pdfversionstring'] = $this->p->pcos_get_string($this->doc,"pdfversionstring");
        $this->output['encrypt/description'] = $this->p->pcos_get_string($this->doc,"encrypt/description");
        $this->output['encrypt/master'] = $this->p->pcos_get_string($this->doc,"encrypt/master");
        $this->output['encrypt/user'] = $this->p->pcos_get_string($this->doc,"encrypt/user");
        $this->output['encrypt/nocopy'] = $this->p->pcos_get_string($this->doc,"encrypt/nocopy");
        $this->output['linearized'] = $this->p->pcos_get_string($this->doc,"linearized");        
    }

    public function set_detail_info() {
        $this->output['pdfx'] = $this->p->pcos_get_string($this->doc,"pdfx");
        $this->output['pdfa'] = $this->p->pcos_get_string($this->doc,"pdfa");

        $xfa_present = $this->p->pcos_get_number($this->doc, "type:/Root/AcroForm/XFA") != 0;
        $this->output['xfa_present'] = $this->p->pcos_get_number($this->doc,"type:/Root/AcroForm/XFA");
        $this->output['tagged'] = $this->p->pcos_get_number($this->doc,"tagged");
        $this->output['pages'] = $this->p->pcos_get_number($this->doc,"length:pages");

    }

    public function set_key_info() {
        $count = $this->p->pcos_get_number($this->doc, "length:/Info");

        for ($i=0; $i < $count; $i++) {
            $info = "type:/Info[" . $i . "]";
            $objtype = $this->p->pcos_get_string($this->doc, $info);

            $info = "/Info[" . $i . "].key";
            $key = $this->p->pcos_get_string($this->doc, $info);
            
            // $info entries can be stored as string or name objects
            if ($objtype == "name" || $objtype == "string") {
                $info = "/Info[" . $i . "]";
                $this->output[$key] = $this->p->pcos_get_string($this->doc,$info);
            } else {
                $info = "type:/Info[" . $i . "]";
                $this->output[$key] = $this->p->pcos_get_string($this->doc,$info);
            }
        }
    }

    public function set_XMP_metadata_info() {
        $objtype = $this->p->pcos_get_string($this->doc, "type:/Root/Metadata");
        if ($objtype == "stream") {
            $contents = $this->p->pcos_get_stream($this->doc, "", "/Root/Metadata");
            $this->output['xmp-metadata'] = $this->p->pcos_get_stream($this->doc, "", "/Root/Metadata");
        } else {
            $this->output['xmp-metadata'] = 'not present';
        }
    }

    public function get_pdf_all() {
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