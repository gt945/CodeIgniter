Class('App.Validate.Publishrecords', 'xui.Module',{
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
			var sp=parseInt('0'+data.StartPage,10);
			var ep=parseInt('0'+data.EndPage,10);
			if(sp<=ep&&sp&&ep) {
				var bp=parseInt('0'+data.BeforePageCount,10);
				var ap=parseInt('0'+data.AfterPageCount,10);
				var tp=bp+ap+ep-sp+1;
				db.getUI("TotalPageCount").setUIValue(tp, true);
				var kai=parseInt('0'+data.KaiId,10);
				var ps;
				if (kai>0){
					ps=tp/kai;
				}else{
					ps=0;
					xui.message('开数错误')
				}
				db.getUI("PrintSheetCount").setUIValue((ps*2+0.4999).toFixed(0)/2, true);
			}else{
				db.getUI("PrintSheetCount").setUIValue(0, true);
			}
			if(data.PicturePage.length){
				var picp=data.PicturePage.split(',');
				_.arr.removeDuplicate(picp);
				_.arr.removeValue(picp,"0");
				_.arr.removeValue(picp,"");
				db.getUI("PicturePage").setUIValue(picp.join(','),true);
				db.getUI("PicPageCount").setUIValue(picp.length,true);
			}else{
				db.getUI("PicPageCount").setUIValue(0,true);
			}
		}
	}
});
