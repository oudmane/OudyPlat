<?php

namespace OudyPlat;

class TranslatedEntity extends Entity {
    private $_language = '';
    public function setLanguage($language) {
        if(empty($this->_language))
            $this->_language = Language::$defaultLanguage;
        $this->_language = $language;
    }
    public function getLanguage() {
        return $this->_language;
    }
    public function get($property, $language = null) {
        if(empty($language))
            $language = $this->getLanguage();
        $key = $language.ucfirst($property);
        return (isset($this->$key) && $this->$key) ? $this->$key : $this->$property;
    }
    public function encoded($property, $language = null) {
        return htmlspecialchars($this->get($property, $language), ENT_QUOTES, 'UTF-8');
    }
}