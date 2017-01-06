Class('App.FieldPermSetting', 'xui.Com',{
    Instance:{
        autoDestroy : true,
        properties : {},
        initialize : function(){
            var ns=this;
            ns._tid=null;
        },
        iniComponents : function(){
            // [[Code created by CrossUI RAD Studio
            var host=this, children=[], append=function(child){children.push(child.get(0));};

            append((new xui.UI.PopMenu)
                .setHost(host,"pop")
                .onMenuSelected('_menu_selected')
            );

            append(
                (new xui.UI.ToolBar())
                    .setHost(host,"toolbar")
                    .setItems([{
                        "id" : "grp1",
                        "sub" : [{
                            "id": "save",
                            "caption": "保存"
                        },{
                            "id": "table",
                            "label":'数据表',
                            "caption": "未选择",
                            "type": "dropButton"
                        }],
                        "caption" : "grp1"
                    }])
                    .onClick("_toolbar_onclick")
            );

            append(
                (new xui.UI.Tabs())
                    .setHost(host,"tabs")
                    .setItems([{
                        "id" : "role_r",
                        "caption" : "读取"
                    },{
                        "id" : "role_u",
                        "caption" : "更新"
                    }])
                    .setValue("role_r")
                    .onItemSelected("_tabs_onitemselected")
            );

            var tabs=["role_r","role_u"];
            _.arr.each(tabs,function(i){
                host.tabs.append(
                    (new xui.UI.TreeGrid())
                        .setHost(host,"grid_"+i)
                        .setRowHandler(false)
                        .setTreeMode(false)
                    ,i
                );
            });

            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        customAppend : function(parent, subId, left, top){
            return false;
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
            var ns=this,
                grid=ns.grid;
            AJAX.callService('system/request',null,"tables",null,function(rsp){
                ns.pop.setItems(rsp.data);
            },function(){
            },function(){
            });
        },
        _toolbar_onclick:function(profile, item, group, e, src){
            var ns=this,ctrl=profile.boxing();
            switch(item.id){
                case "save":
                    ns._save();
                    break;
                case "table":
                    ns.pop.pop(src);
                    break;
            }

        },
        _menu_selected:function(profile,item,src){
            var ns=this;
            ns.toolbar.updateItem("table",{caption:item.caption});
            ns._tid=item.id;
            ns._load();
        },
        _tabs_onitemselected:function(profile, item){
            var ns = this, uictrl=profile.boxing(),grid=ns["grid_"+item.id];
            var tid=grid.getProperties("tid");
            if (ns._tid!=null&&ns._tid!=tid){
                ns._load();
            }
        },
        _load:function(){
            var ns=this;
            var id=ns.tabs.getUIValue();
            var grid=ns["grid_"+id];
            grid.setProperties("tid",ns._tid);
            AJAX.callService('system/request',null,"field_permission",{type:id,tid:ns._tid},function(rsp){
                grid.setHeader(rsp.data.headers);
                grid.setRows(rsp.data.rows);
            },function(){
                grid.busy("正在处理 ...");
            },function(){
                grid.free();
            });
        },
        _save:function(){
            var ns = this;
            var tabs=["role_r","role_u"];
            _.arr.each(tabs, function(id){
                var grid=ns["grid_"+id];
                var data=grid.getDirtied();
                var post={
                    type:id,
                    data:[]
                };
                var tmp=[];
                _.each(data, function(p){
                    if(!_.isDefined(tmp[p.rowId])){
                        tmp[p.rowId]=[];
                    }
                    tmp[p.rowId].push({role:p.colId,value:p.value});
                });
                _.each(tmp,function(d,i){
                    post.data.push({
                        id:parseInt(i,10),
                        fields:d
                    });
                });
                if (post.data.length) {
                    AJAX.callService('system/request',null,"field_permission_save",post,function(rsp){
                        if(rsp.data==1){
                            grid.resetGridValue();
                        }
                    },function(){
                        grid.busy("正在处理 ...");
                    },function(){
                        grid.free();
                    });
                }
            });
        }
    }
});
