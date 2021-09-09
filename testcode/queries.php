<?php

// old code for php compatibility test, uncomment and run code sniffer php compatibility check


if (empty(trim(" "))) {
    echo "empty() works\n";
}

if (empty([])) {
    echo "short array sytax works\n";
}

echo sys_get_temp_dir(). "\n";

throw new Exception("Bier");

// Deprecation of static calls on non static methods
class foo {
    function bar() {
        echo 'I am not static!';
    }
}

foo::bar();

mcrypt_cbc("asd");

$bar = &new foo();

