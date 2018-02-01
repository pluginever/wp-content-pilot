<?php

namespace Pluginever\WPCP\Core;
class Find_Replace {
    /**
     * @var array
     */
    protected $find = [];

    /**
     * @var array
     */
    protected $replace = [];

    /**
     * set find and replacer
     * @since 1.0.0
     *
     * @param array $ar
     *
     */
    public function set_find_replacer( array $ar ) {
        foreach ( $ar as $key => $value ) {
            $this->find[] = $key;
            $this->replace[] = $value;
        }
    }

    /**
     * Do find and replace
     * @since 1.0.0
     *
     * @param $content
     *
     * @return mixed
     *
     */
    public function find_replace( $content ) {
        if ( ! empty( $this->find ) && ! empty( $this->replace ) ) {
            return str_replace( $this->find, $this->replace, $content );
        }

        return $content;
    }
}
