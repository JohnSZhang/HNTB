<div class="container">
<h1>
Top Categories 
</h1>   
<?php
$this->load->helper('url');
for($i=0; $i<count($topic); $i++)
{
    $topiclink = base_url("index.php/topic/".$topic[$i]['amazonid']);
    if($i%3==0)
    {
        echo '<div class="row">';
    }
    echo '<div class="span-one-third">';
    if ($topic[$i]['toplist']>10)
    {
        $headernum = 2;
    }
    else if($topic[$i]['toplist']>5)
    {
        $headernum = 3;
    }
    else 
    {
        $headernum = 4;
    }
    echo '<h'.$headernum.'>';
    echo '<a href='.$topiclink.'>'.$topic[$i]['name'].' ('
        .$topic[$i]['toplist'].')</a>';
    echo '</h'.$headernum.'>';
    echo '</div>';
    if(($i!==0 and (($i+1)%3==0)) or ($i==count($topic)))
    {
        echo '</div>';
    }

}
echo '</div>';
?>
</div>
</div>
</div>
