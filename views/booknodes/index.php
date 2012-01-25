<?php $this->load->helper('url');?>
<html>
<body>
<h1>
<?php echo "This is the nodemap for $currentnodename, node id : $currentnode"; ?>
</h1>
<div/>
<h2>
<?php echo 'Parent node is <a href='.$parentnode.'>'.$parentnodename.'</a> ('.$parentnode.')';?>
</h2>
<div/>
<?php foreach($children as $item)
{echo '<div/> <h3> <a href='.$item['id'].'>'.$item["name"].'</a> id: '.$item["id"].' 
&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;
<a href='.site_url().'/books/viewnode/'.$item['id'].'/reviewrank>Reviewranked Books</a>
&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;&#32;
<a href='.site_url().'/books/viewnode/'.$item['id'].'/salesrank>Salesrank Books</a>

<br/></h3>
';//<a href=../addNode/'.$item["id"].'/'.$item["name"].'>add to node list</a> </h3>';
}
?>

</body>
</html>
