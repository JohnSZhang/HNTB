<?php $this->load->helper('url');?>
<div class = "container">
<div class = "span14">
<h1>
<a href="http://www.amazon.com/gp/product/<?php echo $asin;?>
/ref=as_li_tf_tl?ie=UTF8&tag=hntobo-20&linkCode=am2&camp=1789&creative=390957&creativeASIN=
<?php echo $asin;?>" target="_blank"
><?php echo $title; ?></a>
<img src="http://www.assoc-amazon.com/e/ir?t=hntobo-20&l=am2&o=1&a=
<?php echo $asin;?>" width="1" height="1" border="0" alt="" style="border:none !important; margin:0px !important;" />
</h1>
</div>
<div class = "span16 description">
<pre class ="description">
<img src = "<?php echo $image;?>" class = "coverimage"/>
<h3>Author:  <?php echo $author;?></h3>
<h3>
Topics: <?php foreach($tags as $tag)
{
    echo '<a href="'.base_url("/index.php/topic").'/'.$tag['id'].'">'.$tag['name'].'</a> ';
}?> 
</h3>
<p>
<?php echo $description;?>
</pre>
</p>
</div>
</div>
<div class="container hackernews">
<div class="span 15">
<a href="http://www.amazon.com/gp/product/<?php echo $asin;?>
/ref=as_li_tf_tl?ie=UTF8&tag=hntobo-20&linkCode=am2&camp=1789&creative=390957&creativeASIN=
<?php echo $asin;?>" target="_blank"><img src ="<?php echo base_url('/application/images/amazon.gif');?>"/></a>
<h3 class='discussion'>
Discussion on Hacker News
(<?php echo $hncomment['hits'];?> Hits) :
</h3>
<?php
foreach($hncomment["results"] as $result)
{
    echo '<div class="span10 discussiontitle">';
    echo '<b><a class="discussion" href="http://news.ycombinator.com/item?id='.$result['item']['discussion']['id'].'" target="_blank">'
    .$result['item']['discussion']['title'].'</a></b>';
    echo '</div> <div class="span10 comment">';
    echo $result['item']['username'].':  '.$result['item']['text'];
    if(isset($result['item']['parent_sigid']))
    {
    echo '  <a href="http://news.ycombinator.com/item?id='
    .substr($result['item']['parent_sigid'],0,strpos($result['item']['parent_sigid'],'-')-1)
    .'" target="_blank">Context</a>';
    }
    echo '<br/></div>';
}?>
<?php echo '<a href="http://www.hnsearch.com/search#request/all&q='.str_replace(' ','+',$hnsearchtitle).'">More Hacker News Search Results on '.$hnsearchtitle.'</a>';?>
</div>
</div>



