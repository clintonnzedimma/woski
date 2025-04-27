<?php 
function _import($file){
	if (!file_exists($file)) {
		throw new \Exception("Woski: Cant Import File '$file' to your project");
	}
	return require $file;
 }
 
 
 
 if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}

if (!function_exists('config')) {
    function config($key, $default = null) {
        static $configs = [];

        // Split key: e.g. "app.name" => ["app", "name"]
        $parts = explode('.', $key);
        $file = $parts[0];
        $keys = array_slice($parts, 1);

        // Load config file if not cached
        if (!isset($configs[$file])) {
            $path = ROOT . "config/{$file}.php";
            if (file_exists($path)) {
                $configs[$file] = require $path;
            } else {
                return $default;
            }
        }

        $value = $configs[$file];

        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }
}


if (!function_exists('validate')) {
    function validate($input, $rules)
    {
        return \Woski\Support\Validator::make($input, $rules,);
    }
}
