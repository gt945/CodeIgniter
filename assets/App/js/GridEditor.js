Class('App.GridEditor', 'xui.Com',{
	Instance:{ 
		properties : {
				pageSize:20,
				sidx:"",
				sord:"",
				filterForm:null
		},
		initialize : function(){
				var ns=this;
				ns._search=false;
				ns._filters={};
				ns._curPage=1;
				ns._nodeid=0;
		},
		iniComponents : function(){
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			
			append((new xui.UI.TreeGrid())
				.setHost(host,"grid")
				.setShowDirtyMark(false)
				.setColHidable(false)
				.setValue("")
				.setNoCtrlKey(false)
				.afterUIValueSet("_grid_afteruivalueset")
				.afterRowActive("_grid_afterrowactive")
				.onDblclickCell("_grid_ondblclickcell")
				.beforeColSorted("_grid_beforecolsorted")
				.afterColResized("_grid_aftercolresized")
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
							.setRowHandlerWidth(100)
							.setSelMode("single")
							.onGetContent("_grid_ongetcontent")
							.setRowNumbered(true);
							ns.setProperties("pageSize", -1);
					}else{
							grid.setTreeMode(false).setRowHandlerWidth(18).setSelMode("multi");
					}
					if(ns.getProperties("gridGroup")){
							var item=ns.toolbar.getItemByItemId('group');
							ns._gid=item.gid;
							ns._ogid={value:item.gid,caption:item.caption};
					}
					ns._fillHeader(ns.getProperties("gridCols"),ns.getProperties("gridSetting"));
					ns.loadGridData(1);
				},function(){
				grid.busy("正在处理 ...");
			},function(result){
				if (result=='fail'){
					grid.free();
				}
			});
		},
		_fetch_data:function(callback){
			var ns=this, grid=ns.grid;
			var pageSize=ns.getProperties("pageSize");
			var post={
				nodeid:ns._nodeid,
				page:ns._curPage,
				filters:ns._filters,
				size:pageSize,
				sidx:ns.getProperties("sidx"),
				sord:ns.getProperties("sord"),
				search:ns._search,
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
			grid.setActiveRow(null);
			grid.setUIValue(null,true);
			grid.setRows(grows);
		},
		_buildHeader:function(cols,setting){
			var ns=this,
				header=[];
			
			var obj;
			var i=0;
			_.arr.each(cols,function(col){
				if(setting[col] && !setting[col].minify){
					obj=setting[col];
					obj.id=col;
					header.push(obj);
					setting[col].index=i++;
				}
			});
			
			return header;
		},
		_buildRows:function(header,cols,setting,rows){
			var ns=this;
			var grows=[],grow,cell,index;
			_.arr.each(rows,function(row){
				grow={id:row.id,cells:[],sub:false};
				_.arr.each(header,function(col){
					index=_.arr.indexOf(cols, col.id);
					grow.cells.push(row.cells[index]);
				});
				if(typeof row.sub == 'boolean'){
					grow.sub=row.sub;
				}
				grows.push(grow);
			});
			return grows;
		},
		_grid_ondblclickcell:function (profile, cell, e, src){
			var ns = this, 
				row=profile.boxing().getRowbyCell(cell),
				recordId=row.id;
			ns._openForm([recordId]);
		},
		_grid_beforecolsorted:function(profile,col){
			var ns=this;
			if (col && col.sort) {
				col.sortby=function(){return false};
				ns.setProperties("sidx",col.id);
				if (col._order){
					ns.setProperties("sord","desc");
				}else{
					ns.setProperties("sord","asc");
				}
				ns.loadGridData(ns._curPage);
				return true;
			} else {
				return false;
			}
		},
		_openForm:function(recordIds){
			var ns = this;
			var prop={
				recordIds:recordIds,
				_gid:ns._ogid,
				_pid:ns._opid
			};
			_.merge(prop, ns.properties);
			xui.ComFactory.newCom(ns.getProperties("gridForm"),function(){
				this.show();
			},null,prop,{
				afterCreated:function(data){
					var rows=ns._buildRows(ns.grid.getHeader(), ns.cols,ns.setting, data.rows);
                    if(ns.getProperties("gridTreeMode")){
                        var row=ns.grid.getRowbyRowId(data.pid);
                        if(row){
                            if(row.sub){
                                ns.grid.toggleRow(data.pid,true);
                            }else{
                                ns.grid.updateRow(data.pid,{sub:[]});
                            }
                        }
                    }
					ns.grid.insertRows(rows,data.pid,null,false);
				},
				afterUpdated:function(rowIds, hash){
					_.each(rowIds,function(rowId){
						_.each(hash,function(v, k){
							ns.grid.updateCellByRowCol(rowId, k, (_.isHash(v)?v:{value:v}), false, false);
						});
					});
				}
			});
		},
		_openFilter:function(){
				var ns = this;
				if (ns.properties.filterForm){
					ns.properties.filterForm.mainDlg.show(null,true);
				}else{
					ns.properties.filterForm=xui.ComFactory.newCom(ns.getProperties("gridFilter"),function(){
						ns.properties.filterForm=this;
						this.show();
					},null,ns.properties,{
						onSelect:function(filters){
							ns._filters=filters;
							if (filters.rules.length){
								ns._search=true;
							}
							ns.loadGridData(1);
						}
					});
				}
				
		},
		_openExporter:function(){
			var ns = this;
			var prop={_filter:ns._filters,_search:ns._search,_sidx:ns.getProperties("sidx"),_sord:ns.getProperties("sord")};
			_.merge(prop,ns.properties);
			ns.properties.filterForm=xui.ComFactory.newCom(ns.getProperties("gridExporter"),function(){
				this.show();
			},null,prop);
				
		},
		_delRecords:function(ids){
			var ns=this, grid=ns.grid;
			AJAX.callService(ns.getProperties("gridName"),"delete",{ids:ids},function(rsp){
				grid.setActiveRow(null);
				grid.setUIValue(null,true);
				xui.message("已删除"+ids.length+"条数据");
				if(ns.getProperties("gridTreeMode")){
					grid.removeRows(ids);
				}else{
					ns.loadGridData(ns._curPage);
				}
				
			},function(){
				xui.Dom.busy("正在处理 ...");
			},function(result){
				xui.Dom.free();
			}); 
		},
		_toolbar_onclick:function (profile, item, group, e, src){
			var ns = this,ctrl=profile.boxing();
			switch(item.id){
				case "filter":
					ns._openFilter();
					break;
				case "new": 
					ns._openForm([]);
					break;
				case "edit":
					var rows=ns.grid.getUIValue();
					var ids=rows.split(';');
					if(ids.length>0){
						ns._openForm(ids);
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
							value:ns._gid,
							setting:setting['gid']
						});
						this.setEvents({
							onSelect:function(value,caption,item){
								ns.toolbar.updateItem("group",{caption:caption});
								if (ns._gid!=value){
									ns._ogid={value:value,caption:caption};
									ns._gid=value;
									ns.loadGridData(1);
								}
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
				case "export":
						ns._openExporter();
						break;
			}
		},
		_grid_afterrowactive:function (profile, row){
			var ns = this,ctrl=profile.boxing();
			var value = ctrl.getUIValue();
			if (value) {
				var values=value.split(';');
				this.toolbar.updateItem("edit",{disabled:!row||values.length<1});
				if(ns.getProperties("gridTreeMode")&&row){
					var setting=ns.getProperties("gridSetting");
					var name=setting[ns.getProperties("gridTreeMode")].tree_field;
					ns._opid={
						value:row.id,
						caption:row.cells[setting[name].index].value
					};
				}
			}
		},
		_grid_afteruivalueset:function (profile, oldValue, newValue){
			var ns = this,ctrl=profile.boxing();
			if(newValue!=oldValue){
				
				ns.toolbar.updateItem("delete",{disabled:!newValue});
				ns.toolbar.updateItem("edit",{disabled:!newValue});
				if(newValue){
					var values=newValue.split(';');
					ctrl.setActiveRow(values[0]);
				}
			}
		},
		_ctl_sbutton1_onclick:function (profile, e, src, value){
			this._nodeid=0;
			this.loadGridData(this._curPage);
		},
		_pagebar_onclick:function (profile, page){
			this.loadGridData(page);
		},
		_grid_aftercolresized:function(profile,colId,width){
			var ns = this, uictrl = profile.boxing();
			AJAX.callService(ns.getProperties("gridName"),"resize",{name:colId,width:width}, null);
		}
	}
});
