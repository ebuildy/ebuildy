<?php

namespace eBuildy\Asset;

use eBuildy\Asset\AssetResolver;
use eBuildy\Component\ApplicationAware;

/**
 * @Service('asset', 'asset')
 */
class AssetHelper
{
    use ApplicationAware;
    
    /**
     * @Inject("templating")
     */
    public $templatingService;

    /**
     * Configuration("path_css")
     */
    public $cssPath = '/dev/css/';
    
    /**
     * Configuration('path_js")
     */
    public $jsPath  = '/dev/css/';
    
    /**
     * Configuration("compress")
     */
    public $compress = false;
    
    /**
     * Configuration("version")
     */
    public $version = 4;
    
   /**
     * Configuration("version_format")
     */
    public $versionFormat = 4;
    
    public $enableCompilation = false;
    public $forceCompilation = false;

    public function initialize($configuration)
    {
        $this->cssPath = $configuration['uri'] . 'css/';
        $this->jsPath  = $configuration['uri'] . 'js/';
        $this->version = isset($configuration['version']) ? $configuration['version'] : null;
        $this->versionFormat = isset($configuration['version_format']) ? $configuration['version_format'] : '{name}.{extension}?v={version}';
        
        if (isset($configuration['compile']))
        {
            $this->enableCompilation = $configuration['compile']['enabled'];
            $this->forceCompilation = $configuration['compile']['force'];
        }
        
        if (DEBUG)
        {
            $this->version = time();
        }
    }

    /**
     * @Expose("getCss")
     */
    public function css($source, $options = array())
    {
        $target = $this->compile('css', $this->getAssetPath($source), $options);
        
        return '<link href="' . $target . '" rel="stylesheet" type="text/css" />';
    }
    
    /**
     * @Expose("addCss")
     */
    public function addCSS($source, $options = array())
    {
        $target = $this->compile('css', $this->getAssetPath($source), $options);
        
        $listItems = $this->templatingService->variables->get('assets_css', array());
        
        $listItems []= $target;
        
        $this->templatingService->variables->set('assets_css', $listItems);
    }

    /**
     * @Expose("getJs")
     */
    public function js($source, $options = array())
    {
        $target = $this->compile('js', $this->getAssetPath($source), $options);
                
        return '<script src="' . $target . '"></script>';
    }
    
    /**
     * @Expose("addJs")
     */
    public function addJs($source, $options = array())
    {
        $target = $this->compile('js', $this->getAssetPath($source), $options);
        
        $listItems = $this->templatingService->variables->get('assets_js', array());
        
        $listItems []= $target;
        
        $this->templatingService->variables->set('assets_js', $listItems);
    }
    
    public function getAssetPath($source)
    {
        $sourcePath = AssetResolver::resolveSourcePath($source, $this->templatingService->getContext());
        
        if ($sourcePath === null)
        {
            debug("Context", $this->templatingService->getContext());
            
            throw new \Exception("The asset " . $source ." is not found !");
        }
        
        return $sourcePath;
    }
    
    public function compile($type, $source, $options = array(), $force = false)
    {
        $targetFileName   = AssetResolver::resolveRessourceName($source, $options);
        $targetUri             = ($type === 'js' ? $this->jsPath : $this->cssPath) . AssetResolver::resolveNameWithVersion($targetFileName, $type, $this->version, $this->versionFormat);
        $targetFilePath     = WEB_PATH . AssetResolver::resolveNameForCompilation($targetUri);
                
        if ($force || $this->forceCompilation)
        {
            $doCompilation = true;
        }
        elseif ($this->enableCompilation)
        {
            if (!file_exists($targetFilePath))
            {
                $doCompilation = true;
            }
            elseif (filemtime($source) > filemtime($targetFilePath))
            {
                $doCompilation = true;
            }
            else
            {
                $doCompilation = false;
            }
        }
        else
        {
            $doCompilation = false;
        }
                
        if ($doCompilation)
        {
            if ($type === 'js')
            {
                $compiler = new Compiler\JSCompiler($options);
            }
            elseif ($type === 'css')
            {
                $compiler = new Compiler\CSSCompiler($options);
            }

            $compiler->compile($source, $targetFilePath);
        }
        
        return $targetUri;
    }
}
