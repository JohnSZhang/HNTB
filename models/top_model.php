<?php 
/**
* The top model class selects information from the toplist table
* for controller Top to display
*/
class Top_model extends CI_Model
{
    /**
    * constructor calls the database library
    */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    /**
    * A method to get all books from top list as array
    * 
    * @return array $result_array() 
    */
    public function topList()
    {
       return $this->db->get('toplist')->result_array();
    }
    /**
    * An update media method that updates the media information
    * 
    * @param string $asin the asin of the book
    * @param string $medimageurl the url to medium size image
    * @param string $largeimageurl large image url to be update
    * @param string $description the description of the book
    */
    public function updateMedia($asin, $medimageurl, $largeimageurl, $description)
    {
        $this->db->where('asin', $asin)
            ->set('medimage', $medimageurl)
            ->set('largeimage', $largeimageurl)
            ->set('description', $description);
        $this->db->update('toplist');
    }
    /**
    * Get a list of top books given start and end number
    *
    * @param int $start start rank
    * @param int $end ending rank
    * @return array $result_array() the ranking array
    */
    public function getTopList($start, $end)
    {
       $this->db->order_by("authorresult", "desc");
       $this->db->order_by("asinresult", "desc");
       $this->db->order_by('name', "asc");
       $this->db->limit($end-($start-1),$start-1);
       return $this->db->get('toplist')->result_array();
    }
    /**
    * method to get information of book from the books table
    * 
    * @param string $asin asin of book
    * @return array $result array
    */
    public function getBookInfo($asin)
    {
        $this->db->where('asin', $asin);
        return $this->db->get('books')->result_array();
    }
    /**
    * method to get toplist info
    *
    * @param string $asin book's asin
    * @return array $result array book info
    */
    public function getTopListInfo($asin)
    {
        $this->db->where('asin', $asin);
        return $this->db->get('toplist')->result_array();
    }
    /**
    * method to get all the category names of a given category list
    * 
    * @param string $nodes all nodes of a cateory
    * @return array $categories an array of name and id of each topic
    */
    public function getCategories($nodes)
    {
        $categories = array();
        $nodearray = explode(',', $nodes);
        for($i=0;$i<count($nodearray);$i++)
        {
            $node = $this->db->where('amazon_id', $nodearray[$i])
                    ->get('nodes')->result_array();
            $categories[$i]['name']=$node[0]['name'];
            $categories[$i]['id']=$node[0]['amazon_id'];


        }
        return $categories;
    }

} 
