<?php

namespace eBuildy\Templating\Volt;

if (interface_exists('\Phalcon\Mvc\ViewInterface'))
{
	class VoltView implements \Phalcon\Mvc\ViewInterface
	{
		public function cache($options = null) {
			
		}
	
		public function cleanTemplateAfter() {
			
		}
	
		public function cleanTemplateBefore() {
			
		}
	
		public function disable() {
			
		}
	
		public function enable() {
			
		}
	
		public function finish() {
			
		}
	
		public function getActionName() {
			
		}
	
		public function getActiveRenderPath() {
			
		}
	
		public function getCache() {
			
		}
	
		public function getContent() {
			
		}
	
		public function getControllerName() {
			
		}
	
		public function getCurrentRenderLevel() {
			
		}
	
		public function getLayout() {
			
		}
	
		public function getLayoutsDir() {
			
		}
	
		public function getMainView() {
			
		}
	
		public function getParams() {
			
		}
	
		public function getParamsToView() {
			
		}
	
		public function getPartialsDir() {
			
		}
	
		public function getRenderLevel() {
			
		}
	
		public function getViewsDir() {
			return TMP_PATH . '/volt/';
		}
	
		public function isDisabled() {
			
		}
	
		public function partial($partialPath) {
			
		}
	
		public function pick($renderView) {
			
		}
	
		public function registerEngines($engines) {
			
		}
	
		public function render($controllerName, $actionName, $params = null) {
			
		}
	
		public function reset() {
			
		}
	
		public function setBasePath($basePath) {
			
		}
	
		public function setContent($content) {
			
		}
	
		public function setLayout($layout) {
			
		}
	
		public function setLayoutsDir($layoutsDir) {
			
		}
	
		public function setMainView($viewPath) {
			
		}
	
		public function setParamToView($key, $value) {
			
		}
	
		public function setPartialsDir($partialsDir) {
			
		}
	
		public function setRenderLevel($level) {
			
		}
	
		public function setTemplateAfter($templateAfter) {
			
		}
	
		public function setTemplateBefore($templateBefore) {
			
		}
	
		public function setVar($key, $value) {
			
		}
	
		public function setViewsDir($viewsDir) {
			
		}
	
		public function start() {
			
		}
	}
}
