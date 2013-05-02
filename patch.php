<?php
/**
* Copyright (c) 2013 Muntasir Mohiuddin<muntasir.mohiuddin@gmail.com>
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
namespace MonkeyPatch;

class Patch {

	static $_default	= array(
		'property' => array(
				'reference' => NULL,
				'visbility' => 'public',
				'value' => 'True',
			),

		'method' => array(
				'reference' => NULL,
				'visbility' => 'public',
				'code' => 'return True;',
			),

	);

	/**
	* Remove the namespace from the name and return defined name
	* 
	* @param string $name name to remove namespace from
	* @return string sanitized name without the namespace
	*/
	public static function removeNamespace($name) {
		$name_segments = explode("\\", $name);
		return end($name_segments);
	}

	/**
	* Returns the full qualified path of the provided filename.
	* If the file does not exist in that locauon, returns FALSE
	* 
	* @param string $file_name name of the file
	* @return mixed False if file does not exist. Full path as string if does.
	*/
	public static function getQualifiedPath($file_name) {
		$qualified_file = sprintf("%s/patches/%s.php", dirname(__FILE__), $file_name);

		if(!file_exists($qualified_file)) {
			return False;
		} 

		return $qualified_file;
	}

	/**
	* Patch a class.
	* 
	* @param $string $class_name Classname which is getting patched.
	* @return boolean TRUE on success or FALSE on failure	
	*/
	static function cls($class_name) {
		$_class_name = self::removeNamespace($class_name);  # find the name of the class by removing namespace(s)
		
		# full path to the patch class
		$qualified_file = self::getQualifiedPath($_class_name);
		
		if($qualified_file) { // there is a file by that name
			/* TODO: fix this bug */
			class_exists($_class_name);  # for some reason this drops the class definition!!! 
			
			# import classe and force override - mainly for namespaced classes
			return runkit_import($qualified_file, 
				RUNKIT_IMPORT_CLASSES|RUNKIT_IMPORT_CLASS_METHODS|RUNKIT_IMPORT_CLASS_CONSTS|RUNKIT_IMPORT_CLASS_PROPS|RUNKIT_IMPORT_OVERRIDE);
		}

		return False;  # by default return False
	}  

	/**
	* Patch a method of a give class.
	* 
	* @param $string $class_name Classname of the method including namespace.
	* @param $string $method_name Method to patch.
	* @param $string $code Custom code to patch the method with. If no code is passed 
	*						the system will try to load code from 
	*						patches/<class name>.<method name>.php file. If that file is 
	*						not present or has no content applies default method code.
	* @return boolean TRUE on success or FALSE on failure	
	*/
	static function method($class_name, $method_name, $code=NULL){
		$class = new \ReflectionClass($class_name);	

		$method = $class->getMethod($method_name);
		
		$visbility = RUNKIT_ACC_PUBLIC;  # usually public methods get changed

		if($method->isPrivate()){  # if method is private
			$visbility = RUNKIT_ACC_PRIVATE;
		} 

		if($method->isStatic()) {  # if the method is of static reference
			$visbility = $visbility|RUNKIT_ACC_STATIC;
		}
		
		$parameters = array();  # save parameters here

		foreach($method->getParameters() as $parameter){
			$parameters[] = "$" . $parameter->name;
		}

		# no code passed, check if there are any flle in patches
		if(empty($code)){
			# the file name is required to be <class_name>.<method name>
			$sanitized_class_name = self::removeNamespace($class_name);
			$qualified_file = self::getQualifiedPath($sanitized_class_name . "." . $method_name);
			
			if($qualified_file) {  # if file exists
				$code = file_get_contents($qualified_file);
			} 
		}
		
		return runkit_method_redefine(
			$class_name,  # class name
			$method_name,  # method name 
			implode(",", $parameters),  # paramters
			# if code is either empty ot not set(NULL), assing default code
			(!empty($code)) ? $code : self::$_default['method']['code'],  # custom code
			$visbility  # visibility
		);
	}
}
?>