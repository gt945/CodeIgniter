Class('App.QKZX.PrintDelivers', 'xui.Module',{
	autoDestroy : true,
	Instance:{
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			var grid=host.properties.editor.grid;
			var row=grid.getActiveRow();
			if(row){
				xui.Dom.submit(SITEURL+'data/delivers/'+row.id,null);
			}
			return children;
		}
	}
});
