<?php

class S4Y_Form_Field_File {

    static function render($name, $value, $attr = null) {
        $html = '';
        $id = $attr['id'];
        $src = isset($attr['src']) ? $attr['src'] : '';
        if ($src != '') {
            $html .= '<a href="'.$src.'" target="_blank">'.$src.'</a>';
            if (!isset($attr['required']) || !$attr['required']) {
                $html .= ' <label><input type="checkbox" name="' . $name . '_clear" id='
                    . $id . '_clear" value="yes">Удалить</label>';
            }
        }
        $exclAttr = [];
        if ($src !== '' && isset($attr['required']) && $attr['required']) $exclAttr = ['required'];
        $html .= '<div class="input-group"><div class="input-group-addon">Загрузить '.
            (($src != '') ? 'другой ': '').'файл: </div>'
            . S4Y_Form::attr(Html::input()->type('file')->_class('form-control')->name($name), $attr, $exclAttr) . '</div>';
        return $html;
    }
}