<?php

require('TestClass.php');
require('../src/PHPAnnotationParser.php');

new TestClass();
$parser = new PHPAnnotationParser('TestClass');
$a = $parser->getMethodsAnnotation();
print_r($a);
?>