<?php

namespace App\Forms;

use Nette\Application\UI\Form;
use Nette\SmartObject;


class EventFormFactory
{
    use SmartObject;

    /**
     * @return Form
     */
    public function create($stories)
    {
        $form = new Form();
        $form->addText('title', 'Název:')
            ->setRequired();
        $form->addSelect('story_id', 'Příběh', $stories)
            ->addCondition($form::EQUAL, 'new')
            ->toggle('story_name');

        $form->addText('story_name', 'Jméno:')
            ->setOption('id', 'story_name');

        $form->addText('place', 'Kde:')
            ->setRequired();
        $form->addText('start', 'Kdy:')
            ->setRequired();
        $form->addText('end', 'Konec:')
            ->setRequired();
        $form->addText('organizator', 'Organizátor:')
            ->setRequired();
        $form->addTextArea('description', 'Obsah:')
            ->setRequired();

        $form->addSubmit('send', 'Uložit');

        return $form;
    }
}