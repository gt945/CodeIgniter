Class('App.QKZX.ExportAllSalesStats', 'xui.Module',{
	autoDestroy : true,
	Instance:{
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			var pns=host.properties.editor;
			var post={
				filters:pns._filters,
				search:pns._search
			};
			
			xui.Dom.submit(SITEURL+'data/sales_all_stats',{paras:post},'post');
			return children;
			// ]]Code created by CrossUI RAD Studio
		}
	}
});

