<?php 
/**
* The HNsearch controller contains methods for updating the topbooks table with 
* books from amazon and updates how often they are mentioned in Hacker News
*/
class Hnsearch extends CI_Controller
{
    /**
    * Constructor that loads the HNsearch_model
    */
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Hnsearch_model');
    }
    /** 
    * A derive title function to format all book titles in the book table
    */
    public function deriveTitle()
    {
        $allbooks = array();
        $allbooks = $this->Hnsearch_model->getBooks();
        foreach($allbooks as $book)
        {
            $asin = $book['asin'];
            $amazontitle = $book['name'];
            $hnarray = explode(':',$amazontitle);
            $specialchar = array('"', '!', '#', '?', '%', '@', '/');
            $hnname = str_replace($specialchar, "", $hnarray[0]);
            $hnname = preg_replace('/\([^\)]+\)/', '', $hnname);
            $hnname = trim($hnname);
            $this->Hnsearch_model->updateHNName($asin, $hnname);
        }
    }
    /**
    * A method to find the last name of the first author and
    * strips special titles
    */
    public function deriveAuthor()
    {
        $allbooks = array();
        $allbooks = $this->Hnsearch_model->getBooks();
        foreach($allbooks as $book)
        {
            $asin = $book['asin'];
            $amazonauthor = $book['author'];
            $authorarray = explode(',',$amazonauthor);
            $hnauthor = str_replace('.', "", $authorarray[0]);
            $postname = array('jr', 'sr', 'phd', 'II', 'III',
                    'MD','CPA','CFA');
            $hnauthor = str_ireplace($postname,"",$hnauthor);
            $hnauthor = trim($hnauthor);
            $lastnamearray = explode(' ', $hnauthor);
            $lastnameposition = count($lastnamearray)-1;
            $hnauthor = $lastnamearray[$lastnameposition];
            $this->Hnsearch_model->updateHNAuthor($asin, $hnauthor);
        }
    }
    /** 
    * A Method for searching all combined title/author
    * and search for them in Hackernews then update in 
    * the result table
    */
    public function searchHN()
    {
    $allbooks = array();
    $allbooks = $this->Hnsearch_model->getBooks();
    $count['count'] = 0; 
    foreach($allbooks as $book)
    {       
        $rawtitle = $book['asin'];
        $authorname = $book['hnauthor'];
        $refinedtitle = rawurlencode('"'.$rawtitle.'"');
                //.' '.$authorname);
        //$refinedtitle = $book['name'];  
        //$refinedtitle = preg_replace('/\([^\)]+\)/', '', $refinedtitle);
        //$refinedtitle = rawurlencode('"'.$refinedtitle.'"');
        $result = $this->hnCurl($refinedtitle);
        $hits = $result['hits'];
        if($hits>=1)
        {
            $this->Hnsearch_model->updateHits($book['asin'], $hits, 'asinresult');
        }
        $count['count'] = $count['count'] + 1;
    }
    $this->load->view('hnsearch/search', $count);
    }
    /**
    * Using a curl function to call the HackerNews API
    * 
    * @param string $title booktitle as search query
    * @return array $result the decoded JASON object as array
    */
    protected function hnCurl($title)
    {
        $hnsearch = curl_init();
        curl_setopt($hnsearch, 
        CURLOPT_URL, 
        'http://api.thriftdb.com/api.hnsearch.com/items/_search?q='.$title);
        curl_setopt($hnsearch, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($hnsearch), TRUE);
        if(is_null($result))
        {
            throw new exception('error, the reply is not a JSON object');
        }
        return $result;
        curl_close($hnsearch);      
    }
} 


