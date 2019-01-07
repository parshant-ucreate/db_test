<?php

if(!function_exists('ObjectToArray') ){
	function ObjectToArray($input)
	{
		if ($input) {
            $input = json_decode(json_encode($input, 1), 1);
            return  $input;
        }
        return [];
	}
}

if(!function_exists('pr') ){
	function pr($input)
	{
		echo '<pre>';
		print_r($input);
		echo '</pre>';
	}
}

?>