<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;
use SquidIT\PhpCodingStandards\PhpCsFixer\Rules;

$finder = Finder::create()
    ->in(__DIR__)
    ->exclude(['var', 'vendor']);

$phpFixer = new Config();

return $phpFixer
    ->setFinder($finder)
    ->setCacheFile('var/cache/.php-cs-fixer.cache')
    ->setRiskyAllowed(true)
    ->setRules(Rules::getRules())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());
