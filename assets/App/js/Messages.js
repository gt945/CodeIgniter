Class('App.Messages', 'xui.Module',{
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
				.setAltRowsBg(true)
				.afterUIValueSet("_grid_afteruivalueset")
				.afterRowActive("_grid_afterrowactive")
				.onDblclickCell("_grid_ondblclickcell")
				.beforeColSorted("_grid_beforecolsorted")
				.afterColResized("_grid_aftercolresized")
				.onResize("_grid_resize")
			);
			
			append((new xui.UI.ToolBar())
				.setHost(host,"toolbar")
				.setItems([{"id":"grp1", "sub":[
					 {"id":"delete","image":"@xui_ini.appPath@image/delete.png","caption":"删除","disabled":true},
					 {"id":"filter","image":"@xui_ini.appPath@image/filter.png","caption":"搜索"},
					 {"id":"send","image":"@xui_ini.appPath@image/message.png","caption":"发送消息"}
				], "caption":"grp1"}])
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
			
				// append((new xui.UI.Block())
			// 	.setHost(host,"block")
			// 	.setHeight(200)
			// 	.setDock("bottom")
			// 	.setBorderType("none")
			// );
				
			return children;
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this,
				grid=ns.grid;
			AJAX.callService('xui/request',ns.properties.target,"grid",{mid:ns.properties.id},function(rsp){
				ns.setProperties(rsp.data);
				if(ns.properties.gridTreeMode){
						grid.setTreeMode(true)
						.setRowHandlerWidth(100)
						.setSelMode("single")
						.onGetContent("_grid_ongetcontent")
						.setRowNumbered(true);
						ns.properties.pageSize=-1;
				}else{
						grid.setTreeMode(false).setRowHandlerWidth(24).setSelMode("multi");
				}
				if(ns.properties.gridGroup){
						var item=ns.toolbar.getItemByItemId('group');
						ns._gid=item.gid;
						ns._ogid={value:item.gid,caption:item.caption};
				}
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
				nodeid:ns._nodeid,
				page:ns._curPage,
				filters:ns._filters,
				size:pageSize,
				sidx:ns.properties.sidx,
				sord:ns.properties.sord,
				search:ns._search,
				gid:ns._gid,
				sub:ns._sub
			};
			AJAX.callService('xui/request',ns.properties.gridId,"getlist",post,function(rsp){
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
		_grid_ondblclickcell:function (profile, cell, e, src){
			var ns = this, 
				row=profile.boxing().getRowbyCell(cell),
				recordId=row.id;
			ns._openForm([recordId]);
		},
		_grid_beforecolsorted:function(profile,col){
			var ns=this;
			if (col && col.sort) {
				col.sortby=function(){
					return 0;
				};
				ns.properties.sidx=col.id;
				if (col._order){
					ns.properties.sord="desc";
				}else{
					ns.properties.sord="asc";
				}
				ns.loadGridData(ns._curPage);
				return true;
			} else {
				return false;
			}
		},
		_openForm:function(recordIds){
			var ns = this,grid=ns.grid;
			var row=grid.getActiveRow();
			var prop={
				recordIds:recordIds
			};
			if (row){
				prop['activeId']=row.id;
			}
			_.merge(prop, ns.properties);
			if (ns.properties.gridForm) {
				xui.ModuleFactory.newCom(ns.properties.gridForm,function(){
					if (!_.isEmpty(this)){
						this.show();
					}
				},null,prop,{
					onNavigate:function(dir){
						ns._navigate(this,dir);
					},
					onUpdateUI:function(id){
						AJAX.callService('QKZX/request',null,"message_show",{id:id},function(rsp){
							ns.grid.updateCellByRowCol(id, 'IsRead', {value:'Y',caption:'已读'}, false, false);
						});
					}
				});
			}
		},
		_openFilter:function(){
				var ns = this;
				if (ns.properties.filterForm){
					ns.properties.filterForm.mainDlg.show(null,true);
				}else{
					xui.ModuleFactory.newCom(ns.properties.gridFilter,function(){
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
		_delRecords:function(ids){
			var ns=this, grid=ns.grid;
			AJAX.callService('xui/request',ns.properties.gridId,"delete",{ids:ids},function(rsp){
				grid.setActiveRow(null);
				grid.setUIValue(null,true);
				xui.message("已删除"+ids.length+"条数据");
				if(ns.properties.gridTreeMode){
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
			var ns=this,ctrl=profile.boxing();
			switch(item.id){
			case "filter":
				ns._openFilter();
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
			case "send":
				xui.ModuleFactory.newCom("App.SendMessage", function(){
					this.show();
				});
				break;
			}
		},
		_grid_afterrowactive:function (profile, row){
			var ns = this,ctrl=profile.boxing();
			var value = ctrl.getUIValue();
			if (value) {
				var values=value.split(';');
				this.toolbar.updateItem("edit",{disabled:!row||values.length<1});
				if(ns.properties.gridTreeMode&&row){
					var setting=ns.properties.gridSetting;
					var name=setting[ns.properties.gridTreeMode].tree_field;
						  var cell=ctrl.getCellbyRowCol(row.id, name);
					ns._opid={
						value:row.id,
						caption:cell.value
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
					if (values.length==1){
						ctrl.setActiveRow(values[0]);
					}
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
			AJAX.callService('xui/request',ns.properties.gridId,"resize",{name:colId,width:width}, null);
		},
		_grid_resize:function(profile,w,h){
			var ns=this;
			ns.properties.pageSize=parseInt((h-27)/21,10);
		},
		_navigate:function(app,dir){
			var ns = this;
			switch(dir){
				case 1:
				case -1:
					var rows=ns.grid.getRows();
					var i=_.arr.subIndexOf(rows, 'id', ns.grid.getActiveRow().id);
					if(i>=0&&rows[i+dir]) {
						i+=dir;
						ns.grid.setUIValue(rows[i].id);
						app.navigateTo(rows[i].id);
					}
					break;
			}
		}
	}
});
