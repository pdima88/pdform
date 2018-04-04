<?php

class S4Y_Form_Field_SummerNote {

    static function render($name, $value = '', $attr = null) {
        $options = isset($attr['options']) ? $attr['options'] : [];
        if (is_array($options)) {
            if (!isset($options['codemirror'])) {
                $options['codemirror'] = [
                    'theme' => 'monokai',
                    'htmlMode' => true,
                    'lineNumbers' => true,
                    'mode' => 'htmlmixed'
                ];
            }
            if (!isset($options['toolbar'])) {
                $options['toolbar'] = [
                    ['magic', ['style', 'clear']],
                    ['undoredo', ['undo', 'redo']],
                    ['style', ['bold', 'italic', 'underline']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fonts', ['fontname', 'fontsize', 'height']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'table', 'hr']],
                    ['media', ['picture', 'video', 'elfinder']],
                    ['misc', ['fullscreen', 'codeview', 'aceCodeEditor', 'help']]
                ];
            }
            if (!isset($options['popover'])) {
                $options['popover'] = [];
            }

            if (!isset($options['popover']['image'])) {
                $options['popover']['image'] = [
                        ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                        ['float', ['floatLeft', 'floatRight', 'floatNone']],
                        ['custom', ['imageAttributes', 'imageShape']],
                        ['remove', ['removeMedia']]
                    ];

            }

            if (!isset($options['popover']['link'])) {
                $options['popover']['link'] = [
                    ['link', ['linkDialogShow', 'unlink']]
                ];
            }
        }

        S4Y_Assets::add('summernote');
        S4Y_Assets::addScript('
            var summerNoteEditorIds = [];
            function elfinderDialog(context) {
                var fm = $("<div/>").dialogelfinder({
                    url : "/scripts/admin?mod=elFinder", // change with the url of your connector
                    lang : "ru",
                    width : 840,
                    height: 450,
                    destroyOnClose : true,
                    getFileCallback : function(files, fm) {
                    console.log(files);
                    context.invoke("editor.insertImage", files.url);
                },
                    commandsOptions : {
                    getfile : {
                        oncomplete : "close",
                        folders : false
                        }
                }
                }).dialogelfinder("instance");
              }
              //hack, content do not saving while in code view
              $(function() {
                  $("form").on("submit", function() {
                      for (var i in summerNoteEditorIds) {
                          var id = summerNoteEditorIds[i];
                          if ($("#"+id).summernote("codeview.isActivated")) {
                              $("#"+id).val($("#"+id).summernote("code"));
                          }
                      }
                      return true;
                  });
              });
        ', 'S4Y_Form:summerNote');
        $id = $attr['id'];
        S4Y_Assets::addScript('
            $(document).ready(function() {
                summerNoteEditorIds.push("'.$id.'");
                $("#'.$id.'").summernote('.json_encode($options).');
            });
            ', 'S4Y_Form:summerNote_'.$id);
        
        return S4Y_Form::attr(Html::textarea($value)->name($name)->_class('form-control'), $attr);
    }

}