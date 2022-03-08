(function($) {
    tinymce.create("tinymce.plugins.wpcomimg", {
        init : function(ed, url) {
            ed.addButton("wpcomimg", {
                icon: "image",
                tooltip : "添加图片",
                onclick: function(){
                    var uploader;
                    if (uploader) {
                        uploader.open();
                    }else{
                        uploader = wp.media.frames.file_frame = wp.media({
                            title: "选择图片",
                            button: {
                                text: "插入图片"
                            },
                            library : {
                                type : "image"
                            },
                            multiple: true
                        });
                        uploader.on("select", function() {
                            var attachments = uploader.state().get("selection").toJSON();
                            var img = "";
                            for(var i=0;i<attachments.length;i++){
                                img += "<img src=\""+attachments[i].url+"\" width=\""+attachments[i].width+"\" height=\""+attachments[i].height+"\" alt=\""+(attachments[i].alt?attachments[i].alt:attachments[i].title)+"\">";
                            }
                            tinymce.activeEditor.execCommand("mceInsertContent", false, img)
                        });
                        uploader.open();
                    }
                }
            });
        }
    });
    // Register plugin
    tinymce.PluginManager.add("wpcomimg", tinymce.plugins.wpcomimg);
})(jQuery);