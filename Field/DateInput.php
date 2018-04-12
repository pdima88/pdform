<?php

namespace pdima88\pdform\Field;

use pdima88\php\Assets;
use pdima88\twbsHelper\Glyphicon;
use pdima88\pdform\Form;

class DateInput {
    static function assets() {
        static $assetsIncluded = false;
        if ($assetsIncluded) return;
        $assetsIncluded = true;
        Assets::add('bootstrap-daterangepicker');
        /*Assets::addScript('
            $(function() {
                $(".s4yform_field_dateinput-clear").on("click", function(e) {
                    var $div = $(this).parent();
                    $div.find("input[type=\"hidden\"]").val("");
                    $div.find("span").text("");
                    $div.find(".s4yform_field_date-clear").hide();
                    e.stopImmediatePropagation();
                    e.stopPropagation();
                    e.preventDefault();
                });
            });
        ','S4Y_Form_Field_Date');*/

        Assets::add('jquery.inputmask');
        Assets::addScript('
                    $(function() {
                        $("[data-inputmask=date]").inputmask({
                           mask: "99.99.9999",                   
                           clearMaskOnLostFocus: false,
                           keepStatic: true
                        });
                    });
                ', 'dateInput_mask');
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
            $div->addHtml(
                Html::el('a')->addHtml(
                    Glyphicon::remove
                )->setClass('s4yform_field_date-clear')
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
        
        $id = $attr['id'];
        $div = Html::el('div')->id($id.'_div')->disabled($disabled);

        $input = Form::attr(Html::el('input')->type('text')->name($name), $attr);

        $input->id($id)->setClass('form-control clear');
        $input->data('inputmask', 'date');
        if ($value) $input->value(date('d.m.Y', strtotime($value)));

        Assets::addScript('
            $(function() {
                moment.locale("ru");'.($disabled ? '' : '
                $("#'.$id.'").daterangepicker({
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
                    autoUpdateInput: false,
                    showDropdowns: true,
                    alwaysShowCalendars: true,
                    showCustomRangeLabel: false
                },
                 function(date) {                    
                    $("#'.$id.'").val(date.format("'.self::getFormat($attr).'"));
                }
                
                
                ); })'),$id);

        return strval($div->addHtml($input));
    }

}