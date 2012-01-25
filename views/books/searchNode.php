<?php $this->load->helper('url');?>
<html>
<body>
<h1> 
<h2> 
You are looking at node <?php echo $nodeid; ?> 
<a href=<?php echo site_url().'/booknodes/search/'.$nodeid;?>>View Children</a> 
<br />
<a href=<?php echo site_url().'/books/addnodebooks/'.$nodeid;?>>Add This Node's Books</a>
<div/>
</h2>
</h1>
<?php echo 'there are '.$totalpages.' total pages in this node';?>
<div/>
<?php 
for($i=1; $i<=10&&$i<=$totalpages; $i++)
{
    echo '&#32;&#32;&#32; <a href='.site_url().'/books/viewnode/'
        .$nodeid.'/'.$sort.'/'.$i.'>'.$i.'</a>';
}
 foreach($book as $item)
    {
        echo "<div/> <h4>".$item['name']."</h4>";
        echo 'by '.$item['author']; 
        if(isset($item['exist']))
        {
            echo '&#32;&#32;&#32; (in database)';
        }
        echo '<br />';

    }
?>
<body>
<html>
