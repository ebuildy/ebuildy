<?php

namespace eBuildy\DataBinder;

/**
 * @Service('form', 'form')
 */
class FormHelper
{        
    /**
     * @Inject("templating")
     * @var \eBuildy\Templating\Templating
     */
    public $templatingService;
    
    protected $templatesPath;
    
    public function initialize($config)
    {
        $this->templatesPath = ROOT . (isset($config['template_path']) ? $config['template_path'] : '');
    }
    
    /**
     * @Expose("form_errors")
     */
    public function renderFormErrors($form)
    {
        $errors = $form->getErrors();
        
        if (count($errors) > 0)
        {
            $html = '<div class="alert alert-error"><ul>';
            
            foreach($errors as $error)
            {
                $html .= '<li>' . $error . '</li>';
            }
            
            $html .= '</ul></div>';
        }
        else
        {
            $html = '';
        }
        
        return $html;
    }
    
    /**
     * @Expose("form_actions")
     */
    public function renderActions($value)
    {
        return '<div class="form-actions"><input type="submit" class="btn" value="'.$value.'" /></div>';
    }
            
    /**
     * @Expose("form")
     */
    public function render($form)
    {
        $html = '';
        
        foreach($form->getChildren() as $childName => $child)
        {
            $html .= $this->renderField($form, $childName);
        }
        
        return $html;
    }
    
    /**
     * @Expose("form_row")
     */
    public function renderField($form, $fieldName)
    {
        $control = $form->getChild($fieldName);
        
        $templateComponentPath = $this->templatesPath . $control->getTemplate() . '.phtml';
        
        $attributes = array_merge(
                $control->getOptions('attributes', array()),
                array(
                    'id' => 'input' . $control->name,
                    'name' => $control->name,
                    'value' => $control->getData()
                )
        );
        
        if ($control->getRowTemplate() === null)
        {
            return $this->templatingService->renderDecoratedTemplate(array($templateComponentPath), array('id' => $control->name,  'attributes' => $attributes,  'label' => $control->getLabel(), 'value' => $control->getData(), 'errors' => $control->getErrors()));
        }
        else
        {        
            $templateRowPath = $this->templatesPath . $control->getRowTemplate();
        
            return $this->templatingService->renderDecoratedTemplate(array($templateComponentPath, $templateRowPath), array('id' => $control->name,  'attributes' => $attributes, 'label' => $control->getLabel(), 'value' => $control->getData(), 'errors' => $control->getErrors()));
        }
    }
    
    /**
     * @Expose("form_control")
     */
    public function renderControl($form, $fieldName)
    {
        $control = $form->getChild($fieldName);
        
        $templateComponentPath = $this->templatesPath . $control->getTemplate() . '.phtml';
        
        $attributes = array_merge(
                $control->getOptions('attributes', array()),
                array(
                    'id' => 'input' . $control->name,
                    'name' => $control->name,
                    'value' => $control->getData()
                )
        );
        
        return $this->templatingService->renderDecoratedTemplate(array($templateComponentPath), array('id' => $control->name, 'attributes' => $attributes, 'label' => $control->getLabel(), 'value' => $control->getData(), 'errors' => $control->getErrors()));
    }
    
    /**
     * @Expose("html_attributes")
     */
    public function renderHtmlAttributes($attributes)
    {
        $buffer = '';
        
        foreach ($attributes as $k => $v)
        {
            $buffer .= $k . '="' . $v . '" ';
        }
        
        return trim($buffer);
    }
}