<?php

namespace eBuildy\Asset;

use eBuildy\Asset\AssetResolver;

/**
 * @Service('asset', 'asset')
 */
class AssetHelper
{    
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
    
    public $groups;
    
    public $expandGroups = false;
    
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
        $this->groups = isset($configuration['groups']) ? $configuration['groups'] : null;
        $this->versionFormat = isset($configuration['version_format']) ? $configuration['version_format'] : '{name}.{extension}?v={version}';
        
        if (isset($configuration['compile']))
        {
            $this->enableCompilation = $configuration['compile']['enabled'];
            $this->forceCompilation = $configuration['compile']['force'];
        }
    }

    /**
     * @Expose("getCss")
     */
    public function css($source, $options = array())
    {
        if ($this->expandGroups && $this->isGroup($source))
        {
            $html = '';

            foreach($this->groups['css'][$source] as $asset)
            {
                $html .= $this->css($asset, $options) . PHP_EOL;
            }

            return trim($html);
        }
        
        $target = $this->compile('css', $source, $options);
        
        return '<link href="' . $target . '" rel="stylesheet" type="text/css" />';
    }
        
    /**
     * @Expose("addCss")
     */
    public function addCSS($source, $options = array())
    {
        $target = $this->compile('css', $source, $options);
        
        $listItems = $this->templatingService->variables->get('assets_css', array());
        
        $listItems []= $target;
        
        $this->templatingService->variables->set('assets_css', $listItems);
    }

    /**
     * @Expose("getJs")
     */
    public function js($source, $options = array())
    {
        $target = $this->compile('js', $source, $options);
                
        return '<script src="' . $target . '"></script>';
    }
    
    /**
     * @Expose("addJs")
     */
    public function addJs($source, $options = array())
    {
        $target = $this->compile('js', $source, $options);
        
        $listItems = $this->templatingService->variables->get('assets_js', array());
        
        $listItems []= $target;
        
        $this->templatingService->variables->set('assets_js', $listItems);
    }
    
    public function getAssetPath($source)
    {
        $sourcePath = AssetResolver::resolveSourcePath($source, $this->templatingService->getContext());
        
        if ($sourcePath === null || $sourcePath === false)
        {            
            throw new \Exception("The asset " . $source ." is not found !");
        }
        
        return $sourcePath;
    }
    
    private function getGroupAssetPath($type, $sources)
    {
        $sourcePath = array();
        
        foreach($sources as $source)
        {
            if ($this->isGroup($source))
            {
                $sourcePath = array_merge($sourcePath, $this->getGroupAssetPath($type, $this->groups[$type][$source]));
            }
            else
            {
                $sourcePath []= $this->getAssetPath($source);
            }
        }
        
        return $sourcePath;
    }
    
    public function compile($type, $source, $options = array(), $force = false)
    {        
        if ($this->isGroup($source))
        {
            $grouping = true;
            
            $targetFileName   = $source;
        }
        else
        {
            $grouping = false;
            
            $targetFileName   = md5($this->templatingService->getContext() . $source);
        }
        
        $targetUri = ($type === 'js' ? $this->jsPath : $this->cssPath) . AssetResolver::resolveNameWithVersion($targetFileName, $type, $this->version, $this->versionFormat);
        
        $doCompilation = false;
        
        if ($this->enableCompilation)
        {
            if ($grouping)
            {
                $sourcePath = $this->getGroupAssetPath($type, $this->groups[$type][$source]);
            }
            else
            {
                $sourcePath = $this->getAssetPath($source);
            }
            
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
                elseif ($grouping)
                {
                    foreach($sourcePath as $source)
                    {
                        $m = filemtime($targetFilePath);

                        if (filemtime($source) > $m)
                        {
                            $doCompilation = true;

                            break;
                        }
                    }
                }
                elseif (filemtime($sourcePath) > filemtime($targetFilePath))
                {
                    $doCompilation = true;
                }
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

                if ($grouping)
                {
                    $compiler->compileGroup($sourcePath, $targetFilePath);
                }
                else
                {
                    $compiler->compile($sourcePath, $targetFilePath);
                }
            }
        }

        return $targetUri;
    }
    
    private function isGroup($name)
    {
        return !strpos($name, '.');
    }
}
