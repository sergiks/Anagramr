<?php

/**
 * Creates bit index of used letters in each word of a dictionary
 */

require_once 'Anagramr.php';


$dict = 'wordsTest.txt';
$index = array(
	'bits' => 'idxBits.dat',
	'rows' => 'idxRows.dat',
	'lets' => 'idxLets.txt',
);

Anagramr::makeIndexes( $dict, $index['bits'], $index['rows'], $index['lets']);

//echo Anagramr::getWord( 11, $dict, $index['rows']);

$test = 'blissulf';
$found = Anagramr::getValidWordsRows( $index['bits'], $test);

foreach( $found AS $i)  echo Anagramr::getWord( $i, $dict, $index['rows']) . PHP_EOL;