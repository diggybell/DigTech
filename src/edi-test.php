<?php

include_once('../lib/autoload.php');
include_once('../lib/autoconfig.php');

use DigTech\EDI\Parser as Parser;

$fileName = $argv[1];
if(!file_exists($fileName))
{
    printf("Input file does not exist (%s)\n", $fileName);
    exit(1);
}

$doc = file_get_contents($fileName);

$p = new Parser($doc);
printf("Transaction Count: %d\n", $p->getTransactionCount());
for($index = 0; $index < $p->getTransactionCount(); $index++)
{
    $json = json_encode([ 'ediDocument' => $p->parseEDIDocument($p->getTransaction($index)) ], JSON_PRETTY_PRINT);
    printf("%s\n", $json);

    $document = json_decode($json);
    print_r($p->parseJSONDocument($document, 'ediDocument', false));
}

?>
