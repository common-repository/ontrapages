(function() 
{
    tinymce.create('tinymce.plugins.ofclicktopop', 
    {
        init : function(ed, url) 
        {
            ed.addButton('ofclicktopop', 
            {
                title : 'Link your ONTRAform',
                image : url+'/oplogo.png',
                onclick : function() 
                {
                    ed.selection.setContent('[of-click-to-pop]' + ed.selection.getContent() + '[/of-click-to-pop]');
                }
            });
        },
        createControl : function(n, cm) 
        {
            return null;
        },
    });
    
    tinymce.PluginManager.add('ofclicktopop', tinymce.plugins.ofclicktopop);


    tinymce.create('tinymce.plugins.embeddedONTRAform', 
    {
        init : function(ed, url) 
        {
            ed.addButton('embeddedONTRAform', 
            {
                title : 'Embed your ONTRAform',
                image : url+'/oplogo.png',
                onclick : function() 
                {
                    ed.selection.setContent('[embeddedONTRAform]');
                }
            });
        },
        createControl : function(n, cm) 
        {
            return null;
        },
    });
    
    tinymce.PluginManager.add('embeddedONTRAform', tinymce.plugins.embeddedONTRAform);

})();