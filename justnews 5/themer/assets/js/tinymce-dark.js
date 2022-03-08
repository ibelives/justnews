(function($) {
    tinymce.create('tinymce.plugins.wpcomdark', {
        init : function(ed, url) {
            ed.addButton('wpcomdark', {
                icon: 'light',
                tooltip : '风格切换',
                onclick: function(){
                    var $el = $('#wp-'+ed.id+'-wrap');
                    $el.toggleClass('dark');
                }
            });
        }
    });
    // Register plugin
    tinymce.PluginManager.add('wpcomdark', tinymce.plugins.wpcomdark);
})(jQuery);