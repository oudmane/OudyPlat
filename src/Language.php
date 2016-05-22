<?php

namespace OudyPlat;

class Language {
    public $language = '';
    public static $components = array();
    public static $strings = array();
    public static $defaultLanguage = '';
    public function __construct($language = null, $components = array()) {
        if($language)
            $this->language = $language;
        if($components)
            $this->loadComponents($components);
    }
    public function loadComponents($components) {
        if(gettype($components) == 'array')
            foreach($components as $component)
                $this->loadComponents($component);
        else {
            if(!isset(self::$components[$this->language]))
                self::$components[$this->language] = array();
            if(!in_array($components, self::$components[$this->language])) {
                $component = strtolower($components);
                if(file_exists($language = COMPONENTS_PATH.$component.DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR.$this->language.'.ini'))
                    $this->addStrings($language);
                else if(defined('PARENT_COMPONENTS_PATH') && file_exists($language = PARENT_COMPONENTS_PATH.$component.DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR.$this->language.'.ini'))
                    $this->addStrings($language);
                array_push(self::$components, $component);
            }
        }
    }
    public function addStrings($string) {
        if(!isset(self::$strings[$this->language]))
            self::$strings[$this->language] = array();
        self::$strings[$this->language] = array_merge(self::$strings[$this->language], parse_ini_file($string));
    }
    public function returnString($string, $values = array(), $added = false) {
        if(!isset(self::$strings[$this->language][strtoupper($string)])) {
            if($added)
                return $string;
            $string = explode('_', strtoupper($string));
            if(count($string) == 1)
                return $string[0];
            $this->loadComponents($string[0]);
            return $this->returnString(implode('_', $string), $values, true);
        }
        $string = strtoupper($string);
        $string = self::$strings[$this->language][$string];
        if($values)
            foreach($values as $key=>$value)
                $string = str_replace('{'.$key.'}', $value, $string);
        return $string;
    }
}