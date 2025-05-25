<?php

namespace Woski\Cli;

use Woski\Cli\Utils\ModelMaker;
use Woski\Cli\Utils\ControllerMaker;
use Woski\Cli\Utils\MiddlewareMaker;

final class Cli {

    public function __construct()
    {
        $options = getopt(null, [
            'run',
            'port:',
            'help',
            'make:model:',
            'make:controller:',
            'make:middleware:',
            'table:',
        ]);

        // Run the app server
        if (isset($options['run'])) {
            $port = $options['port'] ?? ($_ENV['WOSKIPHP_PORT'] ?? 3000);
            if (strlen($port) < 4) {
                echo "❌ Invalid port: must be 4 digits or more.\n";
                exit;
            }

            echo "🔌 Your Woski App is running. Listening on http://localhost:$port\n\n";
            passthru("php -S localhost:$port");
            return;
        }

        // Show help
        if (isset($options['help'])) {
            echo "🌀 WoskiPHP CLI Usage\n\n";
            echo "php woski [command] [options]\n\n";
            echo "--run\t\t\tStart dev server on port 3000 (or env WOSKIPHP_PORT)\n";
            echo "--run --port=PORT\tStart server on custom port\n";
            echo "--make:controller=NAME\tGenerate a controller\n";
            echo "--make:middleware=NAME\tGenerate middleware\n";
            echo "--make:model=NAME\tGenerate a model\n";
            echo "--table=table_name\t(Optional) Set table name for model\n";
            return;
        }

        // Create model
        if (isset($options['make:model'])) {
            $modelMaker = new ModelMaker($options['make:model']);
            $tableName = $options['table'] ?? null;
            if ($tableName) {
                $modelMaker->create($tableName);
            }
            return;
        }

        // Create controller
        if (isset($options['make:controller'])) {
            $maker = new ControllerMaker($options['make:controller']);
            $maker->create();
            return;
        }

        // Create middleware
        if (isset($options['make:middleware'])) {
            $maker = new MiddlewareMaker($options['make:middleware']);
            $maker->create();
            return;
        }

        // ❌ If no known command matched
        echo "❌ Unknown command or missing options.\n";
        echo "Run: php woski --help\n";
    }
}
