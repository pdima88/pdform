<?php
namespace pdima88\pdform\Render;

class DefaultRenderer {

    /** @var S4Y_Form */
    protected $_form;

    static $_fieldRenderers = ['text', 'select', 'checkbox', 'radio', 'input', 'textarea'];

    function __construct($form) {
        $this->_form = $form;
    }

    #region Helpers

    

    static function label($attr) {
        if (isset($attr['label']) && $attr['label'] !== false) {
            $content = $attr['label'];
            if (isset($attr['required']) && $attr['required']) {
                $content .= ' ' . Html::span('*')->_class('required')->title("Это поле обязательно для заполнения");
            }
            $label = Html::label($content);
            if (isset($attr['id']) && $attr['id']) $label->for($attr['id']);
            return $label;
        }
        return null;
    }

    static function datalist($id, $options)
    {
        $html = Html::datalist()->id($id);
        foreach ($options as $option) {
            if (is_array($option)) {
                $text = '';
                foreach ($options as $k => $text) break;
            } else $text = $option;
            $html[] = Html::option(htmlspecialchars($text));
        }
        return $html;
    }

    static function desc($attr)
    {
        if (isset($attr['desc'])) {
            return Html::p($attr['desc'])->_class('help-block');
        }
        return null;
    }

    #endregion

    #region Field renderers

    function text($name, $value, $attr)
    {
        $html = '';
        if (isset($attr['options'])) {
            if (!isset($attr['id'])) $attr['id'] = 'txt'.ucfirst($name);
            $html .= static::datalist($attr['id'].'_datalist', $attr['options']);
            $attr['list'] = $attr['id'].'_datalist';
        }
        $html .= $this->input($name, $value, $attr);
        return $html;

    }

    function input($name, $value, $attr)
    {
        return S4Y_Form::attr(Html::input()->type(isset($attr['type']) ? $attr['type']: 'text')
            ->value($value)->name($name)->_class('form-control'), $attr);
    }

    function textarea($name, $value, $attr)
    {
        return S4Y_Form::attr(Html::textarea($value)->name($name)->_class('form-control'), $attr);
    }

    function checkbox($name, $value, $attr)
    {
        $div = Html::div(
            Html::label(
                S4Y_Form::attr(
                    Html::input()->type('checkbox')->name($name)
                        ->value(1)->checked(boolval($value)), $attr
                ),
                isset($attr['label']) ? ' '.$attr['label'] : ''
            )
            //static::desc($attr)
        )->_class('checkbox');
        if (isset($attr['disabled']) && $attr['disabled']) {
            $div->_class('disabled');
        }
        return $div;
    }

    function radioitem($name, $label, $value, $checked, $attr = null)
    {
        $html = Html::label(
            S4Y_Form::attr(
                Html::input()->type('radio')->name($name)
                    ->value($value)->checked($checked)
            , $attr),
            $label
        );
        if (isset($attr['inline']) && $attr['inline']) {
            $html->_class('radio-inline');
        } else {
            $html = Html::div($html)->_class('radio');
            if (isset($attr['disabled']) && $attr['disabled']) $html->_class('disabled');
        }
        return $html;
    }

    function radio($name, $value, $attr) {
        $options = [];
        if (isset($attr['none'])) {
            $options = ['' => $attr['none']];
        }
        if (isset($attr['options'])) {
            $options += $attr['options'];
        }
        $html = '';

        $i = 1;
        $id = isset($attr['id']) ? $attr['id'] : null;
        if (!isset($attr['inline'])) $attr['inline'] = true;

        foreach ($options as $optValue => $option)
        {
            if (isset($id)) $attr['id'] = $id.'_'.$i++;
            $html .= self::radioitem($name, $option, $optValue, $value == $optValue, $attr);
        }
        return ' &nbsp; '.$html;
    }

