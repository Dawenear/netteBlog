<?php

namespace App\Presenters;

use App\Model\EventManager;
use App\Model\PostManager;
use Nette;
use Nette\Utils\Paginator;

class HomepagePresenter extends BasePresenter
{
    /** @var PostManager @inject */
    public $postManager;
    /** @var EventManager @inject */
    public $eventManager;


    /**
     * @param int $id
     */
    public function renderDefault($id = 1)
    {
        // Zjistíme si celkový počet publikovaných článků
        $articlesCount = $this->postManager->getPublishedPostsCount();

        // Vyrobíme si instanci Paginatoru a nastavíme jej
        $paginator = new Paginator;
        $paginator->setItemCount($articlesCount); // celkový počet článků
        $paginator->setItemsPerPage(10); // počet položek na stránce
        $paginator->setPage($id); // číslo aktuální stránky

        // Z databáze si vytáhneme omezenou množinu článků podle výpočtu Paginatoru
        $articles = $this->postManager->getPosts($paginator->getLength(), $paginator->getOffset());

        // kterou předáme do šablony
        $this->template->articles = $articles;
        // a také samotný Paginator pro zobrazení možností stránkování
        $this->template->paginator = $paginator;

        $this->template->startedEvents = $this->eventManager->getCurrentEvents();

    }
}
