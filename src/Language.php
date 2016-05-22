<?php

namespace OudyPlat;

class Language {
    public static $language = '';
    public static $components = array();
    public static $strings = array();
    public function __construct($language = null, $components = array()) {
        if($language)
            self::$language = $language;
        if($components)
            $this->loadComponents($components);
    }
    public function loadComponents($components) {
        if(gettype($components) == 'array')
            foreach($components as $component)
                $this->loadComponents($component);
        else {
            if(!isset(self::$components[self::$language]))
                self::$components[self::$language] = array();
            if(!in_array($components, self::$components[self::$language])) {
                $component = strtolower($components);
                if(file_exists($language = COMPONENTS_PATH.$component.DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR.self::$language.'.ini'))
                    $this->addStrings($language);
                else if(defined('PARENT_COMPONENTS_PATH') && file_exists($language = PARENT_COMPONENTS_PATH.$component.DIRECTORY_SEPARATOR.'languages'.DIRECTORY_SEPARATOR.self::$language.'.ini'))
                    $this->addStrings($language);
                array_push(self::$components, $component);
            }
        }
    }
    public function addStrings($string) {
        if(!isset(self::$strings[self::$language]))
            self::$strings[self::$language] = array();
        self::$strings[self::$language] = array_merge(self::$strings[self::$language], parse_ini_file($string));
    }
    public function returnString($string, $values = array(), $added = false) {
        if(!isset(self::$strings[self::$language][strtoupper($string)])) {
            if($added)
                return $string;
            $string = explode('_', strtoupper($string));
            if(count($string) == 1)
                return $string[0];
            $this->loadComponents($string[0]);
            return $this->returnString(implode('_', $string), $values, true);
        }
        $string = strtoupper($string);
        $string = self::$strings[self::$language][$string];
        if($values)
            foreach($values as $key=>$value)
                $string = str_replace('{'.$key.'}', $value, $string);
        return $string;
    }
}