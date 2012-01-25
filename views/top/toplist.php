<div class="container">
<h1>
Top Books 
<?php echo $header;?>
</h1>   
<?php
$this->load->helper('url');
for($i=0; $i<count($book); $i++)
{
    $asin = $book[$i]['asin'];
    $booklink = base_url("index.php/top/book/$asin");
    if($i%3==0)
    {
        echo '<div class="row">';
    }
    echo '<div class="span-one-third">';
    echo '<a href='.$booklink.'>
          <img src="'.$book[$i]['image'].'"></img></a>';
    echo '<a class="booklink rank" href='.$booklink.' target="_blank"># '.$book[$i]['rank'].'</a></br>';
    echo '<a class="booklink" href='.$booklink.' target="_blank">'.$book[$i]['title'].'</a>';
    echo '<p>'.$book[$i]['description'].'...</p>';
    echo '</div>';
    if(($i!==0 and (($i+1)%3==0)) or ($i==count($book)))
    {
        echo '</div>';
    }

}
echo '</div>';
?>
</div>
</div>
</div>
