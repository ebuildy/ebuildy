<?php

namespace eBuildy\Asset;

/**
 * @Command("asset:dump")
 */
class AssetCommand extends \eBuildy\Component\Command
{
   public function run()
   {
        $dumper = new AssetDumper(SOURCE_PATH);
        
        $dumper->assetHelper = $this->container->getAssetService();
        
        $assets = $dumper->dump();//var_dump($assets);die();
                
        foreach($assets as $asset => $type)
        {
            $target = $this->container->getAssetService()->compile($type, $asset, array(), true);
            
            echo ' + ' . $type .': ' .$target . PHP_EOL;
        }
   }
}