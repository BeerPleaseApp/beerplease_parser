<?php

include_once 'simple_html_dom.php';
include_once 'ParseBeer.php';

$Parser = new ParseBeer('http://www.paradis-biere.com/', 1);
echo $Parser->parseToJson();