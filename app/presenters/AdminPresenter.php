<?php

namespace App\Presenters;

use App\Exceptions\HandledRequestException;
use App\Model\PostManager;
use App\Model\RequestManager;
use Nette;
use App\Forms\PostCreateFormFactory;
use Nette\Utils\Paginator;

class AdminPresenter extends BasePresenter
{
    /** @var PostCreateFormFactory */
    private $postCreateFormFactory;

    /** @var PostManager @inject */
    public $postManager;

    /** @var RequestManager @inject */
    public $requestManager;

    public function __construct(Nette\Database\Context $db, PostCreateFormFactory $createRequestFormFactory)
    {
        parent::__construct($db);
        $this->postCreateFormFactory = $createRequestFormFactory;
    }

    public function startup()
    {
        parent::startup();
        if (!$this->user->isInRole('admin')) {
            $this->flashMessage('Nejsi opravnen tu byt', 'warning');
            $this->redirect('Homepage:');
        }
    }

    public function actionCreate()
    {
        //nope
        // $this->db->table('web_posts');
        $this['postForm']->setDefaults(['created' => (new \DateTime())->format('Y-m-d H:i:s')]);
    }

    /**
     * @param int $id
     */
    public function actionPosts($id = 1)
    {
        // Zjistíme si celkový počet publikovaných článků
        $articlesCount = $this->postManager->getPublishedPostsCount();

        // Vyrobíme si instanci Paginatoru a nastavíme jej
        $paginator = new Paginator;
        $paginator->setItemCount($articlesCount); // celkový počet článků
        $paginator->setItemsPerPage(50); // počet položek na stránce
        $paginator->setPage($id); // číslo aktuální stránky

        // Z databáze si vytáhneme omezenou množinu článků podle výpočtu Paginatoru
        $articles = $this->postManager->getPosts($paginator->getLength(), $paginator->getOffset());

        // kterou předáme do šablony
        $this->template->articles = $articles;
        // a také samotný Paginator pro zobrazení možností stránkování
        $this->template->paginator = $paginator;
    }

    public function actionEdit($id)
    {
        $post = $this->postManager->getPost($id);
        if (!$post) {
            $this->flashMessage('Clanek nenalazen', 'error');
            $this->redirect('Admin:');
        }
        $this['postForm']->setDefaults($post->toArray());
    }

    public function actionRequests()
    {
        $this->template->requests = $this->requestManager->getRequests();
    }

    public function handleDelete($id)
    {
        $this->postManager->deletePost($id);

        $this->flashMessage('Clanek byl uspesne smazan', 'info');
    }

    public function handleRequestAccept($id)
    {
        try {
            $this->requestManager->handleRequest($id, 'accept');
        } catch (HandledRequestException $e) {
            $this->flashMessage('Tato žádost byla již vyřízena', 'error');
        }
    }

    public function handleRequestRefuse($id)
    {
        try {
            $this->requestManager->handleRequest($id, 'refuse');
        } catch (HandledRequestException $e) {
            $this->flashMessage('Tato žádost byla již vyřízena', 'error');
        }
    }

    public function handleRequestDelete($id)
    {
        try {
            $this->requestManager->handleRequest($id, 'delete');
        } catch (HandledRequestException $e) {
            $this->flashMessage('Tato žádost byla již vyřízena', 'error');
        }
    }

    protected function createComponentPostForm()
    {
        return $this->postCreateFormFactory->create();
    }

    public function postFormSucceeded($form, $values)
    {
        $id = $this->getParameter('id');
        if ($id) {
            $this->postManager->updatePost($id, $values);
        } else {
            $this->postManager->addPost($values);
        }

        $this->flashMessage("Příspěvek byl úspěšně publikován.", 'success');
        $this->redirect('Admin:');
    }

    public function actionOnline()
    {
        $this->template->players = $this->db->query('SELECT c.*, a.username, cl.name as cln, r.name as rn FROM characters.characters c LEFT JOIN auth.account a ON c.account = a.id LEFT JOIN characters._dbc_classes cl ON c.class = cl.id LEFT JOIN characters._dbc_rases r ON c.race = r.id WHERE c.online = ?;', 1);
    }
}
