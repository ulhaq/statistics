<?php
require_once 'Statistics.class.php';

//Instantiating Statistics class.
$dataStats = new Statistics("statistics.xml");

//Calling the function which collects data. This should be included in every page.
$dataStats->dataCollect();

//Calling the function which displays the data. This function should probably be in a admin-protected page.
$dataStats->dataDisplay();
