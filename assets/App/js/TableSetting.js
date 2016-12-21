Class('App.TableSetting', 'xui.Com',{
    Instance:{
        autoDestroy : true,
        properties : {},
        initialize : function(){
        },
        iniComponents : function(){
            // [[Code created by CrossUI RAD Studio
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append(
                (new xui.UI.Dialog())
                .setHost(host,"panel")
                .setLeft(180)
                .setTop(140)
                .setWidth(250)
                .setHeight(120)
                .setResizer(false)
                .setCaption("自定义表单")
                .setImagePos("left top")
                .setMovable(true)
                .setMinBtn(false)
                .setMaxBtn(false)
                .setCloseBtn(false)
                .setOverflow("hidden")
            );
            
            host.panel.append(
                (new xui.UI.Block())
                .setHost(host,"block")
                .setLeft(5)
                .setTop(0)
                .setWidth(235)
                .setHeight(50)
                .setBorderType("inset")
                .setOverflow("visible")
                );
            
            append(
                (new xui.UI.ComboInput())
                .setHost(host,"table_select")
                .setDirtyMark(false)
                .setLeft(20)
                .setTop(30)
                .setType("listbox")
                .setCaption("选择数据表")
                .onChange("_table_select_onchange")
            );
            
            append(
                (new xui.UI.List())
                .setHost(host,"field_list")
                .setDirtyMark(false)
                .setLeft(20)
                .setTop(90)
                .setDropKeys("fl")
                .setDragKey("fl")
            );
            
            append(
                (new xui.UI.Button())
                .setHost(host,"save")
                .setLeft(390)
                .setTop(90)
                .setWidth(100)
                .setRight(20)
                .setCaption("保存")
                .onClick("_save_onclick")
            );
            
            append(
                (new xui.UI.SLabel())
                .setHost(host,"ctl_slabel3")
                .setLeft(20)
                .setTop(70)
                .setCaption("拖拽调整顺序")
            );
            
            append(
                (new xui.UI.Slider())
                .setHost(host,"rows")
                .setShowDirtyMark(false)
                .setLeft(180)
                .setTop(60)
                .setSteps(30)
                .setIsRange(false)
                .setValue("0")
                .afterUIValueSet("_rows_onchange")
                .onChange("_rows_onchange")
            );
            
            append(
                (new xui.UI.Slider())
                .setHost(host,"cols")
                .setShowDirtyMark(false)
                .setLeft(180)
                .setTop(20)
                .setSteps(8)
                .setIsRange(false)
                .setValue("0")
                .afterUIValueSet("_cols_onchange")
                .onChange("_cols_onchange")
            );
            
            append(
                (new xui.UI.Button())
                .setHost(host,"layout")
                .setLeft(390)
                .setTop(60)
                .setWidth(100)
                .setCaption("自动布局")
                .onClick("_layout_onclick")
            );
            
            append(
                (new xui.UI.ComboInput())
                .setHost(host,"layout_type")
                .setDirtyMark(false)
                .setLeft(390)
                .setTop(30)
                .setWidth(100)
                .setType("listbox")
                .setItems([{
                    "id" : "0",
                    "caption" : "1 X"
                },{
                    "id" : "1",
                    "caption" : "2 X"
                },{
                    "id" : "2",
                    "caption" : "3 X"
                },{
                    "id" : "3",
                    "caption" : "4 X"
                }])
                .setValue("0")
            );
            
            append(
                (new xui.UI.ComboInput())
                .setHost(host,"tablename")
                .setLeft(520)
                .setTop(30)
                .setWidth(100)
                .setType("none")
            );
            
            append(
                (new xui.UI.Button())
                .setHost(host,"addtable")
                .setLeft(520)
                .setTop(60)
                .setWidth(100)
                .setCaption("增加/更新数据表")
                .onClick("_addtable_onclick")
            );
            
            append(
                (new xui.UI.Button())
                .setHost(host,"addgroup")
                .setLeft(520)
                .setTop(90)
                .setWidth(100)
                .setCaption("增加分组")
                .onClick("_addgroup_onclick")
            );

            append(
                (new xui.UI.Pane())
                .setHost(host,"ctl_block67")
                .setLeft(640)
                .setTop(30)
                .setWidth(300)
                .setHeight(100)
            );

            host.ctl_block67.append(
                (new xui.UI.TreeGrid())
                .setHost(host,"grid")
                .setShowDirtyMark(false)
                .setSelMode("single")
                .setEditable(true)
                .setRowHandler(false)
                .setHeader([{
                    "id" : "name",
                    "width" : 180,
                    "type" : "input",
                    "caption" : "组名称"
                },{"id" : "x",
                    "width" : 20,
                    "caption": "x",
                    "readonly" : true
                },{"id" : "y",
                    "width" : 20,
                    "caption": "y",
                    "readonly" : true
                },{"id" : "w",
                    "width" : 20,
                    "caption": "w",
                    "readonly" : true
                },{"id" : "h",
                    "width" : 20,
                    "caption": "h",
                    "readonly" : true
                }])
                .setTreeMode(false)
                .afterCellUpdated("_grid_aftercellupdated")
            );
            
            return children;
            // ]]Code created by CrossUI RAD Studio
        },
        customAppend : function(parent, subId, left, top){
            return false;
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
            var ns=this,
                ts=ns.table_select;
            AJAX.callService('xui/request',null,"tables",null,function(rsp){
                ns._fillSelect(rsp.data);
            });
        },
        _fillSelect:function(data){
            var ns=this,
                ts=ns.table_select;
            ts.setItems(data);
        },
        _table_select_onchange:function (profile, oldValue, newValue, force, tag){
            var ns = this, uictrl = profile.boxing(),
            	item = profile.getItemByItemId(newValue);
            AJAX.callService('xui/request',item.id,"fields",{tid:newValue},function(rsp){
                ns.cols.setUIValue(item.w, true);
                ns.rows.setUIValue(item.h, true);
                ns._fillList(rsp.data.fields);
                ns._fillGroups(rsp.data.groups);
                ns._update_layout();
            });
            return true;
        },
        _fillList:function(data){
            var ns=this,
                fl=ns.field_list;
            fl.setItems(data)
            .setHeight(data.length*21);
        },
        _fillGroups:function(data){
            var ns=this,
                grid=ns.grid;
            grid.setRows();
            _.each(data,function (d,i) {
                    grid.insertRows({id:i,cells:d});
                }
            );
        },
        _adjustFormX:function(x){
            var ns=this;
            width=ns._width(x)+50;
            ns.panel.setWidth(width);
            ns.block.setWidth(width-15);
        },
        _adjustFormY:function(y){
            var ns=this;
            height=ns._height(y)+100;
            ns.panel.setHeight(height);
            ns.block.setHeight(height-70);
        },
        _save_onclick:function (profile, e, value){
            var ns = this, uictrl = profile.boxing()
                ,fl=ns.field_list
                ,grid=ns.grid
                ,ts=ns.table_select
                ,tid=ts.getUIValue()
                ,w=ns.cols.getUIValue()
                ,h=ns.rows.getUIValue();
            AJAX.callService('xui/request',null,"setting",{groups:grid.getRows('min'),fields:fl.getItems(),tid:tid,table_w:w,table_h:h}, function(rsp){
                if(rsp.data==1) {
                    items=ts.getItems();
                    i=_.arr.subIndexOf(items,"id",tid);
                    items[i].w=w;
                    items[i].h=h;
                    xui.message("保存成功");
                } else {
                    xui.alert("保存失败");
                }
            });
        },
        _rows_onchange:function (profile, oldValue, newValue, force, tag){
            var ns = this, uictrl = profile.boxing(),ts=ns.table_select;
            ns._adjustFormY(parseInt(newValue,10));
        },
        _cols_onchange:function (profile, oldValue, newValue, force, tag){
            var ns = this, uictrl = profile.boxing(),ts=ns.table_select;
            
            ns._adjustFormX(parseInt(newValue,10));
        },
        _layout_onclick:function (profile, e, value){
            var ns = this, uictrl = profile.boxing(),fl=ns.field_list;
            items=fl.getItems();
            cols=parseInt(ns.layout_type.getUIValue(), 10);
            ns.cols.setUIValue(cols*2+1, true);
            ns.rows.setUIValue(Math.floor((items.length+cols)/(cols+1))-1, true);
            _.each(items,function(o,i){
                x=(i%(cols+1))*2;
                y=Math.floor(i/(cols+1));
                o.x=x;
                o.y=y;
                o.w=1;
                o.h=0;
            });
            ns._update_layout();
        },
        _width:function(v){
            return (v+1)*120+v*30;
        },
        _width_r:function(v){
            r=Math.round((v-80)/150);
            return r<0?0:r;
        },
        _height:function(v){
            return (v+1)*20+v*10;
        },
        _height_r:function(v){
            r=Math.round((v-20)/30);
            return r<0?0:r;
        },
        _left:function(v){
            return 15+v*120+v*30;
        },
        _left_r:function(v){
            r=Math.round((v-15)/150);
            return r<0?0:r;
        },
        _top:function(v){
            return v*30+15;
        },
        _top_r:function(v){
            r=Math.round((v-15)/30);
            return r<0?0:r;
        },
        _grid_aftercellupdated:function(profile,cell,options,isHotRow){
            var ns=this,
                grid=profile.boxing(),
                row =grid.getRowbyCell(cell),
                id = row.id;
            if(!cell.value){
                grid.removeRows(id);
            }
            ns._update_layout();
        },
        _update_layout:function(){
            var ns = this,fl=ns.field_list,grid=ns.grid;
            var items=fl.getItems();
            var groups=grid.getRows();
            ns.block.removeChildren(null,true);
            _.each(items,function(o,i){
                var x=parseInt(o.x,10);
                var y=parseInt(o.y,10);
                var w=parseInt(o.w,10);
                var h=parseInt(o.h,10);
                var prop={
                    ele:o.id,
                    forceMovable:false,
                    minWidth:0,
                    minHeight:0,
                    dragArgs:{
                        widthIncrement:15,
                        heightIncrement:15
                    },
                    zIndex:xui.Dom.TOP_ZINDEX
                };
                var ele=(new xui.UI.Button())
                    .setHost(ns)
                    .setCaption(o.caption)
                    .setLeft(ns._left(x))
                    .setTop(ns._top(y))
                    .setWidth(ns._width(w))
                    .setHeight(ns._height(h));
                ns.block.append(ele);
                ns.block.append((xui.create({key:'App.AdvResizer'}))
                    .setHost(ns)
                    .setProperties(prop)
                    .resetTarget(ele, false)
                    .onContextmenu(function(){return false;})
                    .onUpdate(function(resizer, target, size, cssPos){
                        if(target){
                            target.each(function(target){
                                target = xui([target]);
                                items=fl.getItems();
                                var i=_.arr.subIndexOf(items,"id",resizer.properties.ele);
                                var profile = xui.UIProfile.getFromDom(target.get(0).id);
                                if(size){
                                    var orig_size=profile.getRoot().cssSize();
                                    items[i].w=ns._width_r(size.width+orig_size.width);
                                    items[i].h=ns._height_r(size.height+orig_size.height);
                                    size.width=ns._width(items[i].w);
                                    size.height=ns._height(items[i].h);
                                    profile.getRoot().cssSize(size);
                                    xui.UI.$tryResize(profile,size.width,size.height,null,true);
                                }
                                if(cssPos){
                                    var orig_pos=profile.getRoot().cssPos();
                                    items[i].x=ns._left_r(cssPos.left+orig_pos.left);
                                    items[i].y=ns._top_r(cssPos.top+orig_pos.top);
                                    cssPos.left=ns._left(items[i].x);
                                    cssPos.top=ns._top(items[i].y);
                                    profile.getRoot().cssPos(cssPos);
                                }
                                
                            });
                        }
                        resizer.boxing().rePosSize();
                        return false;
                    })
                );
            });

            _.each(groups,function(g,i){
                var name=g.cells[0].value;
                var x=parseInt(g.cells[1].value,10);
                var y=parseInt(g.cells[2].value,10);
                var w=parseInt(g.cells[3].value,10);
                var h=parseInt(g.cells[4].value,10);
                var prop={
                    ele:g.id,
                    forceMovable:false,
                    minWidth:0,
                    minHeight:0,
                    dragArgs:{
                        widthIncrement:1,
                        heightIncrement:1
                    },
                    zIndex:xui.Dom.TOP_ZINDEX - 1
                };
                var ele=(new xui.UI.Group())
                    .setHost(ns)
                    .setCaption(name)
                    .setLeft(ns._left(x) - 10)
                    .setTop(ns._top(y) - 30)
                    .setWidth(ns._width(w) + 20)
                    .setHeight(ns._height(h) + 35)
                    .setToggleBtn(false);
                ns.block.append(ele);

                ns.block.append((xui.create({key:'App.AdvResizer'}))
                    .setHost(ns)
                    .setProperties(prop)
                    .resetTarget(ele, false)
                    .onContextmenu(function(){return false;})
                    .onUpdate(function(resizer, target, size, cssPos){
                        if(target){
                            target.each(function(target){
                                target = xui([target]);
                                var row=grid.getRowbyRowId(resizer.properties.ele);
                                var profile = xui.UIProfile.getFromDom(target.get(0).id);
                                if(size){
                                    var orig_size=profile.getRoot().cssSize();
                                    var w=ns._width_r(size.width+orig_size.width - 20) ;
                                    var h=ns._height_r(size.height+orig_size.height - 35);
                                    grid.updateCellByRowCol(row.id,'w',w);
                                    grid.updateCellByRowCol(row.id,'h',h);
                                    size.width=ns._width(w)+ 20;
                                    size.height=ns._height(h) + 35;
                                    profile.getRoot().cssSize(size);
                                    xui.UI.$tryResize(profile,size.width,size.height,null,true);
                                }
                                if(cssPos){
                                    var orig_pos=profile.getRoot().cssPos();
                                    var x=ns._left_r(cssPos.left+orig_pos.left);
                                    var y=ns._top_r(cssPos.top+orig_pos.top + 30);
                                    grid.updateCellByRowCol(row.id,'x',x);
                                    grid.updateCellByRowCol(row.id,'y',y);
                                    cssPos.left=ns._left(x) - 10;
                                    cssPos.top=ns._top(y)- 30;
                                    profile.getRoot().cssPos(cssPos);
                                }
//
                            });
                        }
                        resizer.boxing().rePosSize();
                        return false;
                    })

                );
            });

        },
        _addtable_onclick:function (profile, e, src, value) {
            var ns = this, uictrl = profile.boxing();
            var table = ns.tablename.getUIValue();
            if (table.length) {
                AJAX.callService('xui/request', null, "add_table", {table: table}, function (rsp) {
                    if (rsp.data == 1) {
                        xui.message("保存成功");
                    } else {
                        xui.alert("保存失败");
                    }
                });
            }
        },
        _addgroup_onclick:function() {
            var ns=this,grid=ns.grid;
            var rows=grid.getRows();
            grid.insertRows(["Group_"+(rows.length+1),0,1,1,1]);
            ns._update_layout();
        },
        _ctl_group1_ondrop:function(profile, e, node, key, data, item){
            var target = profile.boxing(),
                source = data.profile.boxing(),
                paras = source.getPanelPara(data.domId),
                children = source.getPanelChildren(data.domId)

            paras.popBtn=paras.popBtn||paras.landBtn;
            
            if(target.addPanel(paras, children, item))
                source.removePanel(data.domId);
            
        }
    }
});
