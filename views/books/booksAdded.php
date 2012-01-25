<?php $this->load->helper('url'); ?>
<html>
<body>
<h2>
Successfully added <a href=<?php echo site_url().'/books/viewnode/'.$nodeid.'reviewrank>Node '.$nodeid.'</a>';?> 
<?php echo "<br /> There are ".$totalpages[0]." pages in this node";?>
</h2>
<?php foreach ($book as $item)
{
    echo '<br /> <h4>'.$item['name'].'  '.$item['author'].'</h4>';
}
?>
</body>
</html>
