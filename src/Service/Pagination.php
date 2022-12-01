<?php

namespace App\Service;

use Exeption;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class Pagination{

    private $entityClass;
    private $limit=10;
    private $currentPage=1;
    private $manager;

    private $twig;
    private $route;

    private $templatePath;

    public function __construct(EntityManagerInterface $manager, Environment $twig, RequestStack $request, $templatePath){

        $this->route = $request->getCurrentRequest()->attributes->get('_route');

        $this->manager = $manager;
        $this->twig = $twig;

        $this->templatePath = $templatePath;

    }



    public function display(){

        // appelle le moteur twig et on précise quel template on veut utiliser
        $this->twig->display($this->templatePath,[

            // options nécessaires à l'affichage des données

            // variables : route / page / pages
            'page'=>$this->currentPage,
            'pages'=>$this->getPages(),
            'route'=>$this->route
        ]);
    }

    //1- utiliser la pagination à partir de n'importe quelle entité / on devra préciser l'entité concernée

        public function setEntityClass($entityClass){
            
            // ma donnée entityClass = donnée qui va m'être envoyé
            $this->entityClass = $entityClass;
            
            return $this;
        }
        
        public function getEntityClass(){
            return $this->entityClass;
        }

    //2- quelle est la limite ?

        public function getLimit(){
            return $this->limit;
        }

        public function setLimit($limit){

            $this->limit = $limit;

            return $this;
        }

    //3- sur quelle page je me trouve actuellement

        public function getPage(){
            return $this->currentPage;
        }

        public function setPage($page){
            $this->currentPage = $page;
            return$this;
        }

    //4- on va chercher le nombre de page au total pages

        public function getData(){

            if(empty($this->entityClass)){

                throw new Exeption("setEntityClass n'a pas été renseigné dans le controller correspondant");
            }

            // calculer l'offset 
            $offset = $this->currentPage * $this->limit - $this->limit;

            // demande au repository de trouver les éléments

            // on va chercher le bon repository
            $repo = $this->manager->getRepository($this->entityClass);

            // on construit notre requete
                // method find(n° de l'id) => trouve un objet par son id
                // method findOneBy() => trouve une donnée via des critères de recherche
                // method findBy() => trouve plusieurs données grâce à des critères
            $data = $repo->findBy([],[],$this->limit,$offset);

            return $data;
        }

        public function getPages(){

            $repo = $this->manager->getRepository($this->entityClass);
            
            $total = count($repo->findAll());
            // ceil() remonte un nombre entier pour le numéro de page
            $pages = ceil($total/$this->limit);

            return $pages;
        }

        public function getRoute(){

            return $this->route;
        }

        public function setRoute($route){

            $this->route = $route;
            return $this;
        }

        public function getTemplatePath(){

            return $this->templatePath;
        }

        public function setTemplatePtah($templatePath){

            $this->getTemplatePath = $templatePath;

            return $this;
        }

}