Class('App.Validate.JournalBaseInfo', 'xui.Module',{
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
            var NofPerYear=1;
            switch(data.PublishStyle) {
                case "7":
                    NofPerYear=52;
                    break;
                case "10":
                    NofPerYear=36;
                    break;
                case "15":
                    NofPerYear=24;
                    break;
                case "30":
                    NofPerYear=12;
                    break;
                case "60":
                    NofPerYear=6;
                    break;
                case "90":
                    NofPerYear=4;
                    break;
                case "180":
                    NofPerYear=2;
                    break;
                case "360":
                    NofPerYear=1;
                    break;
            }
            db.getUI("NofPerYear").setUIValue(NofPerYear, true);
        }
    }
});