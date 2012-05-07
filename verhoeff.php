<?php
# @author Semyon Velichko

class Verhoeff {
 
    static public $d = array(
        array(0,1,2,3,4,5,6,7,8,9),
        array(1,2,3,4,0,6,7,8,9,5),
        array(2,3,4,0,1,7,8,9,5,6),
        array(3,4,0,1,2,8,9,5,6,7),
        array(4,0,1,2,3,9,5,6,7,8),
        array(5,9,8,7,6,0,4,3,2,1),
        array(6,5,9,8,7,1,0,4,3,2),
        array(7,6,5,9,8,2,1,0,4,3),
        array(8,7,6,5,9,3,2,1,0,4),
        array(9,8,7,6,5,4,3,2,1,0)
    );
 
    static public $p = array(
        array(0,1,2,3,4,5,6,7,8,9),
        array(1,5,7,6,2,8,3,0,9,4),
        array(5,8,0,3,7,9,6,1,4,2),
        array(8,9,1,6,0,4,3,5,2,7),
        array(9,4,5,3,1,2,6,8,7,0),
        array(4,2,8,6,5,7,3,9,0,1),
        array(2,7,9,3,8,0,6,4,1,5),
        array(7,0,4,6,9,1,3,2,5,8)
    );
 
    static public $inv = array(0,4,3,2,1,5,6,7,8,9);
 
    static function calc($num) {
        if(!preg_match('/^[0-9]+$/', $num)) {
            throw new \InvalidArgumentException(sprintf("Error! Value is restricted to the number, %s is not a number.",
                                                    $num));
        }
 
        $r = 0;
        foreach(array_reverse(str_split($num)) as $n => $N) {
            $r = self::$d[$r][self::$p[($n+1)%8][$N]];
        }
        return self::$inv[$r];
    }
 
    static function check($num) {
        if(!preg_match('/^[0-9]+$/', $num)) {
            throw new \InvalidArgumentException(sprintf("Error! Value is restricted to the number, %s is not a number.",
                                                    $num));
        }
 
        $r = 0;
        foreach(array_reverse(str_split($num)) as $n => $N) {
            $r = self::$d[$r][self::$p[$n%8][$N]];
        }
        return $r;
    }
 
    static function generate($num) {
        return sprintf("%s%s", $num, self::calc($num));
    }
 
    static function validate($num) {
        return self::check($num) === 0;
    }
 
}
 
# Some tests and also usage examples
assert("Verhoeff::calc('75872') === 2");
assert("Verhoeff::check('758722') === 0");
assert("Verhoeff::calc('12345') === 1");
assert("Verhoeff::check('123451') === 0");
assert("Verhoeff::calc('142857') === 0");
assert("Verhoeff::check('1428570') === 0");
assert("Verhoeff::calc('123456789012') === 0");
assert("Verhoeff::check('1234567890120') === 0");
assert("Verhoeff::calc('8473643095483728456789') === 2");
assert("Verhoeff::check('84736430954837284567892') === 0");
 
assert("Verhoeff::generate('12345') === '123451'");
assert("Verhoeff::validate('123451') === true");
assert("Verhoeff::validate('122451') === false");
assert("Verhoeff::validate('128451') === false");
