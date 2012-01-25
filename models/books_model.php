<?php 
/**
* A model class for updating book info for the books controller
*/
class Books_model extends CI_Model
{
    /**
    * the constructor method that loads the database library
    */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    /**
    * a method for adding an array of book data into the books table
    *
    * @param array $bookdata the books to be added
    */
    public function addBooks($bookdata)
    {
        foreach ($bookdata['book'] as $book)
        {
            if($this->bookExist($book['asin']))
            {
               $nodelist = $this->db->select('nodes')->from('books')
                        ->where('asin',$book['asin'])->get()
                        ->_fetch_assoc();
               $nodearray = explode(',',$nodelist['nodes']);
               $nodeexist = NULL;
               foreach($nodearray as $node)
               {
                   if ($book['nodes'] == $node)
                   {
                       $nodeexist = 1;
                       break;
                   }

               }
               if($nodeexist==NULL)
               {
                   //update nodes
                   $this->db->where('asin',$book['asin'])
                       ->set('nodes', $nodelist['nodes'].",".$book['nodes']);
                   $this->db->update('books');
               }
            }
            else
            {
                $this->db->insert('books',$book);
            }
        }
        return $bookdata;
    }
    /**
    * A function that checks if a given book is already in the table
    * so as to not be added again
    *
    * @param string $asin the asin of the book
    * @param bool true or false
    */
    public function bookExist($asin)
    {
       $query = $this->db->query("SELECT * FROM books WHERE asin='$asin'");
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
    * a method to get all books from the books table
    * currently not used.
    */
    public function allBooks()
    {
       return $this->db->get('books')->result_array();
    }
}   
