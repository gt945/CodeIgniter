Class('App.QKZX.PublishRecords', 'xui.Module',{
	Instance:{
		autoDestroy : true,
		properties : {},
		initialize : function(){
		},
		iniComponents : function(){
			// [[Code created by CrossUI RAD Studio
			var host=this, children=[], append=function(child){children.push(child.get(0));};
			append((new xui.DataBinder())
				.setHost(host,"databinder2")
				.setData()
			);
			append((new xui.UI.Block())
				.setHost(host,"ctl_block")
				.setBorderType("none")
				.setDock("fill")
				.setOverflow("overflow-x:hidden;overflow-y:auto")
			);
			return children;
			// ]]Code created by CrossUI RAD Studio
		},
		events:{"onRender":"_com_onrender"},
		_com_onrender:function (com, threadid){
			var ns=this;
			ns.fireEvent("onWidgetReady",[ns]);
		},
		_load:function(){
			var ns=this;
			var post={
				name:ns.properties.name,
				ids:ns.properties.recordIds,
				field:ns.properties.field
			};
			AJAX.callService('xui/request',ns.properties.parentId,"form",post,function(rsp){
				if(!ns.isDestroyed()){
					ns.setProperties(rsp.data);
					var setting=ns.properties.gridSetting;
					var index=0;
					var data={};
					for(var f in setting){
						var dataField=f;
						if(setting[f].form) {
							var ele=_.unserialize(setting[f].form);

							ele.setProperties('setting',setting[f]);
							ns.ctl_block.append(ele
								.setHost(ns,"form_input_"+index)
								.setDataBinder("databinder2")
								.setDataField(dataField)
								.setLeft(ns._left(setting[f].x)+5)
								.setTop(ns._top(setting[f].y))
								.setWidth(ns._width(setting[f].w)-25)
								.setHeight(ns._height(setting[f].h))
							);

						}
						index++;
					}
				}

			},function() {
				// grid.busy("正在处理 ...");
			},function(result){
				// grid.free();
			});
		},
		_update:function(relate,db,profile){
			var ns=this,db=ns.databinder2,data={};
			var post={
				relate:relate
			};
			if (!profile || (profile && relate[profile.boxing().getDataField()])){
				var odata=db.getData();
				_.each(odata,function(v,k){
					odata[k]='';
				});
				db.setData(odata).updateDataToUI();
				AJAX.callService('QKZX/request',null,"publishrecords",post,function(rsp){
					if(rsp.data && _.isArr(rsp.data.rows)&&rsp.data.rows.length){
						var cells=rsp.data.rows[0].cells,
							settings=ns.properties.gridSetting;
						var i=0;
						_.each(settings, function(s,n){
							if(!s.object&&!s.virtual){
								data[n]=cells[i];
								i++;
							}
						});
					}
					db.setData(data).updateDataToUI(function(map,prf){
						_.each(map,function(v){
							if(v.value===null)
								v.value='';
						});
						return map;
					});
				},function(){
				},function(){
				});
			}

		},
		_width:function(v){
			v=parseInt(v,10);
			return (v+1)*150 + v*30;
		},
		_height:function(v){
			v=parseInt(v,10);
			return (v+1)*24+v*6;
		},
		_left:function(v){
			v=parseInt(v,10);
			return v*180;
		},
		_top:function(v){
			v=parseInt(v,10);
			return v*30;
		},

	}
});
