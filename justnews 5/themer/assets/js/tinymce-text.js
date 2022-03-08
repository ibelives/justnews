(function($) {
    tinymce.create('tinymce.plugins.wpcomtext', {
        init : function(ed, url) {
            ed.addButton('wpcomtext', {
                icon: "code",
                tooltip : "切换到文本",
                onclick: function(){
                    tinymce.EditorManager.execCommand('mceToggleEditor', true, ed.id);
                    var $el = $('#wp-'+ed.id+'-wrap');
                    if(tinymce.EditorManager.get(ed.id).hidden){
                        $el.removeClass('tmce-active').addClass('html-active');
                        $el.find('.mce-i-code').closest('.mce-btn').addClass('mce-active');
                    }else{
                        $el.removeClass('html-active').addClass('tmce-active');
                        $el.find('.mce-i-code').closest('.mce-btn').removeClass('mce-active');
                    }
                }
            });
        }
    });
    // Register plugin
    tinymce.PluginManager.add('wpcomtext', tinymce.plugins.wpcomtext);
})(jQuery);