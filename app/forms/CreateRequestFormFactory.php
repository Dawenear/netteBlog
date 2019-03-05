<?php

namespace App\Forms;

use App\Exceptions\DuplicateEmailException;
use App\Model\DuplicateNameException;
use App\Model\RequestManager;
use Nette;
use Nette\Application\UI\Form;

class CreateRequestFormFactory
{
    use Nette\SmartObject;

    /** @var RequestManager */
    private $requestManager;


    public function __construct(RequestManager $requestManager)
    {
        $this->requestManager = $requestManager;
    }


    /**
     * @param callable $onSuccess
     *
     * @return Form
     */
    public function create(callable $onSuccess)
    {
        $form = new Form();
        $form->addText('username', 'Přihlašovací jméno:')
            ->setRequired();
        $form->addText('email', 'Email:')
            ->setRequired();

        $form->addTextArea('experiences', 'Zkušenosti:');
        $form->addTextArea('recommendation', 'Doporučení:');
        $form->addTextArea('notes', 'Poznámky:');

        $form->addSubmit('send', 'Zažádat o účet');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess) {
            try {
                $this->requestManager->createRequest($values);
            } catch (DuplicateNameException $e) {
                $form['username']->addError('Username is already taken.');
                return;
            } catch (DuplicateEmailException $e) {
                $form['email']->addError('Email is already used.');
                return;
            }
            $onSuccess();
        };

        return $form;
    }
}