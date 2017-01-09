Class('App.Dashboard', 'xui.Com',{
    Instance:{
        iniComponents : function(){
            // [[Code created by CrossUI RAD Studio
            var host=this, children=[], append=function(child){children.push(child.get(0));};

            append(
                xui.create("xui.UI.ComboInput")
                .setHost(host,"xui_ui_comboinput107")
                .setLeft(100)
                .setTop(100)
                .setWidth(150)
                .setLabelSize(70)
                .setLabelCaption("当前工作年")
                .setType('input')
                .onChange("_workyear_onchange")
            );

            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        _workyear_onchange:function (profile,oldValue,newValue,force,tag){
            var ns = this, uictrl = profile.boxing();
            if (oldValue!=newValue){
                AJAX.callService('system/request',null,"workyear",{value:newValue},function(rsp){
                },function(){
                    xui.Dom.busy("正在处理 ...");
                },function(){
                    xui.Dom.free();
                });
            }
        }

    }

});