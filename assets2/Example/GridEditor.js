// The default code is a com class (inherited from xui.Com)
Class('App.GridEditor', 'xui.Com',{
    Instance:{ 
        // To initialize internal components (mostly UI controls)
        // *** If you're not a skilled, dont modify this function manually ***
        iniComponents : function(){
            // [[Code created by CrossUI RAD Tools
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append((new xui.UI.TreeGrid())
            .setHost(host,"grid")
            .setShowDirtyMark(false)
            .setSelMode("multibycheckbox")
            .setRowHandlerWidth(28)
            .setColHidable(true)
            .setColMovable(true)
            .setTreeMode(false)
            .setValue("")
            .afterUIValueSet("_grid_afteruivalueset")
            .afterRowActive("_grid_afterrowactive")
            .onDblclickCell("_grid_ondblclickcell")
            );
            
            append((new xui.UI.ToolBar())
            .setHost(host,"toolbar")
            .setItems([{"id":"grp1", "sub":[{"id":"new", "image":"{/}img/new.png", "caption":"New"}, {"id":"open", "image":"{/}img/open.png", "caption":"Open", "disabled":true}, {"id":"delete", "image":"{/}img/delete.png", "caption":"Delete", "disabled":true}], "caption":"grp1"}])
            .onClick("_toolbar_onclick")
            );
            
            append((new xui.UI.SButton())
            .setHost(host,"ctl_sbutton1")
            .setTop(3)
            .setWidth(80)
            .setRight(10)
            .setImage("{/}img/refresh.png")
            .setCaption("Refresh")
            .onClick("_ctl_sbutton1_onclick")
            );
            
            append((new xui.UI.PageBar())
            .setHost(host,"pagebar")
            .setTop(3)
            .setRight(100)
            .onClick("_pagebar_onclick")
            );
            
            return children;
            // ]]Code created by CrossUI RAD Tools
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
            this.loadGridData(1);
        },
        loadGridData:function(curPage){
            var ns=this, grid=ns.grid;
            
            ns._curPage=curPage;
            
            CONF.callService(ns.getProperties("objectName"),"getlist",{
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
            });   
        },
        _fillGrid:function(cols,setting,rows){
            var ns=this,
                grid=ns.grid,
                header = this._buildHeader(cols,setting),
                grows = this._buildRows(header,cols,setting,rows);
            grid.setHeader(header);
            grid.setRows(grows);
        },
        _buildHeader:function(cols,setting){
            var ns=this,
                header=[];
            
            var obj;
            _.arr.each(cols,function(col){
                if(setting[col]){
                    obj=setting[col];
                    obj.id=col;
                    obj.caption = col.replace(/([A-Z]+)/g," $1");
                    header.push(obj);
                }
            });
            
            return header;
        },
        _buildRows:function(header,cols,setting,rows){
            var ns=this,caps={},checkbox={};
            
            _.each(setting,function(o,i){
                if(o.tag){
                    caps[i]=o.tag;             
                }
                if(o.type=="checkbox"){
                    checkbox[i]=1;
                }
            });
            var grows=[],grow,cell,index;
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
                    if(checkbox[col.id]){
                        cell.value=!!parseInt(row[index],10);
                    }
                    grow.cells.push(cell);
                });
                grows.push(grow);
            });
            return grows;
        },
        _grid_ondblclickcell:function (profile, cell, e, src){
            var ns = this, 
                row=profile.boxing().getRowbyCell(cell),
                recordId=row.id;
            ns._openForm(recordId);
        },
        _openForm:function(recordId){
            var ns = this;
            xui.ComFactory.newCom(ns.getProperties("objectForm"),function(){
                var prop={};
                if(_.isSet(recordId)){
                    prop.recordId=recordId;
                }
                this.setProperties(prop);
                this.setEvents({
                    afterCreated:function(data){
                        var rows=ns._buildRows(ns.grid.getHeader(), data.cols,  data.setting, data.rows);
                        ns.grid.insertRows(rows,null,null,true);
                    },
                    afterUpdated:function(rowId, hash){
                        _.each(hash,function(v, k){
                            ns.grid.updateCellByRowCol(rowId, k, (_.isHash(v)?v:{value:v}), false, false);
                        });
                    }
                });
                this.show();
            });
        },
        _delRecords:function(ids){
            var ns=this, grid=ns.grid;
            CONF.callService(ns.getProperties("objectName"),"delete",{ids:ids},function(rsp){
                grid.removeRows(ids);
                xui.message("You deleted "+ids.length+" records!");
            },function(){
                xui.Dom.busy("Deleting Data ...");
            },function(result){
                xui.Dom.free();
            }); 
        },
        _toolbar_onclick:function (profile, item, group, e, src){
            var ns = this,row;
            switch(item.id){
                case "new": 
                    ns._openForm();
                    break;
                case "open": 
                    if((row=ns.grid.getActiveRow())){
                        ns._openForm(row.id);
                    }
                    break;
                case "delete": 
                    var ids=ns.grid.getUIValue(true);
                    if(ids&&ids.length){
                        xui.confirm("Confirm", "Are you sure you want to delete these "+ids.length+" records?", function(){
                            ns._delRecords(ids);
                        });
                    }else{
                        xui.message("You have to select some rows first!");
                    }
                    break;
            }
        },
        _grid_afterrowactive:function (profile, row){
            this.toolbar.updateItem("open",{disabled:!row});
        },
        _grid_afteruivalueset:function (profile, oldValue, newValue){
            this.toolbar.updateItem("delete",{disabled:!newValue});
        },
        _ctl_sbutton1_onclick:function (profile, e, src, value){
            this.loadGridData(this._curPage);
        },
        _pagebar_onclick:function (profile, page){
            this.loadGridData(page);
        }
    }
});