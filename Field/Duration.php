<?php

namespace pdima88\pdform\Field;

use pdima88\php\Assets;
use Nette\Utils\Html;
use pdima88\pdform\Form;

class Duration {
    static function render($name, $value, $attr) {
        Assets::add('bootstrap-durationpicker');

        $id = $attr['id'];

        $input = Form::attr(Html::el('input')->type('text')
            ->name($name)->value($value)->setClass('form-control'), $attr);

        $showDays = false;
        if (isset($attr['options']['showDays']) && $attr['options']['showDays']) {
            $showDays = true;
        }
        $showSeconds = false;
        if (isset($attr['options']['showSeconds']) && $attr['options']['showSeconds']) {
            $showSeconds = true;
        }

        Assets::addScript('$(function() {
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