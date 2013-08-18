<?php

namespace eBuildy\Templating;

trait BlockHelperTrait
{
    protected $parentBlocks = array();
    protected $currentBlockId = null;
    
    public function getBlocks()
    {
	return $this->parentBlocks;
    }

    public function block($id)
    {
        return isset($this->parentBlocks[$id]) ? $this->parentBlocks[$id] : '';
    }

    public function setBlock($id, $content)
    {
        $this->parentBlocks[$id] = $content;
    }
    
    public function setBlocks($blocks)
    {
        $this->parentBlocks = $blocks;
    }
    
    public function hasBlock($id)
    {
	return isset($this->parentBlocks[$id]);
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