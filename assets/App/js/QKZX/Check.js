Class('App.QKZX.Check', 'xui.Com',{
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
                    .setHost(host,"dialog")
                    .setLeft(220)
                    .setTop(250)
                    .setWidth(320)
                    .setHeight(140)
                    .setResizer(false)
                    .setCaption("制定计划")
                    .setMovable(false)
                    .setMinBtn(false)
                    .setMaxBtn(false)
            );

            host.dialog.append(
                (new xui.UI.HTMLButton())
                    .setHost(host,"gen_button")
                    .setLeft(100)
                    .setTop(70)
                    .setWidth(100)
                    .setHeight(25)
                    .setHtml("审核")
                    .onClick("_gen_button_onclick")
            );

            host.dialog.append(
                (new xui.UI.SLabel())
                    .setHost(host,"ctl_slabel3")
                    .setLeft(30)
                    .setTop(20)
                    .setWidth(239)
                    .setHeight(35)
                    .setCaption("审核之后将不能编辑和删除")
                    .setHAlign("left")
            );

            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        customAppend : function(parent, subId, left, top){
            this.dialog.showModal(parent, left, top);
            return true;
        },
        _gen_button_onclick:function(profile, e, src, value){
            var ns=this;
            var ids=ns.properties.editor.grid.getUIValue();
            if (ids) {
                var paras={
                    ids:ids.split(';')
                };
                AJAX.callService("QKZX/request", null, "publishnotify_check", paras, function(rsp){
                    if(!ns.isDestroyed()){
                        xui.message("审核成功据");
                        ns.dialog.close();
                    }
                }, function(){
                    ns.dialog.busy();
                },function(result){
                    if(ns.dialog)
                        ns.dialog.free();
                });
            } else {
                xui.alert("未选择数据");
            }

        }

    }
});
