<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;


class PostCreateFormFactory
{
    use Nette\SmartObject;

    /** @var Model\PostManager */
    private $postManager;


    public function __construct(Model\PostManager $postManager)
    {
        $this->postManager = $postManager;
    }


    /**
     * @return Form
     */
    public function create()
    {
        $form = new Form();
        $form->addText('title', 'Titulek:')
            ->setRequired();
        $form->addTextArea('content', 'Obsah:')
            ->setRequired();

        $form->addText('created', 'Publikovat:')
            ->setType('datetime');

        $form->addSubmit('send', 'Uložit a publikovat');
        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }
}
