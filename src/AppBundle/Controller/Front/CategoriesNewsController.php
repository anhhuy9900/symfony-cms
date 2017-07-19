<?php
namespace AppBundle\Controller\Front;

use AppBundle\Controller\FrontController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CategoriesNewsController extends FrontController
{
    /**
     * @Route("/categories", name="categories_news_page")
     */
    public function indexAction(Request $request)
    {
        dump(1111);die;
        return $this->render();
    }
}
