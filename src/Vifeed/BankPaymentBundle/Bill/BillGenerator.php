<?php
namespace Vifeed\BankPaymentBundle\Bill;

use Symfony\Bundle\TwigBundle\TwigEngine;

/**
 * Class BillGenerator
 *
 * @package Vifeed\SystemBundle\Bill
 */
class BillGenerator
{
    private $ownDetails;
    private $orderDetails = [];
    private $templates;
    private $twig;
    private $template;

    /**
     *
     */
    public function __construct(array $details, $templates, TwigEngine $twig)
    {
        $this->ownDetails = $details;
        $this->templates = $templates;
        $this->twig = $twig;
    }

    /**
     * @param $details
     *
     * @return $this
     */
    public function setOrderDetails($details)
    {
        $this->orderDetails = $details;

        return $this;
    }

    /**
     * @param $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        if (!isset($this->templates[$template])) {
            throw new \Exception('Шаблон ' . $template . ' не определен');
        }

        $this->template = $this->templates[$template];

        return $this;
    }

    /**
     * @return string
     */
    public function generate()
    {
        if ($this->template === null) {
            throw new \Exception('Не задан шаблон');
        }

        $mpdf = new \mPDF('utf-8', 'A4');
        $mpdf->charset_in = 'utf-8';
        $html = $this->twig->render($this->template, array_merge($this->orderDetails, $this->ownDetails));
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }
} 