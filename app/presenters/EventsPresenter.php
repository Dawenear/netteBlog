<?php

namespace App\Presenters;

use App\Forms\EventFormFactory;
use App\Model\EventManager;
use Nette;

class EventsPresenter extends BasePresenter
{
    /** @var EventManager @inject */
    public $eventManager;
    /** @var EventFormFactory */
    private $eventFormFactory;

    /**
     * @param Nette\Database\Context $db
     * @param EventFormFactory $eventFormFactory
     */
    public function __construct(Nette\Database\Context $db, EventFormFactory $eventFormFactory)
    {
        parent::__construct($db);
        $this->eventFormFactory = $eventFormFactory;
    }

    public function renderDefault()
    {
        $this->template->events = $this->eventManager->getEvents();
        $this->template->startedEvents = $this->eventManager->getCurrentEvents();
    }

    public function renderEnded()
    {
        $this->template->events = $this->eventManager->getEndedEvents();
        $this->template->startedEvents = $this->eventManager->getCurrentEvents();
    }

    public function renderStory($id)
    {
        $this->template->events = $this->eventManager->getStoryEvents($id);
        $this->template->now = new \DateTime;
    }

    public function actionCreate()
    {
        if (!$this->user->isInRole('storyteller') && !$this->user->isInRole('admin')) {
            $this->flashMessage('Nemáš tu co dělat', 'warning');
            $this->redirect('Events:');
        }
        $time = (new \DateTime())->format('Y-m-d H:i:s');
        $this['eventForm']->setDefaults(['start' => $time, 'end' => $time]);
    }


    public function renderDetail($id)
    {
        if (!$event = $this->eventManager->getEvent($id)) {
            $this->redirect('Events:');
        }

        $this->template->story = $event->story ? $event->story->name . ' část ' . $event->part : 'Jednorázový';
        $this->template->event = $event;
    }

    public function actionEdit($id)
    {
        if (!$this->user->isInRole('storyteller') && !$this->user->isInRole('admin')) {
            $this->flashMessage('Nemáš tu co dělat', 'warning');
            $this->redirect('Events:');
        }

        if (!$id) {
            $this->flashMessage('Nejde editovat neexistující příspěvek', 'warning');
            $this->redirect('Events:');
        }

        if (!$event = $this->eventManager->getEvent($id)) {
            $this->flashMessage('Nejde editovat neexistující příspěvek', 'warning');
            $this->redirect('Events:');
        }

        $this['eventForm']->setDefaults($event->toArray());
    }

    protected function createComponentEventForm()
    {
        $stories = $this->eventManager->getAllStories();
        $stories = $this->eventManager->orderStories($stories);

        $form = $this->eventFormFactory->create($stories);
        $form->onSuccess[] = [$this, 'postEventFormSucceeded'];

        return $form;
    }

    public function postEventFormSucceeded($form, $values)
    {
        if ($values->start > $values->end) {
            $form['start']->addError('Začátek nesmí být po skončení eventu');
            return;
        }

        $id = $this->getParameter('id');
        if ($id) {
            $this->eventManager->updateEvent($id, $values);
        } else {
            $this->eventManager->addEvent($values);
        }

        $this->flashMessage("Event publikován.", 'success');
        $this->redirect('Events:');
    }
}
