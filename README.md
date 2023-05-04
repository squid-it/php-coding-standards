# PHP Coding Standards - PHP-CS-Fixer Rules
Default coding standard rules for PHP-CS-Fixer.


### Usage

Below you can find an example file named `.php-cs-fixer.dist.php` which should be placed inside your project's root directory.

```php
<?php 

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use SquidIT\PhpCodingStandards\PhpCsFixer\Rules;

$finder = Finder::create()
    ->in(__DIR__);

$overrides = [
    'modernize_types_casting' => false,
];

return (new Config())
    ->setFinder($finder)
    ->setCacheFile('.php-cs-fixer.cache')
    ->setRiskyAllowed(true)    
    ->setRules(Rules::getRules($overrides));
```

### Manual Triggering
Run following command in your project directory, that will run fixer for every `.php` file.
```bash
vendor/bin/php-cs-fixer fix
```
