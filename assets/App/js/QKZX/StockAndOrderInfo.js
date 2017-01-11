Class('App.QKZX.StockAndOrderInfo', 'xui.Module',{
    Instance:{
        autoDestroy : true,
        properties : {},
        initialize : function(){
        },
        iniComponents : function(){
            var host=this, children=[], append=function(child){children.push(child.get(0));};

            append(
                (new xui.UI.Panel())
                    .setHost(host,"mainPanel")
                    .setDock("none")
                    .setLeft(0)
                    .setTop(0)
                    .setWidth(480)
                    .setHeight(400)
                    .setZIndex(1)
                    .setCaption("库存与收发货信息")
                    .setCloseBtn(true)
                    .beforeClose("_mainpanel_beforeclose")
            );

            host.mainPanel.append(
                (new xui.UI.Tabs())
                    .setHost(host,"tabs")
                    .setItems([{
                        "id" : "a",
                        "caption" : "预留库存"
                    },{
                        "id" : "b",
                        "caption" : "到货情况"
                    },{
                        "id" : "c",
                        "caption" : "实际库存"
                    },{
                        "id" : "d",
                        "caption" : "发货情况"
                    },{
                        "id" : "e",
                        "caption" : "编辑部要刊数"
                    }])
                    .setValue("a")
                    .onIniPanelView("_tabs_oninipanelview")
            );
            var tabs=["a","b","c","d","e"];

            _.arr.each(tabs,function(i){
                host.tabs.append(
                    (new xui.UI.TreeGrid())
                        .setHost(host,"grid_"+i)
                        .setRowHandlerWidth(27)
                        .setRowHandler(false)
                        .setTreeMode(false)
                    ,i
                );
                host.tabs.append(
                    (new xui.UI.Block())
                        .setHost(host,"ctl_block_"+i)
                        .setDock("top")
                        .setHeight(30)
                    , i
                );

                host["ctl_block_"+i].append(
                    (new xui.UI.SButton())
                        .setHost(host)
                        .setTop(3)
                        .setWidth(80)
                        .setRight(10)
                        .setImage("@xui_ini.appPath@image/refresh.png")
                        .setCaption("刷新")
                        .onClick("_ctl_sbutton1_onclick")
                );

                host["ctl_block_"+i].append(
                    (new xui.UI.PageBar())
                        .setHost(host,"pagebar_"+i)
                        .setTop(3)
                        .setRight(100)
                        .setCaption("页数:")
                        .onClick("_pagebar_onclick")
                );

            });
            host.mainPanel.append(
                (new xui.UI.Block())
                    .setHost(host,"ctl_block10")
                    .setDock("bottom")
                    .setHeight(40)
            );
            host.ctl_block10.append(
                (new xui.UI.SButton())
                    .setHost(host)
                    .setTop(10)
                    .setWidth(80)
                    .setRight(25)
                    .setCaption("关闭")
                    .onClick("_ctl_sbutton3_onclick")
            );

            return children;
        },
        _fillGrid:function(id,headers,rows){
            var ns=this,grid=ns["grid_"+id];
            grid.setHeader(headers);
            grid.setRows(rows);
            grid.activate();
        },
        customAppend : function(parent, subId, left, top){
            var ns=this, root=ns.mainPanel,
                domId=root.getDomId();
            root.getRoot().popToTop(ns.properties.pos);
            xui.Event.keyboardHook("esc", false, false, false,function(){
                ns.destroy();
            },null,null,domId);

            return true;
        },
        loadGridData:function(id,curPage){
            var ns=this,
                grid=ns.grid;
            this._curPage=curPage;
            var paras={
                field:ns.properties.field,
                page:curPage,
                size:20,
                relate:ns.properties.relate
            };

            AJAX.callService("QKZX/request", id, "get_stock_and_order_info", paras, function(rsp){
                if(!ns.isDestroyed()){
                    ns["pagebar_"+id].setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count,10)/20) ),true);
                    ns._fillGrid(id,rsp.data.headers, rsp.data.rows);
                }
            }, function(){
                ns.mainPanel.busy();
            },function(result){
                ns.mainPanel.free();
            });
        },
        _ctl_sbutton1_onclick:function (profile, e, src, value){
            var id=this.tabs.getValue();
            this.loadGridData(id,this._curPage);
        },
        _ctl_sbutton3_onclick:function(){
            var ns=this;
            ns.destroy();
        },
        _pagebar_onclick:function (profile, page){
            var id=this.tabs.getValue();
            this.loadGridData(id,page);
        },
        _tabs_oninipanelview:function(profile, item){
            var ns = this, uictrl=profile.boxing();
            ns.loadGridData(item.id, 1);
        }
    }
});
