<?php $this->load->database();?>
<html>
<body>
<?php foreach($alllist as $item)
{
    $book = $this->db->select('hnname, hnauthor')->from('books')
                        ->where('asin',$item['asin'])->get()
                        ->_fetch_assoc();

    echo "<h3><pre>".$item['asin']."      (".$item['authorresult'].")       ".$book['hnname']."        ".$book['hnauthor']."<div/></pre></h3>";
}?>
</body>
</html>
