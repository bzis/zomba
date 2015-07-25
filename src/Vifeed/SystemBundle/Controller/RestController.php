<?php

namespace Vifeed\SystemBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vifeed\SystemBundle\Exception\WrongDateIntervalException;

class RestController extends FOSRestController
{

    /**
     * @return array
     * @throws WrongDateIntervalException
     */
    protected function getRequestedDates()
    {
        $options = [
              'csrf_protection' => false,
        ];
        $dateForm = $this->get('form.factory')->createNamedBuilder(null, 'form', null, $options)
                         ->add('date_from', 'date', ['widget' => 'single_text', 'constraints' => new NotBlank()])
                         ->add('date_to', 'date', ['widget' => 'single_text', 'constraints' => new NotBlank()])
                         ->getForm();

        $dateForm->submit($this->getRequest());
        if ($dateForm->isValid()) {
            $data = $dateForm->getData();
            if ($data['date_to'] < $data['date_from']) {
                $dateForm->addError(new FormError('неверный диапазон'));
                throw new WrongDateIntervalException($dateForm);
            }
        } else {
            throw new WrongDateIntervalException($dateForm);
        }

        $data['date_to']->setTime(23, 59, 59);

        return $data;
    }
}
