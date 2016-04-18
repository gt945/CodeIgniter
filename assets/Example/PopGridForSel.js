Class('App.PopGridForSel', 'xui.Com',{
    Instance:{
        // To initialize internal components (mostly UI controls)
        // *** If you're not a skilled, dont modify this function manually ***
        iniComponents : function(){
            // [[Code created by CrossUI RAD Tools
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append((new xui.UI.Panel())
            .setHost(host,"mainPanel")
            .setDock("none")
            .setLeft(0)
            .setTop(0)
            .setWidth(350)
            .setHeight(240)
            .setZIndex(1)
            .setCaption("Selection Window")
            .setCloseBtn(true)
            .beforeClose("_mainpanel_beforeclose")
            );
            
            host.mainPanel.append((new xui.UI.TreeGrid())
            .setHost(host,"grid")
            .setSelMode("single")
            .setRowHandlerWidth(27)
            .setRowHandler(false)
            .setTreeMode(false)
            .onRowSelected("_grid_onRowSelected")
            );
            
            host.mainPanel.append((new xui.UI.Block())
            .setHost(host,"ctl_block8")
            .setDock("top")
            .setHeight(30)
            );
            
            host.ctl_block8.append((new xui.UI.SButton())
            .setHost(host,"ctl_sbutton105")
            .setTop(3)
            .setWidth(80)
            .setRight(10)
            .setImage("{/}img/refresh.png")
            .setCaption("Refresh")
            .onClick("_ctl_sbutton1_onclick")
            );
            
            host.ctl_block8.append((new xui.UI.PageBar())
            .setHost(host,"pagebar")
            .setTop(3)
            .setRight(100)
            .onClick("_pagebar_onclick")
            );
            
            return children;
            // ]]Code created by CrossUI RAD Tools
        },
        _fillGrid:function(cols,setting,rows){
            var ns=this,grid=ns.grid,caps={},
                header=[];
            if(setting){
                _.each(setting,function(o,i){
                    if(o.tag){
                        caps[i]=o.tag;             
                    }
                });
            }
            
            var grows=[],grow,cell,obj,index;
            _.arr.each(cols,function(col){
                if(setting && setting[col]){
                    obj=setting[col];
                    obj.id=col;
                    obj.caption = col.replace(/([A-Z]+)/g," $1");
                    header.push(obj);
                }
            });
            
            _.arr.each(rows,function(row){
                grow={id:row[0],cells:[]};
                _.arr.each(header,function(col){
                    index=_.arr.indexOf(cols, col.id);
                    cell={
                        value:row[index]
                    };
                    if(caps[col.id] && (index=_.arr.indexOf(cols,caps[col.id]))!=-1){
                        cell.caption=row[index];
                    }
                    grow.cells.push(cell);
                });
                grows.push(grow);
            });

            grid.setHeader(header);
            grid.setRows(grows);
            
            grid.activate();
        },
        _mainpanel_beforeclose:function (profile){
            this.fireEvent("onCancel");
        },
        customAppend : function(parent, subId, left, top){
            var ns=this, root=ns.mainPanel,
                domId=root.getDomId();
            root.getRoot().popToTop(ns.properties.pos);
            root.getRoot().setBlurTrigger(domId, function(){
                // fire custom event
                ns.fireEvent("onCancel");
                ns.destroy(); 
            });
            xui.Event.keyboardHook("esc", false, false, false,function(){
                // fire custom event
                ns.fireEvent("onCancel");
                ns.destroy(); 
            },null,null,domId);
           
            ns.loadGridData(1);
            return true;
        },
        loadGridData:function(curPage){
            var ns=this, 
                grid=ns.grid;
            CONF.callService(ns.properties.key,"getlistforsel",{
                page:curPage,
                size:CONF.pageSize
            },function(rsp){
                if(!ns.isDestroyed()){
                ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count[0][0],10)/CONF.pageSize) ),true);
                    ns._fillGrid(rsp.data.cols,rsp.data.setting, rsp.data.rows);
                }
            },function(){
                grid.busy("Getting Data ...");
            },function(result){
                grid.free();
                if(result=='fail'){
                    if(!ns.isDestroyed())
                        ns.destroy(); 
                }
            });   
        },
        _grid_onRowSelected:function (profile, row){
            var ns=this,
                grid=ns.grid;
            // fire custom event
            ns.fireEvent("onSelect",[row.id, grid.getHeader("min"), grid.getRowbyRowId(row.id, "min")]);
            ns.destroy(); 
        },
        _ctl_sbutton1_onclick:function (profile, e, src, value){
            this.loadGridData(this._curPage);
        },
        _pagebar_onclick:function (profile, page){
            this.loadGridData(page);
        }
    }
});