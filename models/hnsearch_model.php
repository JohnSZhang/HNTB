<?php 
/**
* HNSearch model class for dealing with hacernews related data and tables
*/
class Hnsearch_model extends CI_Model
{
    /**
    * constructor to load the database library
    */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    /**
    * An update function that updates the changed hackernews
    * title to the books table
    *
    * @param string $asin books asin
    * @param string $hnttile the updated booktitle to besearched
    */
    public function updateHNName($asin, $hntitle)
    {  
        $this->db->where('asin',$asin)
                       ->set('hnname', $hntitle);
        $this->db->update('books');
    }
    /**
    * An update function that updates the extracted author lastname 
    * to the books table
    *
    * @param string $asin books asin
    * @param string $hnauthor the author's last name ot be updated 
    */
    public function updateHNAuthor($asin, $hnauthor)
    {
        $this->db->where('asin',$asin)
                       ->set('hnauthor', $hnauthor);
        $this->db->update('books');

    }
    /**
    * A select function to get all books from the books table
    */
    public function getBooks()
    {
       return $this->db->get('books')->result_array();
    }
    /**
    * a update function that updates the results table with
    * HN search result
    *
    * @param string $asin book's asin
    * @param int $hits number of hacker news results
    * @param string $column the column nam eot be updaed
    */
    public function updateHits($asin, $hits, $column)
    {
       if($this->bookExist($asin))
       {
           $this->db->where('asin',$asin)
               ->set($column,$hits);
           $this->db->update('hnresult');
       }
       else
       {
           $array = array('asin' => $asin, $column => $hits);
           $this->db->insert('hnresult', $array);

       }
    }
    /**
    * a validation functiong ot see if a book already exist in the result table
    *
    * @param string $asin the book asin
    * @return bool true or faluse
    */
    protected function bookExist($asin)
    {
       $query = $this->db->query("SELECT * FROM hnresult WHERE asin='$asin'");
       $row = $query->num_rows();
       if ($row==0)
       {
           return FALSE;
       }
       else
       {
           return TRUE;
       }
    }
    /**
    * A select function to get all books from the topbooks table
    */

    public function topList()
    {
       return $this->db->get('hnresult')->result_array();
    }
}
