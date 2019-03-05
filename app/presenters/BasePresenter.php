<?php

namespace App\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

   /** @var Nette\Database\Context */
   protected $db;

    public function __construct(Nette\Database\Context $db) {
        $this->db = $db;
    }
}
