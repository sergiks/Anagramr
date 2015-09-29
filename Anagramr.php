<?php

/**
 * Anagramr deals with dictonaries,
 * indexes them
 * and helps find, well, anagrams.
 *
 * by Sergei Sokolov <hello@sergeisokolov.com>
 * Made in Moscow just for fun, 2015.
 */

class Anagramr {

	/**
	 * Creates indexes out of a dictionary.
	 * $indexBits: bitmasks of letters used in each word of a dictionary
	 *     Each word presented by 4 bytes, least 26 bits each corresponding a letter, a to z
	 *     Bit is set if word contains corresponding letter.
	 * $indexBytes: byte offset for a word.
	 * $indexLetters: each word gets its letters sorted alphabetically ("apple" => "aelpp")
	 */
	public static function makeIndexes( $dict, $indexBits, $indexRows, $indexLetters) {		
		if( !is_readable( $dict))		throw new Exception("Dictionary file is not readable");
		
		// echo alphabet
		for($i=25; $i>=0; $i--) echo chr( 97+$i);
		echo "\n";
		
		// prepare file handlers
		$fi = fopen( $dict, 'r');
		if( FALSE === ($fhBits = fopen( $indexBits, 'w')))
			throw new Exception("$indexBits file is not writable");
		if( FALSE === ($fhRows = fopen( $indexRows, 'w')))
			throw new Exception("$indexRows file is not writable");
		if( FALSE === ($fhLets = fopen( $indexLetters, 'w')))
			throw new Exception("$indexLets file is not writable");

		
		// loop through words
		while( !feof( $fi)) {
			if( FALSE === ($str = fgets( $fi))) break;

			// Byte offset
			$offset = ftell( $fi) - strlen( $str);
			fwrite( $fhRows, pack( 'V', $offset));


			// Letter bits
			$word = trim( $str);
			$mask = self::getLettersBitMask( $str);
			fwrite( $fhBits, pack('V', $mask));


			// sort letters
			$arr = str_split( $word);
			sort( $arr);
			fwrite( $fhLets, implode('', $arr) . "\n");


			printf( "%026b\t%d\t%s\n", $mask, $offset, $word);
		}
		
		fclose($fi);
		fclose($fhBits);
		fclose($fhRows);
	}
	
	
	public static function getWord( $rowN, $dict, $indexRows) {
		if( FALSE === ($fh = fopen( $indexRows, 'r'))) throw new Exception("FAiled to open $indexRows");
		fseek( $fh, 4 * $rowN);
		$bytes = fread( $fh, 4);
		$data = unpack( 'Voffset', $bytes);
		fclose( $fh);
		
		$fh = fopen( $dict, 'r');
		fseek( $fh, $data['offset']);
		$word = trim( fgets( $fh));
		
		fclose( $fh);
		return $word;
	}
	
	
	public static function getLettersBitMask( $word) {
		$mask = 0;
		$word = strtolower( trim( $word));
		$len = strlen( $word);
		for( $i=0; $i<$len; $i++) {
			$c = ord( substr( $word, $i, 1)) - 97; // ASCII: a=97 .. z=122
			$mask = $mask | (1 << $c);
		}
		
		return $mask;
	}
	
	
	/**
	 * Find words not containing specified letters
	 *
	 * @param string $letters		- valid letters
	 * @param int optional $limit	- stop after finding this number of valid rows
	 *
	 * @return array of int row numbers
	 */
	public static function getValidWordsRows( $indexBits, $letters, $limit=0) {
		
		if( FALSE === ($fi = fopen( $indexBits, 'r'))) throw new Exception( "Failed opening $indexBits");
		$mask = ~self::getLettersBitMask( $letters);

		$found = [];
		while( !feof($fi)) {
			if( FALSE === ($bytes = fread( $fi, 4))) break;
			if( 4 !== strlen( $bytes)) break;
		
			$d = unpack( 'Vmask', $bytes);
			if( $mask & $d['mask']) continue;

			array_push( $found, (ftell( $fi) / 4)-1);
			
			if( $limit  && (count($found) >= $limit)) break;
		}
		
		fclose( $fi);
		
		return $found;		
	}
		

	
}