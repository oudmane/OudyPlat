<?php

namespace OudyPlat;

class TranslatedConstantEntity extends ConstantEntity {
    public function get($property, $language = null) {
        $key = $language.ucfirst($property);
        return (isset($this->$key) && $this->$key) ? $this->$key : $this->$property;
    }
    public function encoded($property, $language = null) {
        return htmlspecialchars($this->get($property, $language), ENT_QUOTES, 'UTF-8');
    }
}