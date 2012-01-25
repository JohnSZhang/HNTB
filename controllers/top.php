<?php
/**
* The Top class contains methods for displaying top listed books from the database as well
* as individual book information, and FAQ page
*/
class Top extends CI_Controller
{
    /**
    * Constructor loads the config info, amazonECS library
    * and Top_model model class
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
        $this->load->model('Top_model');
    }
    /** 
    * Topbooks method takes a range and returns the list of ranked
    * books, as well as the homepage header if called
    *
    * @param int $start the start of ranks
    * @param int $end the ending list value
    * @param int $home indicate if calling homepage
    * @return view top/toplist toplists
    */
    public function topBooks($start, $end, $homepage=0)
    {
        $books =  $this->Top_model->getTopList($start, $end);
        $data = array();
        $data['header'] = ' '.$start.' to '.$end;
        for($i = 0; $i<count($books); $i++)
        {
            $tagbook = $this->Top_model->getTopListInfo($books[$i]['asin']);
            $data['book'][$i]['rank'] = $i+$start;
            $data['book'][$i]['image'] = $tagbook[0]['medimage'];
            $data['book'][$i]['asin'] = $tagbook[0]['asin'];
            $description = strip_tags($tagbook[0]['description']);
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
            $bookinfo = $this->Top_model->getBookInfo($books[$i]['asin']);
            if(str_word_count($bookinfo[0]['hnname'])==1)
            {
                $data['book'][$i]['title'] = $bookinfo[0]['name'];
            }
            else {
                $data['book'][$i]['title'] = $bookinfo[0]['hnname'];
            }

        }
        $header=array();
        switch ($start){
            case($start<50):
                $header['activeheader']='1-50';
                break;
            case($start<100):
                $header['activeheader']='51-100';
                break;
            case($start<500):
                $header['activeheader']='100+';

        } 
        $header['homepage']=$homepage;
        if($homepage==1){
            $header['activeheader']='none';
        }
        $this->load->view('template/header',$header);
        $this->load->view('top/toplist', $data);
        $this->load->view('template/footer');


    }
    /**
    * Update medialinks for each books in the toplist table using the 
    * medium amazon responsegroup
    */
    public function updateMedia()
    {
        $bookarray = $this->Top_model->topList();
        foreach($bookarray as $book)
        {
            if(empty($book['medimage']) 
                    OR empty($book['largeimage']) 
                    OR empty($book['description']))
            {
                $response = $this->amazonecs->responseGroup('Medium')
                    ->lookup($book['asin']);
                $medimageurl = 
                    $response['Items']['Item']['MediumImage']['URL'];
                $largeimageurl = 
                    $response['Items']['Item']['LargeImage']['URL'];

                if(!count(
                            $response['Items']['Item']['EditorialReviews']
                            ['EditorialReview'])==1)
                {
                    $description =  $response['Items']['Item']
                        ['EditorialReviews']['EditorialReview'][0]['Content'];

                }
                else
                {
                    $description =  $response['Items']['Item']
                        ['EditorialReviews']['EditorialReview']['Content'];
                }
                $this->Top_model->updateMedia($book['asin'],$medimageurl, 
                        $largeimageurl,$description);
            }
        }
    }
    /**
    * Book method that takes an amazon asin and returns the book page, calls
    * HN API for popular comments
    * 
    * @param string $asin the asin of the book
    * @return view top/book displays the book page
    */
    public function book($asin)
    {
        $bookinfo = array();
        $toplistinfo = array();
        $book = array();
        $bookinfo = $this->Top_model->getBookInfo($asin);
        $toplistinfo = $this->Top_model->getTopListInfo($asin);
        $hnsearchquery = rawurlencode('"'.$bookinfo[0]['hnname'].'" '
                .$bookinfo[0]['hnauthor']);
        $hncomments = $this->hnTable($hnsearchquery);
        $book['title'] = $bookinfo[0]['name'];
        $book['author'] = $bookinfo[0]['author'];
        $book['tags'] = $this->Top_model->getCategories($bookinfo[0]['nodes']);
        $book['image'] = $toplistinfo[0]['largeimage'];
        $book['description'] = $toplistinfo[0]['description'];
        $book['hncomment'] = $this->hnTable($hnsearchquery);
        $book['asin'] = $asin;
        $book['hnsearchtitle']=$bookinfo[0]['hnname'].' by '.$bookinfo[0]['hnauthor'];

        $this->load->view('template/header');
        $this->load->view('top/book', $book);
        $this->load->view('template/footer');

    }

    //The HNtable function searches for the title of the book and gets back the
    // first 5 HN API Comments
    /** 
    * HN table method gets comments from hackernews and returns them as an array
    *
    * @param string $title title of the book, in title/author format
    * @return array $result the resulting decoded JSON object as an array
    */
    public function hnTable($title)
    {
        $hnsearch = curl_init();
        curl_setopt($hnsearch, 
                CURLOPT_URL, 
                'http://api.thriftdb.com/api.hnsearch.com/items/_search?q='.$title
                .'&weights[title]=0'.
                '&weights[text]=9.1'.
                '&weights[domain]=0.0'.
                '&weights[username]=0.0'.
                '&weights[url]=0.0');
        curl_setopt($hnsearch, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($hnsearch), TRUE);
        if(is_null($result))
        {
            throw new exception('error, the reply is not a JSON object');
        }
        return $result;
        curl_close($hnsearch);      
    }
    /**
    * Controller method for displaying the FAQ page of HNTB
    * 
    * @return view top/faq faq page
    */
    public function faq()
    {
        $header=array();
        $header['activeheader']='faq';
        $this->load->view('template/header',$header);
        $this->load->view('top/faq');
        $this->load->view('template/footer');
    }
}    
