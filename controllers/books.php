<?php
/**
* A books controller that deals with getting raw book data from amazon
* 
* The controller contains methods for searching and going through nodes
* and adding popular books to the books table sorted by both popularity
* and review scorew. Adds the entire 10 pages allowed by the Amazon Product
* advertisement API
*/
class Books extends CI_Controller
{
    /**
    * The constructor method that loads the amazon config files
    * as well as the AmazonECS library and the books_model
    */
    public function __Construct()
    {
        parent::__Construct();
        $login = array(
            $this->config->item('AWS_API_KEY'),
            $this->config->item('AWS_API_SECRET_KEY'),
            'com',
            $this->config->item('AWS_ASSOCIATE_TAG'));
        $this->load->library('AmazonECS',$login);
        $this->load->model('Books_model');
    }
    
    /**
    * A search function that gets a list of books of a given topic from amazon 
    *
    * @param int $nodeid the amazon booknode ID
    * @param string $sort the sorting method to be configed to amazon
    * @param int $page the pagenumber of ressult
    * @param string $category the category to be searched
    * @param string $keyword the optional keywords to search with 
    * @return array $data an array containing book data extracted from the response
    */
    protected function searchNode($nodeid, $sort, $page, 
                               $category, $keywords = "")
    {
        $data=array();
        $unvalidatedresponse = $this->amazonecs->category($category)
                    ->sortParam($sort)->page($page)->search($keywords,$nodeid);
        $response = $this->validateSearch($unvalidatedresponse);
        //$data['result']=$response;
        $data = $this->getBooks($response,$data);
        $data['totalpages'] = $response['Items']['TotalPages'];
        $data['nodeid'] = $nodeid;
        if (! is_int($data['totalpages']))
        {
            throw new exception('Invalide Page Numbers');
        }
        return $data;
    }
    /**
    * A viewnode function that lists the books of a given node to help decide
    * if the node sohuld be included
    * @param int $nodeid the amazon booknode ID
    * @param string $sort the sorting method to be configed to amazon
    * @param int $page the pagenumber of ressult
    * @param string $category the category to be searched
    * @return view /books/searchnode the view of first page of books in the node 
    */
    //ViewNode fucntion used to view books of a given node and sort method
    public function viewNode($nodeid, $sort, $page = "1", $category = "Books")
    {
        $data = array();
        $data = $this->searchNode($nodeid, $sort, $page, $category);
        $data['sort'] = $sort;
        for($i=0; $i<count($data['book']); $i++)
        {
          if($this->Books_model->bookExist($data['book'][$i]['asin']))
          {
              $data['book'][$i]['exist']=TRUE;
          }
        }
        $this->load->view('books/searchNode',$data);
    }
   /**
   * A method that adds the first 10 pages of nodes in the given node
   * sorted by reviewrank and salesrank
   *
   * @param int $nodeid the amazon id of the node to be added
   */
    public function addNodeBooks($nodeid)
    {
        $books = array();
        $books = $this->searchNode($nodeid, "reviewrank", "1", "Books");
        $pagenumber = $books['totalpages'];
        for($i = 2; $i<=$pagenumber&&$i<=10; $i++)
        {
            $books = array_merge_recursive($books, $this->searchNode(
                        $nodeid, "reviewrank", "$i", "Books"));
        }   
        $this->Books_model->addBooks($books);   

        $books2 = array();
        $books2 = $this->searchNode($nodeid, "salesrank", "1", "Books");
        for($i = 2; $i<=$pagenumber&&$i<=10; $i++)
        {
            $books2 = array_merge_recursive($books2, $this->searchNode(
                        $nodeid, "salesrank", "$i", "Books"));
        }   
        $this->Books_model->addBooks($books2);   
        $data = array_merge_recursive($books,$books2);
        $data['nodeid'] = $nodeid;
        $data['totalpages']=$books['totalpages'];
        $this->load->view('books/booksAdded', $data);
    }

    /**
    * An extraction method that gets the data from amazon response 
    * and saves the relevant information in a data array
    *
    * @param array $responsearray the amazon response array
    * @param array $data the data array to store the extracted info
    * @return array $data returns the extracted and updated data array
    */
    protected function getBooks($responsearray,$data)
    {
        if(! isset($responsearray['Items']['Item'][0]['ASIN']))
        {
            throw new exception('There are no items in this response');
        }

        else
        {       
            $nodeid= $responsearray['Items']['Request']['ItemSearchRequest']
                               ['BrowseNode'];
            $booknum = count($responsearray['Items']['Item']);
            if($booknum>0)
            {
                 for($i=0; $i<$booknum; $i++)
                {
                    $data["book"]["$i"]["asin"]=$responsearray['Items']
                                    ['Item'][$i]['ASIN'];
                    $data["book"]["$i"]["name"]=$responsearray['Items']
                                    ['Item'][$i]['ItemAttributes']
                                    ['Title'];
                    //Check to see if the author field exist or if there are 
                    //multiple authors, if so combine them
                    $author = NULL;
                    if(isset($responsearray['Items']
                        ['Item'][$i]['ItemAttributes']['Author']))
                    {   
                        $author = $responsearray['Items']
                                ['Item'][$i]['ItemAttributes']['Author'];
                    }
                    if(is_null($author))
                    {
                        $data["book"]["$i"]["author"] = "No Author";
                    }

                    else if(is_string($author))
                    {
                        $data["book"]["$i"]["author"] = $author;
                    }

                    else if(is_array($author))
                    {
                        $data["book"]["$i"]["author"] = implode(',',$author); 

                    }
                    else
                    {
                        throw new exception('author array 
                                is neither array nor string');
                    }
                    $data["book"]["$i"]["nodes"]=$nodeid;
                }
            }   
        }       
        return $data;
    }
    /**
    * a validation method to check if the amazonecs response is valid
    * 
    * @param array $response amazon response
    * @return array $response returns reponse if valid
    * @exception if not valide, throws exception
    */
    protected function validateSearch($response)
    {
        if(! $response['Items']['Request']['IsValid']=="True")
        {
            throw new exception('The Response Page is not valid');
        }
        else
        {
            return $response;
        }
    } 
    /**
    * A deprecated method for finding information of a given book
    *
    * @deprecated used for var_dump purposes
    */
    public function book($asin)
    {
       $response = $this->amazonecs->lookup($asin);
       $data["result"]=$response;
       $this->load->view('books/lookUp',$data);
    } 
    /**
    * a search function for finding book of a given string of keywords
    * currently not used in the site
    */
    public function search($category = 'Books', $keywords)
    {
       $searcharray = explode("_", $keywords);
       $searchterms = implode(" ", $searcharray);
       $data['searchterms'] = $searchterms;
       $data['result'] = $this->validateSearch($this->amazonecs
               ->category($category)->search($searchterms));      
       $this->load->view('books/search',$data);
    }
}
