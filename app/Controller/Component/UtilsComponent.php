<?php
App::uses('Component', 'Controller');

class UtilsComponent extends Component {

    /**
     * Get PDF Version from PDF file
     * @param $filename
     * @return int
     */
    public function pdfVersion($filename) {
        $fp = @fopen($filename, 'rb');

        if (!$fp) {
            return 0;
        }

        /* Reset file pointer to the start */
        fseek($fp, 0);

        /* Read 20 bytes from the start of the PDF */
        preg_match('/\d\.\d/',fread($fp,20),$match);

        fclose($fp);

        if (isset($match[0])) {
            return $match[0];
        } else {
            return 0;
        }
    }

    public function ucfarray($array)
    {
        $oarray=[];
        for($x=0;$x<count($array);$x++) {
            $oarray[$array[$x]]=ucfirst($array[$x]);
        }
        return $oarray;
    }

}

?>