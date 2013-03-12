<?php

namespace eBuildy\Templating;

class TwigBaseTemplate  implements \Twig_TemplateInterface
{
    public $env;
    public $container;
    
    use BlockHelperTrait;
    
    public function __construct(\Twig_Environment $env)
    {
        $this->env = $env;
    }

    public function display(array $context, array $blocks = array())
    {
        $this->displayWithErrorHandling($this->env->mergeGlobals($context), $blocks);
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    public function render(array $context)
    {
        $level = ob_get_level();
        ob_start();
        try {
            $this->display($context);
        } catch (Exception $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }

        return ob_get_clean();
    }
    
    public function displayBlock($name, array $context, array $blocks = array())
    {
        echo $this->block($name);
    }
    
     protected function displayWithErrorHandling(array $context, array $blocks = array())
    {
        try {
            $this->doDisplay($context, $blocks);
        } catch (\Twig_Error $e) {
            if (!$e->getTemplateFile()) {
                $e->setTemplateFile($this->getTemplateName());
            }

            // this is mostly useful for Twig_Error_Loader exceptions
            // see Twig_Error_Loader
            if (false === $e->getTemplateLine()) {
                $e->setTemplateLine(-1);
                $e->guess();
            }

            throw $e;
        } catch (\Exception $e) {
            throw new \Twig_Error_Runtime(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, null, $e);
        }
    }
    
    protected function getAttribute($object, $item, array $arguments = array(), $type = \Twig_TemplateInterface::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
    {
        return $object[$item];
    }
}