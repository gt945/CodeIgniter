Class('App.GridEditor', 'xui.Com',{
    Instance:{ 
    	properties : {
    		pageSize:20,
    		sidx:"",
    		sord:"",
    		search:false
    	},
        iniComponents : function(){
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append((new xui.UI.TreeGrid())
            .setHost(host,"grid")
            .setShowDirtyMark(false)
            .setColHidable(true)
            .setValue("")
            .afterUIValueSet("_grid_afteruivalueset")
            .afterRowActive("_grid_afterrowactive")
            .onDblclickCell("_grid_ondblclickcell")
            .beforeColSorted("_grid_beforecolsorted")
            );
            
            append((new xui.UI.ToolBar())
            .setHost(host,"toolbar")
            .setItems([{"id":"grp1", "sub":[{"id":"null","caption":""}], "caption":"grp1"}])
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
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
        	var ns=this,
        		grid=ns.grid;
        	AJAX.callService(ns.getProperties("target"),"grid",null,function(rsp){
        		ns.setProperties(rsp.data);
        		ns.toolbar.setItems(_.unserialize(ns.getProperties("gridToolBarItems")));
        		if(ns.getProperties("gridTreeMode")){
        			grid.setTreeMode(true)
        			.setRowHandlerWidth(60)
        			.setSelMode("single")
        			.onGetContent("_grid_ongetcontent")
        			.setRowNumbered(true);
        			ns.setProperties("pageSize", -1);
        		}else{
        			grid.setTreeMode(false).setRowHandlerWidth(18).setSelMode("multibycheckbox");
        		}
        		ns._fillHeader(ns.getProperties("gridCols"),ns.getProperties("gridSetting"));
        		ns.loadGridData(1);
        	},function(){
                grid.busy("正在处理 ...");
            });
        },
        _fetch_data:function(callback){
        	var ns=this, grid=ns.grid;
            var pageSize=ns.getProperties("pageSize");
            var post={
            	nodeid:ns._nodeid,
                page:ns._curPage,
                size:pageSize,
                sidx:ns.getProperties("sidx"),
                sord:ns.getProperties("sord"),
                search:ns.getProperties("search"),
                gid:ns._gid,
                sub:ns._sub
            };
            AJAX.callService(ns.getProperties("gridName"),"getlist",post,function(rsp){
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
            var pageSize=ns.getProperties("pageSize");
            
            ns._curPage=curPage;
            ns._fetch_data(function(rsp){
            	if(!ns.isDestroyed()){
                    ns.pagebar.setValue("1:"+curPage+":"+( Math.ceil(parseInt(rsp.data.count[0][0],10)/pageSize) ),true);
                    ns._fillGrid(rsp.data.rows);
                }
            });
        },
        _grid_ongetcontent:function(profile, row, callback){
        	 var ns=this, grid=ns.grid;
        	 ns._nodeid=row.id;
        	 ns._fetch_data(function(rsp){
        		 if(!ns.isDestroyed()){
                	 callback(ns._buildRows(ns.header,ns.cols,ns.setting,rsp.data.rows));
                 }
             });
        },
        _fillHeader:function(cols,setting) {
        	var ns=this,
            	grid=ns.grid;
        	ns.cols = cols;
        	ns.setting = setting;
        	ns.header = this._buildHeader(cols,setting);
        	grid.setHeader(ns.header);
        },
        _fillGrid:function(rows){
            var ns=this,
                grid=ns.grid;
        	var grows = this._buildRows(ns.header,ns.cols,ns.setting,rows);
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
                    header.push(obj);
                }
            });
            
            return header;
        },
        _buildRows:function(header,cols,setting,rows){
            var ns=this,caps={},checkbox={},id=0;
            _.each(cols,function(o,i){
            	if(o=="id"){
            		id=i;
            	}
            });
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
                grow={id:row[id],cells:[],sub:false};
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
                index=_.arr.indexOf(cols, "_sub");
                if(index>0){
                	grow.sub=row[index];
                }
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
        _grid_beforecolsorted:function(profile,col){
        	var ns=this;
        	if (col) {
        		ns.setProperties("sidx",col.id);
        		if (col._order){
        			ns.setProperties("sord","desc");
        		}else{
        			ns.setProperties("sord","asc");
        		}
        		ns.loadGridData(ns._curPage);
        		col.sortby=function(){return false};
        		return true;
        	} else {
        		return false;
        	}
        },
        _openForm:function(recordId){
            var ns = this;
            xui.ComFactory.newCom(ns.getProperties("gridForm"),function(){
                var prop={};
                if(_.isSet(recordId)){
                    prop.recordId=recordId;
                }
                this.setProperties(prop);
                this.setEvents({
                    afterCreated:function(data){
                        var rows=ns._buildRows(ns.grid.getHeader(), ns.cols,ns.setting, data.rows);
                        ns.grid.insertRows(rows,data.pid,null,false);
                    },
                    afterUpdated:function(rowId, hash){
                        _.each(hash,function(v, k){
                            ns.grid.updateCellByRowCol(rowId, k, (_.isHash(v)?v:{value:v}), false, false);
                        });
                    }
                });
                this.show();
            },null,ns.properties);
        },
        _openFilter:function(){
        	var ns = this;
        	xui.ComFactory.newCom(ns.getProperties("gridFilter"),function(){
                this.show();
            },null,ns.properties);
        },
        _delRecords:function(ids){
            var ns=this, grid=ns.grid;
            AJAX.callService(ns.getProperties("gridName"),"delete",{ids:ids},function(rsp){
                grid.removeRows(ids);
                grid.setActiveRow(null);
                grid.setUIValue(null,true);
                xui.message("已删除"+ids.length+"条数据");
            },function(){
                xui.Dom.busy("正在处理 ...");
            },function(result){
                xui.Dom.free();
            }); 
        },
        _toolbar_onclick:function (profile, item, group, e, src){
            var ns = this,ctrl=profile.boxing(),row;
            switch(item.id){
            	case "filter":
            		ns._openFilter();
            		break;
                case "new": 
                    ns._openForm();
                    break;
                case "edit": 
                    if((row=ns.grid.getActiveRow())){
                        ns._openForm(row.id);
                    }
                    break;
                case "delete": 
                    var ids=ns.grid.getUIValue(true);
                    if(_.isStr(ids)){
                    	ids=[ids];
                    }
                    if(ids&&ids.length){
                        xui.confirm("确认", "您确定将要删除选中的"+ids.length+"条数据吗?", function(){
                            ns._delRecords(ids);
                        });
                    }else{
                        xui.message("请选择您要删除的数据!");
                    }
                    break;
                case "group":
                	var setting=ns.getProperties("gridSetting")
                	xui.ComFactory.newCom('App.AdvSelect', function(){
                        this.setProperties({
                            key:ns.getProperties("gridName"),
                            field:ns.getProperties("gridGroup"),
                            pos:src,
                            id:ns._gid,
                            setting:setting['gid']
                        });
                        this.setEvents({
                            onSelect:function(value,caption,item){
                            	ns.toolbar.updateItem("group",{caption:caption});
                            	ns._gid=value;
                            	ns.loadGridData(1);
                            }
                        });
                        this.show(); 
                    });
                	break;
                case "sub":
                	if (item.value){
                		ns._sub=1;
                	}else{
                		ns._sub=0;
                	}
                	ns.loadGridData(1);
                	break;
            }
        },
        _grid_afterrowactive:function (profile, row){
            this.toolbar.updateItem("edit",{disabled:!row});
        },
        _grid_afteruivalueset:function (profile, oldValue, newValue){
            this.toolbar.updateItem("delete",{disabled:!newValue});
        },
        _ctl_sbutton1_onclick:function (profile, e, src, value){
        	this._nodeid=null;
            this.loadGridData(this._curPage);
        },
        _pagebar_onclick:function (profile, page){
            this.loadGridData(page);
        }
    }
});