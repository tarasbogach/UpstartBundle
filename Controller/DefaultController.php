<?php

namespace SfNix\UpstartBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('UpstartBundle:Default:index.html.twig');
    }
}
