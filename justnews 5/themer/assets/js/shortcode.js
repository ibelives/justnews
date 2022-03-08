(function ($) {
    // 组件功能代码
    tinymce.create("tinymce.plugins.wpcom_shortcodes", {
        init : function(ed, url) {
            // Register example button
            ed.addButton("wpcom_shortcodes", {
                id : "wpcom_shortcode_button",
                title : "添加组件",
                //cmd : "wpcom_shortcodes",
                image : url.replace('assets/js', 'assets/images') + "/shortcodes.png",
                onclick: function(){
                    $("#sc-iframe").html('<iframe class="sc-iframe" frameborder="0" src="'+ajaxurl+'?action=wpcom_mce_panel&post='+_panel_options.post_id+'"></iframe>');
                    $("#sc-modal").show();
                    jQuery("body").addClass("modal-open");
                }
            });
            $("body").append('<div class="modal" id="sc-modal"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title">添加组件</h4></div><div class="modal-body" id="sc-iframe"></div></div></div></div>');
        },
        getInfo : function() {
            return {
                longname : "WPCOM组件添加插件",
                author : "Lomu",
                authorurl : "https://www.wpcom.cn",
                infourl : "http://www.tinymce.com/wiki.php/API3:method.tinymce.Plugin.init",
                version : "1.0"
            };
        }
    });
    // Register plugin
    tinymce.PluginManager.add('wpcom_shortcodes', tinymce.plugins.wpcom_shortcodes);
})(jQuery);