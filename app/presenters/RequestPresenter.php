<?php

namespace App\Presenters;

use App\Forms\CreateRequestFormFactory;
use App\Model\RequestManager;
use Nette;

class RequestPresenter extends BasePresenter
{
    /** @var CreateRequestFormFactory */
    protected $createRequestFormFactory;

    /** @var RequestManager */
    protected $requestManager;

    public function __construct(Nette\Database\Context $db, CreateRequestFormFactory $createRequestFormFactory, RequestManager $requestManager)
    {
        parent::__construct($db);
        $this->createRequestFormFactory = $createRequestFormFactory;
        $this->requestManager = $requestManager;
    }

    protected function createComponentCreateRequestForm()
    {
        return $this->createRequestFormFactory->create(function () {
            $this->flashMessage('Žádost zaznamenána. Ověříme ji co nejdříve.', 'info');
            $this->redirect('Homepage:');
        });
    }
}