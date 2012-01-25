<?php
/** 
* The Booknodes controller class for dealing with Amazon booknodes
*
* The controller class handles displaying all topics for the 
* site and the books contained in a given topic. It also contains
* the method to up date topic list by the books in the toplist table
* as well as deprecated methods for transversing through Amazon book
* nodes
*/
class Booknodes extends CI_Controller{
    /**
    * Constructor that loads AWS configs, AmazonECS library and 
    * Node, Top_model Model
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
        $this->load->model('Node');
        $this->load->model('Top_model');
    }
    
    /**
    * Sets up the browseNodeLookup function and returns response
    * 
    * @param string $key amazon node number
    * @return array $response returns the amazon response as array
    */
    protected function amazonNodeSearch($key)
    {
        $response = $this->amazonecs->responseGroup('BrowseNodeInfo')
            ->browseNodeLookup($key);
        $response = $this->validateResponse($response);
        return $response;
    }
    /**
    * Validation method for amazon node response
    * 
    * @param array $reponsearray the amazon response array
    * @return array $responsearray returns the array if valide
    * @exception if not a valide array throws exception
    */
    protected function validateResponse($responsearray)
    {
        if (! is_array($responsearray))
        {
            throw new Exception('The response is not an array');
        }
        if ($responsearray['BrowseNodes']['Request']['IsValid']=="True")
        {
            return $responsearray;
        }
        else 
        {
            throw new Exception('The array is not a valid request');
        }
    }
        /**
    * Method for extracting current node info of the amazon response
    *
    * @param array $responsearray amazon response array
    * @param array $data array to store parsed childnodes
    * @return array $data return updated array $data
    */
    protected function currentNode($responsearray,$data)
    {
        $data["currentnode"]=$responsearray['BrowseNodes']
            ['BrowseNode']['BrowseNodeId'];
        $data["currentnodename"]=$responsearray['BrowseNodes']
            ['BrowseNode']['Name'];
        return $data;
    } 
    
