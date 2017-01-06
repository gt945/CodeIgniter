Class('App.QKZX.NewPlan', 'xui.Module',{
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
                    .setHeight(100)
                    .setResizer(false)
                    .setCaption("制定计划")
                    .setMovable(false)
                    .setMinBtn(false)
                    .setMaxBtn(false)
            );

            host.dialog.append(
                (new xui.UI.HTMLButton())
                    .setHost(host,"gen_button")
                    .setLeft(160)
                    .setTop(20)
                    .setWidth(100)
                    .setHeight(25)
                    .setHtml("生成")
                    .onClick("_gen_button_onclick")
            );

            host.dialog.append(
                (new xui.UI.Input())
                    .setHost(host,"ctl_input1")
                    .setLeft(20)
                    .setTop(20)
                    .setLabelSize(70)
                    .setLabelCaption("年份")
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
            ns.dialog.busy();
            xui.request(SITEURL+"QKZX/generate_new_plan", null, function(rsp){
//                 if(!ns.isDestroyed()){
//                     if (rsp.code != 200) {
//                         grid.updateCell(cell.id, {value:ovalue}, true, false);
//                     }
//                 }
                ns.dialog.free();
            },function(){
//                 grid.updateCell(cell.id, {value:ovalue}, true, false);
                ns.dialog.free();
            },null,{method:'post'});
        }

    }
});
