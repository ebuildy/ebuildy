<?php

namespace eBuildy\Templating;

trait BlockHelperTrait
{
    protected $blocks = array();
    protected $currentBlockId = null;

    public function block($id)
    {
        return isset($this->blocks[$id]) ? $this->blocks[$id] : '';
    }

    public function setBlock($id, $content)
    {
        $this->blocks[$id] = $content;
    }
    
    public function setBlocks($blocks)
    {
        $this->blocks = $blocks;
    }

    protected function beginBlock($id)
    {
        $this->currentBlockId = $id;

        ob_start();
    }

    protected function endBlock()
    {
        $buffer = ob_get_clean();

        $this->setBlock($this->currentBlockId, $buffer);
    }

}