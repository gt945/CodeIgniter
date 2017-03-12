Class('App.Validate.ArrivalManage', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		initialize : function(){
			this.post={
				"JID":null,
				"Year":null,
				"No":null
			};
		},
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};

			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		autoComplete:function(db){
			var ns=this;
			var data=db.getUIValue();
			if(data.JID!=""&&data.Year!=""&&data.No!="") {
				if(data.JID!=ns.post.JID||data.Year!=ns.post.Year||data.No!=ns.post.No){
					ns.post.JID=data.JID;
					ns.post.Year=data.Year;
					ns.post.No=data.No;
					AJAX.callService('QKZX/request',null,"check_arrival",ns.post,function(rsp){
					},function(){
					},function(result){
					});
					
				}
			}
		}
	}
});