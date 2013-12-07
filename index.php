<?php
include 'class.database.php';

echo '<pre>';
$db = new Database('localhost', 'root', '', 'asad');


$where = array('email' => 'getvivekv@gmail.com')  ; 

$db -> select('*') -> from('tblcontacts') -> where($where) -> execute();

$result = $db->fetch() ;
print_r($result);

echo 'Last query was : ' . $db -> last_query() . ' <br>';
echo 'Affected row :' . $db->affected_rows;
