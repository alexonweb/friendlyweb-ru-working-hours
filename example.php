<?php

require 'FriendlyWeb/TimeTable/TimeTable.php';

$timeTable = new \FriendlyWeb\TimeTable;

$timeTable->friendly();

echo "<hr>";

$timeTable->table();

?>