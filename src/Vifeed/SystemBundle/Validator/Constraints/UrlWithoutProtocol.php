<?php
namespace Vifeed\SystemBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Url;

/**
 * @Annotation
 */
class UrlWithoutProtocol extends Url
{
    public $message = 'This value is not a valid URL.';

}
