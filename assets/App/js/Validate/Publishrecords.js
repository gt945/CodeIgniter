Class('App.Validate.Publishrecords', 'xui.Com',{
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

        }
    }
});
