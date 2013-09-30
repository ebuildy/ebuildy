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
    
    /**
     * @Inject("translator")
     * @var \eBuildy\Helper\Translator
     */
    public $translatorService;
    
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
            $html = '<div class="alert alert-danger"><ul>';

            foreach($errors as $fieldName => $error)
            {
                $field = $form->getChild($fieldName);
                
                $html .= '<li>' . $this->translatorService->get($error, array('field' => $field->name, 'value' => $field->getData())) . '</li>';
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
    public function renderActions($value, $type = 'normal')
    {
        return '<div class="form-group" style="margin-top:10px"><div class="col-sm-offset-4 col-sm-8 "><input type="submit" class="btn btn-success btn-block" value="'.$value.'" /></div></div>';
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
     * @Expose("form_row_inline")
     */
    public function renderFieldInline($name, $template, $label = null, $value = null, $rowTemplate = '__row.phtml', $attributes = array())
    {        
        $templateComponentPath = $this->templatesPath . $template . '.phtml';
        
        $attributes = array_merge(
                $attributes,
                array(
                    'id' => 'input' . $name,
                    'name' => $name,
                    'value' => $value
                )
        );
        
        if ($rowTemplate === null)
        {
            return $this->templatingService->renderDecoratedTemplate(array($templateComponentPath), array('id' => $name,  'attributes' => $attributes,  'label' => $label, 'value' => $value, 'errors' => null));
        }
        else
        {        
            $templateRowPath = $this->templatesPath . $rowTemplate;
        
            return $this->templatingService->renderDecoratedTemplate(array($templateComponentPath, $templateRowPath), array('id' => $name,  'attributes' => $attributes, 'label' => $label, 'value' => $value, 'errors' => null));
        }
    }
    
    /**
     * @Expose("form_row")
     */
    public function renderField($form, $fieldName = null)
    {
        $control = $control = $fieldName === null ? $form : $form->getChild($fieldName);
        
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
    public function renderControl($form, $fieldName = null)
    {
        $control = $fieldName === null ? $form : $form->getChild($fieldName);
        
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