Class('App.Dashboard', 'xui.Com',{
    Instance:{
        properties : {
            pageSize:20
        },
        iniComponents : function(){
            // [[Code created by CrossUI RAD Studio
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            append((new xui.UI.TreeGrid())
                .setHost(host,"grid")
                .setShowDirtyMark(false)
                .setColHidable(false)
                .setValue("")
                .setNoCtrlKey(false)
                .setAltRowsBg(true)
                .onDblclickCell("_grid_ondblclickcell")
                .beforeColSorted("_grid_beforecolsorted")
                .onResize("_grid_resize")
            );

            append((new xui.UI.ToolBar())
                .setHost(host,"toolbar")
                .setItems([{"id":"grp1", "sub":[{"id":"send","caption":"发送消息"}], "caption":"grp1"}])
                .onClick("_toolbar_onclick")
            );

            append((new xui.UI.SButton())
                .setHost(host,"ctl_sbutton1")
                .setTop(3)
                .setWidth(80)
                .setRight(10)
                .setImage("@xui_ini.appPath@image/refresh.png")
                .setCaption("刷新")
                .onClick("_ctl_sbutton1_onclick")
            );

            append((new xui.UI.PageBar())
                .setHost(host,"pagebar")
                .setTop(3)
                .setRight(100)
                .setCaption("页数:")
                .onClick("_pagebar_onclick")
            );

            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
            var ns=this,
                grid=ns.grid;
            AJAX.callService('xui/request',"messages","grid",null,function(rsp){
                ns.setProperties(rsp.data);
                grid.setTreeMode(false).setRowHandlerWidth(24).setSelMode("single");
                grid.setHeader(ns.properties.gridHeaders);
                ns.loadGridData(1);
            },function(){
                grid.busy("正在处理 ...");
            },function(result){
                grid.free();
            });
        },
        _fetch_data:function(callback){
            var ns=this, grid=ns.grid;
            var pageSize=ns.properties.pageSize;
            var post={
                page:ns._curPage,
                size:pageSize,
            };
            AJAX.callService('xui/request',"messages","getlist",post,function(rsp){
                if(!ns.isDestroyed()){
                    callback(rsp);
                }
            },function(){
                grid.busy("正在处理 ...");
            },function(result){
                grid.free();
            });
        },
        loadGridData:function(curPage){
            var ns=this, grid=ns.grid;
            var pageSize=ns.properties.pageSize;

            ns._curPage=curPage;
            ns._fetch_data(function(rsp){
                if(!ns.isDestroyed()){
                    ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count,10)/pageSize) ),true);
                    ns._fillGrid(rsp.data.rows);
                }
            });
        },
        _grid_ongetcontent:function(profile, row, callback){
            var ns=this, grid=ns.grid;
            ns._nodeid=row.id;
            ns._fetch_data(function(rsp){
                if(!ns.isDestroyed()){
                    callback(ns._buildRows(rsp.data.rows));
                }
            });
        },
        _fillGrid:function(rows){
            var ns=this,
                grid=ns.grid;
            var grows = this._buildRows(rows);
            grid.setActiveRow(null);
            grid.setUIValue(null,true);
            grid.setRows(grows);
        },
        _buildRows:function(rows){
            var ns=this;
            _.arr.each(rows,function(row){
                _.arr.each(ns.properties.gridHeaders,function(h,i){
                    if(ns.properties.gridSetting[h.id].type=="checkbox"){
                        row.cells[i].value=!!parseInt(row.cells[i].value,10);
                    }
                });
            });
            return rows;
        },
        _grid_beforecolsorted:function(profile,col){
            return false;
        },
        _grid_resize:function(profile,w,h){
            var ns=this;
            ns.properties.pageSize=parseInt((h-27)/21,10);
        },
        _grid_ondblclickcell:function (profile, cell, e, src){
            var ns = this,
                row=profile.boxing().getRowbyCell(cell),
                recordId=row.id;
            ns._readMessage(recordId);
        },
        _readMessage:function(recordId){
            var ns = this,grid=ns.grid;
            var prop={
                recordIds:recordId
            };
            xui.ModuleFactory.newCom("App.readMessage",function(){
                if (!_.isEmpty(this)){
                    this.show();
                }
            },null,prop,null);
        },
        _ctl_sbutton1_onclick:function (profile, e, src, value){
            this._nodeid=0;
            this.loadGridData(this._curPage);
        },
        _pagebar_onclick:function (profile, page){
            this.loadGridData(page);
        },
        _toolbar_onclick:function (profile, item, group, e, src){
            var ns=this,ctrl=profile.boxing();
            switch(item.id){
                case "send":
                    xui.ModuleFactory.newCom("App.SendMessage",function(){
                        if (!_.isEmpty(this)){
                            this.show();
                        }
                    },null,null,null);
                    break;
            }
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