<?php

include_once('timetable.php');

$timeTable = new TimeTable;

$timeTable->friendly();

echo "<hr>";

$timeTable->table();


?>