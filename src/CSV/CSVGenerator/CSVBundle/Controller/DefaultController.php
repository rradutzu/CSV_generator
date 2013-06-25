<?php

namespace CSV\CSVGenerator\CSVBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('CSVGeneratorBundle:Default:index.html.twig', array('name' => $name));
    }

}
