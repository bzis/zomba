<?php


namespace Vifeed\SystemBundle\Exception;


use Symfony\Component\Form\Form;

class WrongDateIntervalException extends \Exception
{
    private $form;

    /**
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
} 