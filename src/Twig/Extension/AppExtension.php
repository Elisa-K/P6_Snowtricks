<?php

namespace App\Twig\Extension;

use App\Twig\Runtime\AppExtensionRuntime;
use Symfony\Component\String\Inflector\FrenchInflector;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new TwigFunction('inflector', [$this, 'inflector']),
        ];
    }

    public function inflector(int $count, string $string): string
    {
        $inflector = new FrenchInflector();
        $str = $count < 2 ? $inflector->singularize($string) : $inflector->pluralize($string);
        return $count . " " . $str[0];
    }
}