<?php

namespace Site\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Site\MainBundle\Form\Email;
use Site\MainBundle\Form\Type\EmailType;

include_once("Mobile_Detect.php");

class MainController extends Controller
{

    public function indexAction()
    {
        $repository_news = $this->getDoctrine()
            ->getRepository('SiteMainBundle:Page');
        $pages = $repository_news->findAll();
        $page = $repository_news->findOneBySlug('glavnaya_3');
        $page2 = $repository_news->findOneBySlug('glavnaya_4');
        $repository_catalog = $this->getDoctrine()
            ->getRepository('SiteMainBundle:Catalog');
        $catalog = $repository_catalog->findAll();
        $repository_portfolio = $this->getDoctrine()
            ->getRepository('SiteMainBundle:Portfolio');
        $imagesPortfolio = $repository_portfolio->getRandom();

        $repository_factory = $this->getDoctrine()
            ->getRepository('SiteMainBundle:Factory');
        $factory = $repository_factory->findAll();

        $imagesCatalog = array();
        $i = 0;
        foreach ($catalog as $cat) {
            $imagesCatalog[$i]['id'] = $cat->getId();
            $img = $cat->getImages();
            $imagesCatalog[$i]['photo'] = $img[0]->getSrc();
            $imagesCatalog[$i]['desciption'] = urlencode($cat->getDescription());
            $i++;
        }

        $mobile = 0;
        $smart = 0;
        $tablet = 0;
        $Mobile_Detect = new Mobile_Detect();
        if($Mobile_Detect->isTablet() || $Mobile_Detect->isMobile()){
            $mobile = 1;
        }
        if($Mobile_Detect->isTablet()){
            $tablet = 1;
        }
        $params = array(
            "pages" => $pages,
            'page' => $page,
            'page2' => $page2,
            "catalog" => $catalog,
            "factory" => $factory,
            'portfolio' => $imagesPortfolio,
            "imagesPortfolio" => json_encode($imagesPortfolio),
            "imagesCatalog" => json_encode($imagesCatalog),
            "mobile" => $mobile,
            "tablet" => $tablet
        );
        return $this->render('SiteMainBundle:Main:index.html.twig', $params);
    }

    public function pageAction($slug){
        $repository_page = $this->getDoctrine()
            ->getRepository('SiteMainBundle:Page');
        $page = $repository_page->findOneBy(array("slug" => $slug));
        $params = array(
            "page" => $page,
            "slug" => $slug
        );
        return $this->render('SiteMainBundle:Main:page.html.twig', $params);
    }

    public function scanerAction(){
        $audio = array();
        $directory = 'music';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
        $i = -1;

//      Функция, которая полсчитывает количетсво файлов
        $countFiles = function ($dir){
            $count = 0;
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false){
                    if (!in_array($file, array('.', '..')) && !is_dir($dir.$file))
                        if(substr(pathinfo($file)['filename'], 0, 1) != '.'){
                            $count++;
                        }
                }
            }

            return $count;
        };

        $iterator->rewind();
        while($iterator->valid()) {
            if (!$iterator->isDot()) {

//              Флаг существования плейлиста
                $fl = 0;

//              Проверяем есть ли такой плейлист
                if(count($audio) > 0){
                    foreach($audio as $a){
                        if(isset($a['name'])){
                            if($a['name'] == $iterator->getSubPath()){
                                $fl = 1;
                                break;
                            }
                        }
                    }
                }

//              Если такого плейлиста нет, то добавляем его
                if($fl == 0){
                    if(mb_strlen($iterator->getSubPath()) > 0 && mb_detect_encoding($iterator->getSubPath()) == 'ASCII' && $iterator->getSubPath() != ""){

                        $count_files = $countFiles('music/' . $iterator->getSubPath());

                        if($count_files > 0){
                            $i++;
                            $audio[$i] = array(
                                'name' => $iterator->getSubPath(),
                                'iconv' => mb_detect_encoding($iterator->getSubPath()),
                                'selected' => 0,
                                'seeking' => 0,
                                'numberTrack' => 0,
                                'countTracks' => $countFiles('music/' . $iterator->getSubPath())
                            );
                        }

                    }
                }

                $pathinfo = pathinfo($iterator->key());

//              Добавляем музыку в плейлист
                if(substr($pathinfo['filename'], 0, 1) != '.' && $pathinfo['filename'] != 'playlist' && $i >= 0){
                    $audio[$i]['audio'][] = array(
                        'name' => $pathinfo['filename'],
                        'linkGooglePlay' => 'https://play.google.com/store/search?q=' . urlencode($pathinfo['filename']),
                        'linkItunes' => 'http://itunes.com/search?term=' . urlencode($pathinfo['filename']),
                        'file' => $iterator->key()
                    );
                }

            }

            $iterator->next();
        }

        $sort = array(
            "Pre Party Sound" => 0,
            "Club Sound" => 1,
            "After Party Sound" => 2,
            "Night Sound" => 3,
            "Morning Sound" => 4,
            "Day Sound" => 5,
            "Evening Songs" => 6,
            "Morning Songs" => 7,
            "Relax Music" => 8,
            "Radio Release Eng" => 9,
            "Radio Release Russ" => 10,
            "Утренние Песни" => 11,
            "Дневные Композиции" => 12,
            "Вечерние Треки" => 13,
            "Ночные Мелодии" => 14,
            "9_12" => 15
        );

        $audioCopy = array();

        foreach($sort as $key => $s){
            foreach($audio as $key2 => $a){
                if($a['name'] == $key){
                    $audioCopy[] = $audio[$key2];
//                    unset($audio[$key2]);
                }
            }
        }

        file_put_contents('music/playlist.json', json_encode($audioCopy, TRUE));
        return new Response(json_encode($audioCopy, TRUE), 200);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function emailAction(Request $request){
        $entity = new Email();
        $form = $this->createForm(new EmailType(), $entity);

        if($request->isMethod('POST')){
            $form->handleRequest($request);

            if($form->isValid()){
                $swift = \Swift_Message::newInstance()
                    ->setSubject('Письмо с сайта')
                    ->setFrom(array($this->container->getParameter('email_from') => "Письмо с сайта"))
                    ->setTo($this->container->getParameter('emails_admin'))
                    ->setBody(
                        $this->renderView(
                            'SiteMainBundle:Email:message.html.twig',
                            array(
                                'form' => $entity
                            )
                        )
                        , 'text/html'
                    );
                $this->get('mailer')->send($swift);

                return new Response('Сообщение успешно отправлено!', 200);
            }

            return new Response('Ошибка!', 500);
        }

        return $this->render('SiteMainBundle:Email:form.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
