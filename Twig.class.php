<?php
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

require_once 'vendor/autoload.php';

class TwigGlobalsExtension extends AbstractExtension implements GlobalsInterface {
    public function getGlobals() : array {
        return get_defined_constants();
    }
}

class Twig {
    /** @var Environment $twig */
    public static $twig;
}

$loader = new FilesystemLoader('templates');
Twig::$twig = new Environment($loader, ['debug' => true]);
Twig::$twig->addExtension(new TwigGlobalsExtension());
Twig::$twig->addFilter(new TwigFilter('preg_match_first_group', function($pattern, $subject) {
    if (preg_match($pattern, $subject, $matches)) return $matches[0];
    return null;
}));
