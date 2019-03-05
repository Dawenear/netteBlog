<?php

namespace App\Presenters;

use Nette;
use App\Forms;
use App\Model\UserManager;
use Nette\Database\Context;


class SignPresenter extends BasePresenter
{
    /** @var Forms\SignInFormFactory @inject */
    public $signInFactory;

    /** @var Forms\SignUpFormFactory @inject */
    public $signUpFactory;

    /** @var  UserManager */
    protected $userManager;

    protected $token;

    /**
     * SignPresenter constructor.
     *
     * @param Context $db
     * @param UserManager $userManager
     */
    public function __construct(Context $db, UserManager $userManager)
    {
        parent::__construct($db);
        $this->userManager = $userManager;
    }

    public function actionOut()
    {
        $this->getUser()->logout();
    }

    /**
     * @param string $id
     *
     * @throws Nette\Application\AbortException
     */
    public function actionUp($token)
    {
        if (!$token) {
            $this->flashMessage('Pro registraci je třeba mít platný token');
            $this->redirect('Sign:close');
        }

        if (!$this->userManager->checkToken($token)) {
            $this->flashMessage('Pro registraci je třeba mít platný token');
            $this->redirect('Sign:close');
        }

        $this->token = $token;
    }

    /**
     * Sign-in form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignInForm()
    {
        return $this->signInFactory->create(function () {
            $this->redirect('Homepage:');
        });
    }


    /**
     * Sign-up form factory.
     * @return Nette\Application\UI\Form
     */
    protected function createComponentSignUpForm()
    {
        return $this->signUpFactory->create(function () {
            $this->flashMessage('Účet byl úspěšně vytvořen');
            $this->redirect('Sign:in');
        }, $this->token, function () {
            $this->flashMessage('Neplatný token', 'warning');
            $this->redirect('Homepage:');
        });
    }
}
