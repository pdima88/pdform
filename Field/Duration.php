<?php

class S4Y_Form_Field_Duration {
    static function render($name, $value, $attr) {
        S4Y_Assets::add('bootstrap-durationpicker');

        $id = $attr['id'];

        $input = S4Y_Form::attr(Html::input()->type('text')
            ->name($name)->value($value)->_class('form-control'), $attr);

        $showDays = false;
        if (isset($attr['options']['showDays']) && $attr['options']['showDays']) {
            $showDays = true;
        }
        $showSeconds = false;
        if (isset($attr['options']['showSeconds']) && $attr['options']['showSeconds']) {
            $showSeconds = true;
        }

        S4Y_Assets::addScript('$(function() {
            $("#'.$id.'").durationPicker({
                lang: {
                    day: "день",
                    hour: "час",
                    minute: "мин.",
                    second: "сек.",
                    days: "дн.",
                    hours: "час.",
                    minutes: "мин.",
                    seconds: "сек."
                },
                showDays: '.($showDays ? 'true': 'false').',
                showSeconds: '.($showSeconds ? 'true': 'false').',
                onChanged: function (newVal) {
                    $("#'.$id.'").val(newVal);
                }
            });                
        });', 'initDurationPicker'.$id);

        return $input;
    }
}