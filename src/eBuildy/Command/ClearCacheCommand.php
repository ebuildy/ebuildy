<?php

namespace eBuildy\Command;

/**
 * @Command("clear:cache")
 */
class ClearCacheCommand extends \eBuildy\Component\Command
{
    public function execute($input, $output)
    {
        rmdir(TMP_PATH);
        
        //mkdir(TMP_PATH . 'dev');
    }

    public function run()
    {
        
    }
}