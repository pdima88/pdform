<?php

class S4Y_Form_Field_Date {
    static function assets() {
        static $assetsIncluded = false;
        if ($assetsIncluded) return;
        $assetsIncluded = true;
        S4Y_Assets::add('bootstrap-daterangepicker');
        S4Y_Assets::addScript('
            $(function() {
                $(".s4yform_field_date-clear").on("click", function(e) {
                    var $div = $(this).parent();
                    $div.find("input[type=\"hidden\"]").val("");
                    $div.find("span").text("");
                    $div.find(".s4yform_field_date-clear").hide();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    e.preventDefault();
                });
            });
        ','S4Y_Form_Field_Date');
    }

    static function getMinMax($attr) {
        $r = '';
        if (isset($attr['min'])) {
            $r .= ' minDate: "'.$attr['min'].'",';
        }
        if (isset($attr['max'])) {
            $r .= ' maxDate: "'.$attr['max'].'",';
        }
        return $r;

    }
    static function getFormat($attr) {
        return isset($attr['format']) ? $attr['format'] : 'DD.MM.YYYY';

    }

    static function renderClearButton($div, $attr) {
        if ((!isset($attr['disabled']) || !$attr['disabled'])
            && (!isset($attr['required']) || !$attr['required'])) {
            $div->contain_(
                Html::a(
                    Html::i()->_class('glyphicon glyphicon-remove')
                )->_class('s4yform_field_date-clear')
                    ->href('#')->style('float: right; margin-left: 8px;margin-top:1px;position:relative;color:#444')
            );
        }
    }

    static function render($name, $value, $attr) {
        static::assets();
        $disabled = false;
        if (isset($attr['disabled']) && $attr['disabled']) {
            $disabled = true;
        }

        $span =  Html::span('')->_style('font-size: 12px;');
        $id = $attr['id'];
        $div = Html::div()->id($id.'_div')->disabled($disabled)
            ->_class('form-control s4yform_field_date')
            ->_style('overflow: hidden; cursor: pointer; text-overflow: ellipsis')
            ->contain_(Html::i()->_class('glyphicon glyphicon-calendar'));

        static::renderClearButton($div, $attr);

        $div->contain_(
            Html::b()->_class('caret')->style('float: right; margin-top: 7px'),
            $span
        );

        $input = S4Y_Form::attr(Html::input()->type('hidden')->name($name), $attr);

        $input->id($id);
        if ($value) $input->value(date('d.m.Y', strtotime($value)));

        S4Y_Assets::addScript('
            $(function() {
                moment.locale("ru");'.($disabled ? '' : '
                $("#'.$id.'_div").daterangepicker({
                    locale: {
                        format: "'. static::getFormat($attr) .'",
                        separator: " - ",
                        applyLabel: "Применить",
                        cancelLabel: "Сброс",
                        weekLabel: "W",
                        daysOfWeek: moment.weekdaysMin(),
                        monthNames: moment.months(),
                        firstDay: moment.localeData().firstDayOfWeek()
                    },'.($value ? '
                    startDate: "'.date('d.m.Y', strtotime($value)).'",':'').'
                    '. static::getMinMax($attr) .'
                    singleDatePicker: true,
                    showDropdowns: true,
                    alwaysShowCalendars: true,
                    showCustomRangeLabel: false
                }, function(date) {
                    var v = moment(date).format("L");
                    $("#'.$id.'_div .s4yform_field_date-clear").show();
                    $("#'.$id.'").val(v);
                    var $el = $("#'.$id.'_div span").text(moment(date).format("LL"));
                });').'
                
                if ($("#'.$id.'").val() != "") {
                    var v = $("#'.$id.'").val();
                    $("#'.$id.'_div .s4yform_field_date-clear").show();

                    var date = moment(v,"DD.MM.YYYY");

                    $("#'.$id.'_div span").text(date.format("LL"));
                } else {
                    $("#'.$id.'_div .s4yform_field_date-clear").hide();
                }
            });
        ',$id);

        return strval($div->contain_($input));
    }

}