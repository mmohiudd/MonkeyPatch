<?php
include_once(dirname(__FILE__) . "/bootstrap.php");  # include classes
use app\classes\MyClass;

$a = rand(1,10);
$b = rand(1,10);
$result = MyClass::add($a, $b);
echo sprintf("\n MyClass::add(%s,%s) = %s\n\n", $a, $b, $result);
?>