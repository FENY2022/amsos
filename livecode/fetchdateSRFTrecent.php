<?php

$start_date = $_GET['start_date']; 
$end_date = $_GET['end_date']; 
$status_search = $_GET['status_search']; 



// echo '<script>window.location.href = "mainmenu.php?dir=recent&start_date='.$start_date.'&end_date='.$end_date.'";</script>';
echo '<script>window.location.href = "mainmenu.php?dir=recent&start_date='.$start_date.'&end_date='.$end_date.'&status_search='.$status_search.'";</script>';



?>