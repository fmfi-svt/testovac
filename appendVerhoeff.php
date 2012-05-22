<?php
require_once('verhoeff.php');

function appendVerhoeffAfter15DigitString($digitString) {
	if( ! (strlen($digitString) === 15) || ! is_digits($digitString)) {
		            throw new \InvalidArgumentException(sprintf("Error! Value is restricted to the digit string with length 15.", $digitString));
	}
	$result = appendVerhoeff($digitString);
	return $result;
}

function appendVerhoeff($digitString) {
	if( ! is_digits($digitString)) {
		            throw new \InvalidArgumentException(sprintf("Error! Value is restricted to the digit string.", $digitString));
	}
	return (Verhoeff::generate($digitString));
}


function is_digits($string) {
	return preg_match('/^[0-9]+$/', $string);
}

assert(is_digits('5'));
assert( ! is_digits('5ed'));
assert(is_digits('09872350'));
assert( ! is_digits('3d00035430'));
assert( ! is_digits('30003g5430'));
assert("Verhoeff::check(appendVerhoeffAfter15DigitString('123456789012345')) === 0");

$f = fopen('php://stdin', 'r');
while ($line = fgets($f)) {
	$line=rtrim($line);

	$num=$line[3];
	$num=(12 - $num) % 10;
	$line=substr($line, 0, 7) . $num . substr($line, 7);

    echo appendVerhoeffAfter15DigitString($line)."\n";
}
