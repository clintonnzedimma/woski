<?php

namespace Woski\Cli\Utils;

class ControllerMaker {

    protected $name;

    public function __construct($name) {
        $this->name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
    }

    public function create() {
        $className = $this->name;
        $controllerPath = ROOT . "app/Controllers/{$className}.php";

        if (file_exists($controllerPath)) {
            echo "⚠️  Controller already exists: {$className}\n";
            return;
        }

        $template = <<<PHP
<?php

namespace App\Controllers;

class {$className} {

    public function index() {
        return function (\$req, \$res) {
            return \$res->render("index", [
				"variable" => "Hello World!",
			]);
        };
    }
}
PHP;

        file_put_contents($controllerPath, $template);
        echo "✅ Controller created: app/Controllers/{$className}.php\n";
    }
}
