:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
php vendor\bin\ecs check vendor/code4nix/contao-project-bundle/src --fix --config vendor/code4nix/contao-project-bundle/tools/ecs/config.php
php vendor\bin\ecs check vendor/code4nix/contao-project-bundle/contao --fix --config vendor/code4nix/contao-project-bundle/tools/ecs/config.php
::php vendor\bin\ecs check vendor/code4nix/contao-project-bundle/tests --fix --config vendor/code4nix/contao-project-bundle/tools/ecs/config.php