    function select($name, $value, $attr)
    {
        $html = S4Y_Form::attr(Html::select()->name($name)->_class('form-control'), $attr);
        $options = [];
        if (isset($attr['none'])) {
            $options = ['' => $attr['none']];
        }
        if (isset($attr['options'])) {
            $options += $attr['options'];
        }
        $selected = false;
        foreach ($options as $v => $opt) {
            // TODO: optgroups
            $option = Html::option()->value($v);
            if (is_array($opt)) {
                $text = isset($opt['name']) ? $opt['name'] : $opt['title'];
                if (!empty($attr['filter'])) {
                    foreach ($attr['filter'] as $f => $fname) {
                        $option[' data-'.$f] =$opt[$f];
                    }
                }
            } else {
                $text = $opt;
            }
            $option->contain_($text);

            if ($v == $value && !$selected) {
                $selected = true;
                $option->selected(true);
            }
            $html[] = $option;
        }

        if (!empty($attr['filter'])) {
            foreach ($attr['filter'] as $filterAttr => $filterName) {
                if (isset($this->_form->fields[$filterName])) {
                    if (isset($this->_form->fields[$filterName]['id'])) {
                        $parentId = $this->_form->fields[$filterName]['id'];
                    } else {
                        $parentId = $this->_form->getDefaultId($filterName,
                            isset($this->_form->fields[$filterName]['type']) ?
                                $this->_form->fields[$filterName]['type'] : 'text'
                        );
                        $this->_form->fields[$filterName]['id'] = $parentId;
                    }
                } else {
                    $parentId = $filterName;
                }

                if (isset($attr['id'])) {
                    $id = $attr['id'];
                }
                if (empty($id)) $id = $this->_form->getDefaultId($name, 'select');

                S4Y_Assets::addScript('
                    function S4Y_Form_Select_Filter_' . $name . '_by' . $filterAttr . '() {
                        var id = $("#' . $parentId . '").val();
                        $("#' . $id . ' option[data-' . Html::normalize_($filterAttr) . '!=\""+id+"\"]").hide();' .
                        (isset($attr['none']) && $attr['none'] ? '$("#' . $id . ' option:first-child").show();' : '') .
                        '$("#' . $id . ' option[data-' . Html::normalize_($filterAttr) . '=\""+id+"\"]").show();
                    }
                    
                    $(function() {
                        $("#' . $parentId . '").on("change",
                            function() {
                                S4Y_Form_Select_Filter_' . $name . '_by' . $filterAttr . '();
                                $("#' . $id . '").val("");
                            });
                                
                            S4Y_Form_Select_Filter_' . $name . '_by' . $filterAttr . '();
                    });
                ', 'S4Y_Form_Select_Filter_' . $name . '_by' . $filterAttr);
            }
        }

        return $html;
    }

    #endregion

    #region Decorators

    function decorateField($content, $attr)
    {
        //$label = isset($attr['label']) ? $attr['label'] : '';
        $type = (isset($attr['type']) ? $attr['type'] : 'text');
        $renderLabel = true;
        if (isset($attr['render']['label'])) $renderLabel = $attr['render']['label'];
        if ($type == 'hidden' || $type == 'checkbox') $renderLabel = false;

        $label = null;
        if ($renderLabel !== false) {
            $label = static::label($attr);
        }

        if ($type != 'hidden' && $type != 'checkbox') {
            return Html::div($label, $content, static::desc($attr))->_class('form-group');
        }
        return $content;
    }

    function renderMultilangField($name, $attr)
    {
        $srcName = $name;
        $names = ['' => $name];
        foreach ($this->_form->options['lang'] as $lang => $langName) {
            $names[$langName] = $name . '_' . $lang;
        }

        $html = [];
        $div = Html::div()->_class('well');
        foreach ($names as $langName => $name) {
            if ($name === $srcName) {
                $html[] = $this->renderField($name, $attr);

                $id = (isset($attr['id']) ? $attr['id'] : $name);
                $a = Html::a(
                    Html::span()->_class("glyphicon glyphicon-globe")
                )->_class('btn btn-default btn-xs')
                    ->role('button')
                    ->title('Показать переводы')
                    ->style('float:right;margin-top:-30px;margin-right:5px')
                    ->href('#multilang_' . $id);
                $a['data-toggle'] = 'collapse';
                $a['aria-expanded'] = 'false';
                $a['aria-controls'] = 'multilang_' . $id;

                $html[] = $a;
                $html[] = Html::div($div)->_class('collapse')->id('multilang_' . $id);
            } else {
                $attr['label'] = $langName;
                $attr['required'] = false;
                $attr['desc'] = false;
                $div[] = $this->renderField($name, $attr);
            }
        }
        return $html;
    }

    
    function renderField($name, $attr) {
        $type = isset($attr['type']) ? $attr['type']: 'text';
        if (!isset($attr['id'])) $attr['id'] = $this->_form->getDefaultId($name, $type);
        $value = $this->_form->getValue($name);

        $before = (isset($attr['before']) ? $attr['before'] : '');
        unset($attr['before']);
        $after = (isset($attr['after']) ? $attr['after'] : '');
        unset($attr['after']);

        $html = '';

        if (isset($attr['multilang']) && $attr['multilang'] &&
            isset($this->_form->options['lang']) &&
            is_array($this->_form->options['lang']) &&
            count($this->_form->options['lang']) > 1
        ) {
            unset($attr['multilang']);
            $html .= $this->renderMultilang($name, $attr);
        } else {
            if (file_exists($_SERVER['DOCUMENT_ROOT'].'/../framework/S4Y/Form/Field/'.ucfirst($type).'.php')) {
                $className = 'S4Y_Form_Field_' . ucfirst($type);
                S4Y::autoload($className);
                $field = $className::render($name, $value, $attr);
            } elseif (method_exists($this, $type)) {
                $field = static::$type($name, $value, $attr);
            } else {
                if (in_array($type, ['hidden', 'text', 'password', 'number', 'range', 'tel', 'url',
                    'date', 'datetime', 'datetime-local', 'time', 'month', 'week', 'color'])) {
                    $field = static::input($name, $value, $attr);
                } else {
                    $field = static::field($name, $value, $attr);
                }
            }


            $html .= $this->decorateField($field, $attr);
        }

        return $before.$html.$after;
    }

    function render()
    {
        $form = [];
        if ($this->_form->options['form']) {
            $form = Html::form()->_class('form');
            if ($this->_form->options['id']) $form->id($this->_form->options['id']);
            if ($this->_form->options['action']) $form->action($this->_form->options['action']);
            if ($this->_form->options['enctype']) $form->enctype($this->_form->options['enctype']);
            if ($this->_form->options['method']) $form->method($this->_form->options['method']);
        }

        if (isset($this->_form->options['begin'])) {
            $form[] = $this->_form->options['begin'];
        }

        // TODO: CSRF Protection
        //$html .= '<input type="hidden" name="_token" value="'.$this->createToken().'">';

        foreach ($this->_form->fields as $name => &$attr)
        {
            if (is_array($attr)) {
                $form[] = $this->renderField($name, $attr);
            } else {
                $form[] = Html::input()->type('hidden')->name($name)->value($attr);
            }
        }
        unset($attr);

        if (isset($this->_form->options['end'])) {
            $form[] = $this->_form->options['end'];
        }

        if ($this->_form->options['submit']) {
            $form[] = Html::input()->type("submit")
                ->_class("btn btn-default")->value($this->_form->options['submit']);
        }

        return is_array($form) ? join('', $form): $form;
    }


   /* function renderElement() {
        $type = $params['type'] ?: 'text';
        $id = isset($params['id']) ? $params['id'] : S4Y_Form_Helpers::getDefaultId($name, $type);
        $label = $params['label'];
        if (isset($params['none'])) {
            $options = ['' => $params['none']];
            foreach ($params['options'] as $key => $value) {
                $options[$key] = $value;
            }
            //$options = array_merge_recursive(['' => $params['none']], $params['options']);
        } else $options = isset($params['options']) ? $params['options'] : null;


        $html = (isset($params['before']) ? $params['before'] : '');
        / *if ($params['type'] == 'checkbox') {
            $html .= '<div class="checkbox">';
        } else {* /
        $html .= '<div class="form-group">';
        //}

        if (!isset($params['class'])) {
            if ($params['type'] === 'checkbox' ||
                $params['type'] === 'radio') {
            } else {
                $params['class'] = 'form-control';
            }
        }

        $srcName = $name;

        if (isset($params['multilang']) && $params['multilang']) {
            $names = ['default' => $name];
            foreach ($this->options['languages'] as $lang) {
                if ($lang['id'] == $this->options['defaultLang']) continue;
                $names[$lang['name']] = $name .'_'.$lang['id'];
            }
        } else {
            $names = [$name];
        }
        foreach ($names as $langName => $name) {
            if ($name !== $srcName) {
                $label = $langName;
                $params['required'] = false;
            } elseif (isset($params['multilang']) && $params['multilang']) {

            }

            switch ($type) {
                case 'checklist':
                    $html .= S4Y_Form_Checklist::render($name, $label, $this->getValue($name), $options, $id, $params);
                    break;
                case 'hidden':
                case 'text':
                case 'password':
                case 'number':
                case 'range':
                case 'tel':
                case 'url':
                case 'date':
                case 'datetime':
                case 'datetime-local':
                case 'time':
                case 'month':
                case 'week':
                case 'color':
                    if ($type === 'text' && isset($params['options']))
                    {
                        $dataListId = $id.'__list';
                        $html .= S4Y_Form_Helpers::datalist($dataListId, $params['options']);
                        $params['list'] = $dataListId;
                    }
                    $html .= S4Y_Form_Helpers::input($type, $name, $label, $this->getValue($name), $id, $params);
                    break;
                case 'checkbox':
                    $html .= S4Y_Form_Helpers::checkbox($name, $label, $params['value'], $this->getValue($name), $id, $params);
                    break;
                case 'radio':
                    $html .= S4Y_Form_Helpers::radioGroup($name, $label, $options, $this->getValue($name), $id, $params);
                    break;
                case 'select':
                    $html .= S4Y_Form_Helpers::select($name, $label, $options, $this->getValue($name), $id, $params);
                    if (!empty($params['filter'])) {
                        foreach ($params['filter'] as $filterAttr => $filterName) {
                            $thisId = '';
                            $parentId = '';
                            if (isset($this->fields[$filterName]['id'])) {
                                $parentId = $this->fields[$filterName]['id'];
                            }
                            if (empty($parentId)) $parentId = S4Y_Form_Helpers::getDefaultId($filterName, 'select');

                            if (isset($params['id'])) {
                                $thisId = $params['id'];
                            }
                            if (empty($thisId)) $thisId = S4Y_Form_Helpers::getDefaultId($name, 'select');

                            $this->_script['filter_' . $name . '_by' . $filterAttr] = '
                                function filter_' . $name . '_by' . $filterAttr . '() {
                                    var id = $("#' . $parentId . '").val();
                                    $("#' . $thisId . ' option[data-' . $filterAttr . '!=\""+id+"\"]").hide();' .
                                ($params['none'] ? '$("#' . $thisId . ' option:first-child").show();' : '') .
                                '$("#' . $thisId . ' option[data-' . $filterAttr . '=\""+id+"\"]").show();
                                }
                            ';

                            $this->_script['onload'] .= '                       
                                $("#' . $parentId . '").on("change",
                                    function() {
                                        filter_' . $name . '_by' . $filterAttr . '();
                                        $("#' . $thisId . '").val("");
                                    });
                                
                                filter_' . $name . '_by' . $filterAttr . '();
                            ';
                        }
                    }
                    break;
                case 'textarea':
                    $html .= S4Y_Form_Helpers::textarea($name, $label, $this->getValue($name), $id, $params);
                    break;
                case 'img':
                    $html .= S4Y_Form_Helpers::img($name, $label, $params['category'], $this->getValue($name), $id, $params);
                    break;
                case 'imgFile':
                    $html .= S4Y_Form_File::imgfile($name, $label, isset($params['src']) ? $params['src'] : '' , $id, $params);
                    break;
                case 'file':
                    $html .= S4Y_Form_File::file($name, $label, $this->getValue($name), $id, $params);
                    break;
                case 'summernote':
                    $html .= S4Y_Form_SummerNote::summerNote($name, $label, $params['options'], $this->getValue($name), S4Y_Form_Helpers::getDefaultId($name, $type), $params);
                    break;
            }

            if (isset($params['multilang']) && $params['multilang'] && $name === $srcName) {
                //$html = str_replace("$label</label>", $label.'</label><div class="input-group">', $html);
                $html .= '
                    <a class="btn btn-default btn-xs" role="button" data-toggle="collapse" 
                    title="Показать переводы" style="float:right;margin-top:-30px;margin-right:5px"
                    href="#multilang_'.$id.'" aria-expanded="false" aria-controls="multilang_'.$id.'">
                      <span class="glyphicon glyphicon-globe"></span>
                    </a>';
                if (isset($params['desc'])) {
                    $html .= '<p class="help-block">' . $params['desc'] .'</p>';
                }
                $html .= '                    
                    </div>
                  <div class="collapse" id="multilang_'.$id.'">
                  <div class="well">';
            }
        }
        if (isset($params['multilang']) && $params['multilang']) {
            $html .= '</div></div>';
        } else {
            if (isset($params['desc'])) {
                $html .= '<p class="help-block">' . $params['desc'] . '</p>';
            }
            $html .= '</div>';
        }
        if (isset($params['after'])) $html .= $params['after'];
        return $html;
    }*/




}