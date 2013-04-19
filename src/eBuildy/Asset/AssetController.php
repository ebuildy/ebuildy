<?php

namespace eBuildy\Asset;

use eBuildy\Component\Controller;
use eBuildy\Component\Response;

class AssetController extends Controller
{
    public function compileCSS($request, $response)
    {
        $buffer = $this->resolveData($request->get('data'));
        
        $compiler = new Compiler\CSSCompiler();
        
        return new Response($compiler->compile($buffer['source'], WEB_PATH . $buffer['target']), array(
            'content-type' => 'text/css'
        ));
    }
    
    public function compileJS($request, $response)
    {
        $buffer = $this->resolveData($request->get('data'));
        
        $compiler = new Compiler\JSCompiler();
            
        return new Response($compiler->compile($buffer['source'], WEB_PATH . $buffer['target']), array(
            'content-type' => 'application/javascript'
        ));
    }
    
    protected function resolveData($data)
    {
        $dataDecoded = base64_decode($data);
        $buffer = json_decode($dataDecoded, true);
        
        return array('target' => $buffer[0], 'source' => ROOT . $buffer[1]);
    }
}