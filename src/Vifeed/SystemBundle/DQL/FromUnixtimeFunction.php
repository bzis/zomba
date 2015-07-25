<?php

namespace Vifeed\SystemBundle\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

/**
 * Class FromUnixtimeFunction
 *
 * @package Vifeed\SystemBundle\DQL
 */
class FromUnixtimeFunction extends FunctionNode
{
    private $arg;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'FROM_UNIXTIME(' . $sqlWalker->walkSimpleArithmeticExpression($this->arg) . ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->arg = $parser->SimpleArithmeticExpression();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}