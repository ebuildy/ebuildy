<?php

namespace eBuildy\Templating;

class TwigOutputBufferTokenParser extends \Twig_TokenParser {
	
	public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
        $value = $parser->getExpressionParser()->parseExpression();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new Project_Set_Node($name, $value, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'obStart';
    }
	
}
