Class('App.Validate.Publishnotify', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};

			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		autoComplete:function(db){
			data=db.getUIValue();
			if(!data.BindupTime){
				var dd = new Date();
				dd.setDate(dd.getDate()+8);
				db.getUI("BindupTime").setUIValue(dd, true);
			}
		}
	}
});
