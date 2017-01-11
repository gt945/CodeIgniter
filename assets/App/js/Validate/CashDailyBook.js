Class('App.Validate.CashDailyBook', 'xui.Module',{
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
            var flag=data.RIDFlag;

            db.getUI("JID").setDisabled(true);
            db.getUI("PID").setDisabled(true);
            db.getUI("EID").setDisabled(true);
            if(flag!="1"){
                db.getUI("JID").setUIValue(null, true);
            } else{
                db.getUI("JID").setDisabled(false);
            }
            if(flag!="2"){
                db.getUI("PID").setUIValue(null, true);
            } else{
                db.getUI("PID").setDisabled(false);
            }
            if(flag!="3"){
                db.getUI("EID").setUIValue(null, true);
            } else{
                db.getUI("EID").setDisabled(false);
            }

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