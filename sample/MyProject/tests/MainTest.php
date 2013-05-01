<?php
use MonkeyPatch\Patch;
use app\classes\MyClass;

class MainTest extends PHPUnit_Framework_TestCase {	
	public function test_method() {
		# patch a class
		//Patch::cls('app\classes\Calculator');

		# patch a method by reading code from a file
		# the file name is required to be 
		# <class_name>.<method name>
		//Patch::method('app\classes\Calculator', 'add');

		# patch a method by passing custm code
		//Patch::method('app\classes\Calculator', 'add', "return 5;");

		$result = MyClass::add(1, 1);			
		$this->assertEquals(5, $result);
	}
}

?>