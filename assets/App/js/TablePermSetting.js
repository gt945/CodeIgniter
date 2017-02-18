Class('App.TablePermSetting', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		properties : {},
		initialize : function(){
		},
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};

			append((new xui.UI.ToolBar())
				.setHost(host,"toolbar")
				.setItems([{"id": "grp1", "sub": [{"id": "save", "caption": "保存"}], "caption": "grp1"}])
				.onClick("_toolbar_onclick")
			);

			append(
				(new xui.UI.Tabs())
					.setHost(host,"tabs")
					.setItems([{
						"id" : "role_c",
						"caption" : "插入"
					},{
						"id" : "role_r",
						"caption" : "读取"
					},{
						"id" : "role_u",
						"caption" : "更新"
					},{
						"id" : "role_d",
						"caption" : "删除"
					}])
					.setValue("role_c")
					.onIniPanelView("_tabs_oninipanelview")
			);

			var tabs=["role_c","role_r","role_u","role_d"];
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

		},
		_toolbar_onclick:function(profile, item, group, e, src){
			var ns=this,ctrl=profile.boxing();
			switch(item.id){
				case "save":
					ns._save();
					break;
			}

		},
		_tabs_oninipanelview:function(profile, item){
			var ns = this, uictrl=profile.boxing(),grid=ns["grid_"+item.id];
			AJAX.callService('system/request',null,"table_permission",{type:item.id},function(rsp){
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
			var tabs=["role_c","role_r","role_u","role_d"];
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
					AJAX.callService('system/request',null,"table_permission_save",post,function(rsp){
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
