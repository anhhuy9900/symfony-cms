<?php
namespace AppBundle\Controller\Front;

use AppBundle\Controller\FrontController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends FrontController
{
    /**
     * @Route("/", name="home_page")
     */
    public function indexAction(Request $request)
    {

        $this->image_helper = $this->container->get('app.image_helper');

        $repository = $this->getDoctrine()->getRepository('AppBundle:NewsEntity');
        $results = $repository->list_news_get_all();
        if(!empty($results)){
            foreach($results as $value){
                $image_path = $this->getParameter('upload_dir').'/'. $value->getImage();
                $resize_path = $this->getParameter('upload_dir').'/resize/';
                $resize_data = $this->image_helper->resizeImage($image_path, $resize_path, 360, 206);
                $value->image_url = $this->get('request')->getBasePath() . '/web/uploads/resize/' . $resize_data['image_name'];

            }
        }

        $this->data['title'] = 'Home Page';
        $this->data['results'] = $results;
        return $this->render('@frontend/home/index.html.twig', $this->data);

    }
}