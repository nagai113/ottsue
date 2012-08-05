(function() {
    // Creates a new plugin class and a custom listbox
	
    tinymce.create('tinymce.plugins.themifyMenu', {
		init : function(ed, url) {
			tinymce.plugins.themifyMenu.theurl = url;			
		},
		createControl: function(n, cm) {
		    if (n != 'btnthemifyMenu') return null;

		    var c = cm.createMenuButton('themifyMenu', {
		        title : 'Shortcodes',
		        image : tinymce.plugins.themifyMenu.theurl + '/../img/themify-editor-icon.png'
		    });
			
			var p = this;
		    c.onRenderMenu.add(function(c, m) {
				ed = tinyMCE.activeEditor;
				
		        m.add({title : 'Shortcodes', 'class' : 'mceMenuItemTitle'}).setDisabled(1);
		        
		        p.themifyDialog('Button', 'button', 400, 540, m, ed);
		        
		        c = m.addMenu({title:"Columns"});
				p.themifyCol( '2-1 Half', '2-1', c, ed);
				p.themifyCol( '2-1 Half First', '2-1 first', c, ed);
				p.themifyCol( '3-1 One-Third', '3-1', c, ed);
				p.themifyCol( '3-1 One-Third First', '3-1 first', c, ed);
				p.themifyCol( '4-1 Quarter', '4-1', c, ed);
				p.themifyCol( '4-1 Quarter First', '4-1 first', c, ed);
		        
		        p.themifyDialog('Image', 'img', 400, 250, m, ed);
		        p.themifyDialog('Horizontal Rule', 'hr', 400, 270, m, ed);
		        p.themifyWrap('Quote', 'quote', m, ed);
		        p.themifyWrap('Is Logged In', 'is_logged_in', m, ed);
				p.themifyWrap('Is Guest', 'is_guest', m, ed);
				p.themifyDialog('Map', 'map', 400, 350, m, ed);
				p.themifyDialog('Video', 'video', 400, 250, m, ed);
				p.themifyDialog('Flickr Gallery', 'flickr', 400, 470, m, ed);
		        p.themifyDialog('Post Slider', 'post_slider', 400, 510, m, ed);
		        
		        c = m.addMenu({title:"Custom Slider"});
				p.themifyWrapDialog( 'Slider', 'slider', 400, 520, c, ed);
				p.themifyWrap( 'Slide', 'slide', c, ed);
				
				p.themifyDialog('List Posts', 'list_posts', 400, 500, m, ed);
				p.themifyWrapDialog( 'Box', 'box', 400, 210, m, ed);
				p.themifyDialog('Author Box', 'author_box', 400, 450, m, ed);
	        });
	
	        return c;
		},
		themifyDialog : function(t, sc, w, h, m, ed){
			m.add({
				title : t,
				onclick : function() {
					ed.windowManager.open({
						file : tinymce.plugins.themifyMenu.theurl + '/dialog.php?shortcode=' + sc + '&title=' + t,
						width : w,
						height : h,
						inline : 1
					});
				}
			});
		},
		themifyWrapDialog : function(t, sc, w, h, m, ed){
			m.add({
				title : t,
				onclick : function() {
					ed.windowManager.open({
						file : tinymce.plugins.themifyMenu.theurl + '/dialog.php?shortcode=' + sc + '&title=' + t + '&selection=' + encodeURIComponent(ed.selection.getContent()),
						width : w,
						height : h,
						inline : 1
					});
				}
			});
		},
		themifyWrap : function(t, sc, m, ed) {
			m.add({
				title : t,
				onclick : function() {
					ed.selection.setContent('[' + sc + ']' + ed.selection.getContent() + '[/' + sc + ']');
				}
			})
		},
		themifyCol : function(t, grid, m, ed) {
			m.add({
				title : t,
				onclick : function() {
					ed.selection.setContent('[col grid="' + grid + '"]' + ed.selection.getContent() + '[/col]');
				}
			})
		}
    });
    tinymce.PluginManager.add('themifyMenu', tinymce.plugins.themifyMenu);
})();
