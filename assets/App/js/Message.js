Class('App.Message', 'xui.Com',{
    Instance:{
        autoDestroy : true,
        properties : {},
        initialize : function(){
        },
        iniComponents : function(){
            // [[Code created by CrossUI RAD Studio
            var host=this, children=[], append=function(child){children.push(child.get(0));};

            append(
                (new xui.UI.Dialog())
                    .setHost(host,"ctl_dialog8")
                    .setLeft(120)
                    .setTop(100)
                    .setWidth(640)
                    .setHeight(480)
                    .setCaption("发送消息")
            );

            host.ctl_dialog8.append(
                (new xui.UI.Block())
                    .setHost(host,"xui_ui_block4")
                    .setDock("top")
                    .setHeight(40)
                    .setBorderType("none")
            );

            host.xui_ui_block4.append(
                (new xui.UI.HTMLButton())
                    .setHost(host,"ctl_htmlbutton2")
                    .setLeft(55)
                    .setTop(10)
                    .setWidth(24)
                    .setHeight(22)
                    .setHtml("+")
            );

            host.xui_ui_block4.append(
                (new xui.UI.SLabel())
                    .setHost(host,"ctl_slabel5")
                    .setLeft(10)
                    .setTop(15)
                    .setCaption("发送给:")
            );

            host.ctl_dialog8.append(
                (new xui.UI.RichEditor())
                    .setHost(host,"ctl_richeditor1")
                    .setDock("fill")
            );

            host.ctl_dialog8.append(
                (new xui.UI.Block())
                    .setHost(host,"xui_ui_block4")
                    .setDock("bottom")
                    .setHeight(35)
                    .setBorderType("none")
            );

            host.xui_ui_block4.append(
                (new xui.UI.SButton())
                    .setHost(host,"btnSave")
                    .setLeft(230)
                    .setTop(10)
                    .setWidth(70)
                    .setCaption("发送")
                    .onClick("_ctl_sbutton14_onclick")
            );

            host.xui_ui_block4.append(
                (new xui.UI.SButton())
                    .setHost(host,"btnClose")
                    .setLeft(340)
                    .setTop(10)
                    .setWidth(70)
                    .setCaption("关闭")
                    .onClick("_ctl_sbutton486_onclick")
            );

            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        customAppend : function(parent, subId, left, top){
            return false;
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
            var ns=this;

        }
    }
});
