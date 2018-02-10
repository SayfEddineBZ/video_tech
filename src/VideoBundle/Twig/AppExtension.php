<?php
/**
 * Created by PhpStorm.
 * User: saifeddine
 * Date: 10/02/18
 */

namespace VideoBundle\Twig;
use Symfony\Bridge\Doctrine\RegistryInterface;



class AppExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    public function getGlobals()
    {
        $rc = $this->doctrine->getRepository('VideoBundle:Categorie');
        $categories = $rc->findAll();
        $rf = $this->doctrine->getRepository('VideoBundle:Film');
        $films = $rf->findAll();

        return array(
            'categories' => $categories,
            'filmN' => count($films)
        );
    }

    public function getName()
    {
        return 'app_extension';
    }
}