    /**
    * Method for generation the topics page with all topics in database
    * 
    * @return view top/topics the topics page
    */
    public function allNodes()
    {
        $data = array();
        $topiclist = $this->Node->allNodes();
        for ($i=0; $i<count($topiclist); $i++)
        {
        $data['topic'][$i]['name'] = $topiclist[$i]['name'];
        $data['topic'][$i]['amazonid'] = $topiclist[$i]['amazon_id'];
        $data['topic'][$i]['toplist'] = $topiclist[$i]['numtoplist'];

        }
        $header=array();
        $header['activeheader']='topic';
        $this->load->view('template/header',$header);
        $this->load->view('top/topics',$data);
        $this->load->view('template/footer');
    }
    /**
    * The updatenode method that first wipes the entire node table then
    * takes all books from table toplist and calls 
    * getnodes to update nodes table
    */
    public function updateNodes()
    {
        $this->Node->clearTable();
        $topbooks = $this->Node->topList();
        foreach ($topbooks as $book)
        {
            $asin = $book['asin'];
            $this->getNodes($asin);
        }
    }   
    /**
    * Find all nodes that belong to a book and update node table 
    * 
    * First checks to see if the asin number is valid and has nodes
    * Then calls amazonnodesearch to get node name, then either inserts
    * a new node or update the node count of the existing node
    *
    * @param int $asin the books asin number
    */ 
    protected function getNodes($asin)
    {
        $nodes = $this->Node->bookNodes($asin);
        if ($nodes == FALSE)
        {
            throw new Exception('error this book has no node 
                    or invalid ASIN number');
        }
        $nodearray = explode(',', $nodes[0]['nodes']);
        foreach ($nodearray as $nodename)
        {
            if (!$this->Node->nodeExist($nodename))
            {
                $nodeinfo = $this->amazonNodeSearch($nodename);
                $currentnode = array();
                $currentnode = $this->currentNode($nodeinfo, $currentnode);
                
                    $this->Node->addNode($currentnode['currentnode'], 
                            $currentnode['currentnodename']);
                    $this->Node->nodeCount($currentnode['currentnode']);
            }
            else 
            {
                $this->Node->nodeCount($nodename);
            }

        }
    }
    /** 
    * Tage method that finds and displays all books in a given
    * tops(node) and sort by it's popularity
    * 
    * The function first gets the entire table of top books, 
    * then searches each book to see if they belong to the category
    * if it is, add them to an array and sort by their popularity
    *
    * @param int $node the amazon node number
    * @return view top/toplist the page with all books of the node
    */
    public function topic($node)
    {
        $topbooks = $this->Node->topList();
        $nodebooks = array();
        foreach ($topbooks as $book)
        {
            $asin = $book['asin'];
            $nodes = $this->Node->bookNodes($asin);
            if ($nodes == FALSE)
            {
                throw new Exception('error this book has no node 
                        or invalid ASIN number');
            }
            $nodearray = explode(',', $nodes[0]['nodes']);
            foreach ($nodearray as $nodename)
            {
                if ($nodename == $node)
                {
                    $nodebooks = array_merge($nodebooks, array($asin));
                }
            }

        }
        $data = array();
        for($i = 0; $i<count($nodebooks); $i++)
        {
            $books = $this->Top_model->getTopListInfo($nodebooks[$i]);
            $data['book'][$i]['image'] = $books[0]['medimage'];
            $data['book'][$i]['asin'] = $books[0]['asin'];
            $data['book'][$i]['authorresult'] = $books[0]['authorresult'];
            $description = strip_tags($books[0]['description']);
            if(strlen($description)<300)
            {
                $truncated = $description;
            }
            else
            {
                $truncated = substr($description,0,
                        strpos($description,' ',300));
            }
            $data['book'][$i]['description'] = $truncated;
            $bookinfo = $this->Top_model->getBookInfo($nodebooks[$i]);
            if(str_word_count($bookinfo[0]['hnname'])==1)
            {
                $data['book'][$i]['title'] = $bookinfo[0]['name'];
            }
            else {
                $data['book'][$i]['title'] = $bookinfo[0]['hnname'];
            }

        }
        $sort_result = array();
        foreach ($data['book'] as $a=>$key)
        {
            $sort_result[] = $key['authorresult'];
        }
        array_multisort($sort_result,SORT_DESC, $data['book']);
        for($i=0; $i<count($data['book']); $i++)
        {
            $data['book'][$i]['rank']=$i+1;
        }
        $data['header'] = 'about '.
                $this->Top_model->getCategories($node);
        $header=array();
        $header['activeheader'] = 'topic';
        $this->load->view('template/header', $header);
        $this->load->view('top/toplist', $data);
        $this->load->view('template/footer');
    }
    /**
    * Deprecated searchnode function that displays a given node's
    * parent, self and child information
    * 
    * @deprecated use the updatenode functions instead
    * @param string $key amazon node number
    * @return view $booknodes/index html view
    */
    public function searchnode($key)
    {
        $data=array();
        $response = $this->amazonNodeSearch($key);
        $data=$this->childNodes($response,$data);
        $data=$this->currentNode($response,$data);
        $data=$this->parentNode($response,$data);
        $data['result']=$response;
        $this->load->view('booknodes/index',$data); 
    }
    /**
    * Method for extracting child nodes of the amazon response
    *
    * @deprecated deprecated along with searchnode
    * @param array $responsearray amazon response array
    * @param array $data array to store parsed childnodes
    * @return array $data return updated array $data
    */
    protected function childNodes($responsearray,$data)
    {
        $this->load->helper('url'); 
        if(! isset($responsearray['BrowseNodes']['BrowseNode']
                    ['Children']['BrowseNode']))
        {
            $data["childnum"]=0;
            $data["children"][0]["id"]=0;
            $data["children"][0]["name"]='It_has_no_Children';

        }
        else
        {       
            $childnum = count($responsearray['BrowseNodes']['BrowseNode']
                    ['Children']['BrowseNode']);
            if($childnum>0)
            {
                $data['childnum']=$childnum;
                for($i=0; $i<$childnum; $i++)
                {$data["children"]["$i"]["id"]=$responsearray['BrowseNodes']
                    ['BrowseNode']['Children']['BrowseNode'][$i]
                        ['BrowseNodeId'];
                    $data["children"]["$i"]["name"]=url_title(
                            $responsearray['BrowseNodes']['BrowseNode']
                            ['Children']['BrowseNode']
                            [$i]['Name'],'underscore');
                }
            }
        }
        return $data;
    }
    /**
    * Method for extracting parent node info of the amazon response
    *
    * @deprecated deprecated along with searchnode
    * @param array $responsearray amazon response array
    * @param array $data array to store parsed childnodes
    * @return array $data return updated array $data
    */
    protected function parentNode($responsearray,$data)
    {
        $data["parentnode"]=$responsearray['BrowseNodes']
            ['BrowseNode']['Ancestors']['BrowseNode']['BrowseNodeId'];
        $data["parentnodename"]=$responsearray['BrowseNodes']
            ['BrowseNode']['Ancestors']['BrowseNode']['Name'];
        return $data;
    }
}




