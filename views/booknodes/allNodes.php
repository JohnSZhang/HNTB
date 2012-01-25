<html>
<body>
<h2>
List of all nodes in database
</h2>
<div/>  
<?php 
foreach($nodelist as $item)
{echo "<h3> ".$item['amazon_id']." <a href=search/".$item['amazon_id'].">".$item['name']."</a> 
     <a href=removeNode/".$item['amazon_id'].">Remove Node</a> </h3> <div/>";}
?>
<h3>
Too add a new node, search searching <a href=search/1000>here</a>
</h3>
</body>
</html>
