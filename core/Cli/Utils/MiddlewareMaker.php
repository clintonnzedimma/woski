<?php

namespace Woski\Cli\Utils;

class MiddlewareMaker {

    protected $name;

    public function __construct($name) {
        $this->name = preg_replace('/[^a-zA-Z0-9_]/', '', $name);
    }

    public function create() {
        $className = $this->name;
        $controllerPath = ROOT . "app/Middlewares/{$className}.php";

        if (file_exists($controllerPath)) {
            echo "⚠️  Controller already exists: {$className}\n";
            return;
        }

        $template = <<<PHP
<?php

namespace App\Middlewares;

class {$className} {

    public function handle() {
        return function (\$req, \$res, \$pipe) {
         // return this if you want block the request
        // return \$pipe->block();
        };
    }
}
PHP;

        file_put_contents($controllerPath, $template);
        echo "✅ Controller created: app/Middlewares/{$className}.php\n";
    }
}
