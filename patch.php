<?php
namespace MonkeyPatch;

class Patch {
	static $_default	= array(
		'method' => array(
				'visbility' => 'public',
				'code' => 'return True;',
			),
		'property' => array(
				'visbility' => 'public',
				'value' => 'True',
			),
	);

	/**
	* Apply a patch from the patches folder
	* 
	* @param string $class_name name of the class that requires patching
	* @return boolean returns True/False based on if the patch loaded successfully
	*/
	public static function apply($class_name){
		try{
			require_once(sprintf("patches/%s.php", $class_name));
		} catch(Exception $e){
			return False;
		}

		return True;
	}

	/**
	* Redefine a function based on the config provided.  
	* 
	*  		
	*/
	public static function redefine($config=NULL){
		// is class name provided ?
		if (is_null($config) || empty($config['class_name'])){
			return False;
		}

		$redefined_properties_code = "";
		foreach($config['properties'] as $property){
			$name = $property['name'];
			$visbility = (!empty($property['visbility'])) ? $property['visbility'] : self::$_default['property']['visbility'];
			$value = (!empty($property['value'])) ? $property['value'] : self::$_default['property']['value'];

			$redefined_properties_code .= sprintf("\t%s $%s = %s;\n", $visbility, $name, $value);
		}


		$redefined_methods_code = "";
		foreach($config['methods'] as $method){
			$name = $method['name'];
			
			$visbility = (!empty($method['visbility'])) ? $method['visbility'] : self::$_default['method']['visbility'];
			

			// if method args passed
			$args = "";
			if(!empty($method['args'])){
				$args = sprintf("%s", implode(",", $method['args']));
			}

			$code = (!empty($method['code'])) ? $method['code'] : self::$_default['method']['code'];

			$redefined_methods_code .= sprintf("\t%s function %s(%s){\n%s\n}", $visbility, $name, $args, $code);
		}
		$code = sprintf("class %s {\n%s\n\n%s\n}", $config['class_name'], $redefined_properties_code, $redefined_methods_code);
		
		eval($code);
		
	} // end of redefine 
}

?>