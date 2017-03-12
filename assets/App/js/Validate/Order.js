Class('App.Validate.Order', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};

			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		autoComplete:function(db){
			var data=db.getUIValue();
			// var price=parseFloat("0"+data.CostTotal);
			// var discount=parseInt("0"+data.SaleDiscount,10);

			/*
			var sp=parseInt(data.StartPage,10);
			var ep=parseInt(data.EndPage,10);
			if(sp<=ep&&sp&&ep) {
				db.getUI("PrintSheetCount").setUIValue(ep - sp + 1, true);
			}else{
				db.getUI("PrintSheetCount").setUIValue(0, true);
			}
			if(data.PicturePage.length){
				var picp=data.PicturePage.split(',');
				_.arr.removeValue(picp,"0");
				db.getUI("PicturePage").setUIValue(picp.join(','),true);
				db.getUI("PicPageCount").setUIValue(picp.length,true);
			}else{
				db.getUI("PicPageCount").setUIValue(0,true);
			}
			*/

		}
	}
});