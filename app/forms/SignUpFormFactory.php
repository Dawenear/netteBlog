<?php

namespace App\Forms;

use App\Model;
use Nette;
use Nette\Application\UI\Form;
use App\Exceptions\InvalidTokenException;


class SignUpFormFactory
{
    use Nette\SmartObject;

    const PASSWORD_MIN_LENGTH = 7;

    /** @var FormFactory */
    private $factory;

    /** @var Model\UserManager */
    private $userManager;


    public function __construct(FormFactory $factory, Model\UserManager $userManager)
    {
        $this->factory = $factory;
        $this->userManager = $userManager;
    }


    /**
     * @return Form
     */
    public function create(callable $onSuccess, $token, callable $invalidToken)
    {
        $form = $this->factory->create();

        $form->addPassword('password', 'Create a password:')
            ->setOption('description', sprintf('at least %d characters', self::PASSWORD_MIN_LENGTH))
            ->setRequired('Please create a password.')
            ->addRule($form::MIN_LENGTH, null, self::PASSWORD_MIN_LENGTH);

        $form->addSubmit('send', 'Sign up');

        $form->onSuccess[] = function (Form $form, $values) use ($onSuccess, $token, $invalidToken) {
            try {
                $this->userManager->addUser($values->password, $token);
            } catch (Model\DuplicateNameException $e) {
                $form['username']->addError('Username is already taken.');
                return;
            } catch (InvalidTokenException $e) {
                $invalidToken();
            }
            $onSuccess();
        };

        return $form;
    }
}
