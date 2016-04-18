// The default code is a com class (inherited from xui.Com)
Class('App.order', 'xui.Com',{
    Instance:{
        // To initialize internal components (mostly UI controls)
        // *** If you're not a skilled, dont modify this function manually ***
        iniComponents : function(){
            // [[Code created by CrossUI RAD Tools
            var host=this, children=[], append=function(child){children.push(child.get(0));};
            
            append((new xui.DataBinder())
            .setHost(host,"databinder")
            .setName("databinder")
            .afterUpdateDataFromUI("_databinder_afterupdatedatafromui")
            );
            
            append((new xui.UI.Dialog())
            .setHost(host,"mainDlg")
            .setLeft(25)
            .setTop(19)
            .setWidth(690)
            .setHeight(457)
            .setResizer(false)
            .setOverflow("hidden")
            .setCaption("Order")
            .setImage("{/}img/app.png")
            .setImagePos("left top")
            .setMinBtn(false)
            .setMaxBtn(false)
            .onHotKeydown("_maindlg_onhotkeydown")
            .beforeClose("_maindlg_beforeclose")
            );
            
            host.mainDlg.append((new xui.UI.Block())
            .setHost(host,"ctl_block159")
            .setLeft(5)
            .setTop(0)
            .setWidth(675)
            .setHeight(390)
            .setOverflow("visible")
            .setBorderType("inset")
            );
            
            host.ctl_block159.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel299")
            .setLeft(10)
            .setTop(14)
            .setCaption("Customer")
            );
            
            host.ctl_block159.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel301")
            .setLeft(10)
            .setTop(70)
            .setCaption("Comments")
            );
            
            host.ctl_block159.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel47")
            .setLeft(280)
            .setTop(14)
            .setCaption("Order Date")
            );
            
            host.ctl_block159.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel48")
            .setLeft(530)
            .setTop(14)
            .setCaption("Order ID")
            );
            
            host.ctl_block159.append((new xui.UI.Input())
            .setHost(host,"ctl_input41")
            .setDataBinder("databinder")
            .setDataField("OrderID")
            .setReadonly(true)
            .setLeft(600)
            .setTop(10)
            .setWidth(50)
            );
            
            host.ctl_block159.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel49")
            .setLeft(280)
            .setTop(44)
            .setCaption("PO Number")
            );
            
            host.ctl_block159.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel50")
            .setLeft(10)
            .setTop(44)
            .setCaption("Employee")
            );
            
            host.ctl_block159.append((new xui.UI.Pane())
            .setHost(host,"ctl_pane21")
            .setLeft(0)
            .setTop(260)
            .setWidth(660)
            .setHeight(130)
            );
            
            host.ctl_pane21.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel303")
            .setLeft(420)
            .setTop(16)
            .setCaption("Order SubTotal")
            );
            
            host.ctl_pane21.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel304")
            .setLeft(450)
            .setTop(46)
            .setCaption("Taxes")
            );
            
            host.ctl_pane21.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel305")
            .setLeft(420)
            .setTop(76)
            .setCaption("Shipping & Handling")
            );
            
            host.ctl_pane21.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel300")
            .setLeft(10)
            .setTop(74)
            .setCaption("Shipping Date")
            );
            
            host.ctl_pane21.append((new xui.UI.ComboInput())
            .setHost(host,"iSubTotal")
            .setReadonly(true)
            .setLeft(560)
            .setTop(12)
            .setWidth(90)
            .setType("currency")
            .setValue(0)
            );
            
            host.ctl_pane21.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel302")
            .setLeft(420)
            .setTop(104)
            .setCaption("Order Total")
            );
            
            host.ctl_pane21.append((new xui.UI.ComboInput())
            .setHost(host,"iTotal")
            .setReadonly(true)
            .setLeft(560)
            .setTop(100)
            .setWidth(90)
            .setType("currency")
            .setValue(0)
            );
            
            host.ctl_pane21.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel58")
            .setLeft(10)
            .setTop(44)
            .setCaption("Shipping Method")
            );
            
            host.ctl_pane21.append((new xui.UI.SLabel())
            .setHost(host,"ctl_slabel51")
            .setLeft(10)
            .setTop(14)
            .setCaption("Payment Method")
            );
            
            host.ctl_pane21.append((new xui.UI.ComboInput())
            .setHost(host,"ctl_comboinput17")
            .setDataBinder("databinder")
            .setDataField("PaymentMethodID")
            .setLeft(120)
            .setTop(10)
            .setWidth(110)
            .setTabindex(7)
            .setType("listbox")
            .setDropListWidth(150)
            .setInputReadonly(true)
            .beforePopShow("_ctl_comboinput17_beforepopshow")
            );
            
            host.ctl_pane21.append((new xui.UI.ComboInput())
            .setHost(host,"ctl_comboinput56")
            .setDataBinder("databinder")
            .setDataField("ShippingMethodID")
            .setLeft(120)
            .setTop(40)
            .setWidth(110)
            .setTabindex(8)
            .setType("listbox")
            .setDropListWidth(150)
            .setInputReadonly(true)
            .beforePopShow("_ctl_comboinput56_beforepopshow")
            );
            
            host.ctl_pane21.append((new xui.UI.ComboInput())
            .setHost(host,"ctl_comboinput23")
            .setDataBinder("databinder")
            .setDataField("ShipDate")
            .setLeft(120)
            .setTop(70)
            .setWidth(110)
            .setTabindex(9)
            .setType("date")
            );
            
            host.ctl_pane21.append((new xui.UI.SCheckBox())
            .setHost(host,"ctl_scheckbox3")
            .setDataBinder("databinder")
            .setDataField("PaymentReceived")
            .setLeft(250)
            .setTop(10)
            .setTabindex(10)
            .setCaption("Payment Received?")
            );
            
            host.ctl_pane21.append((new xui.UI.ComboInput())
            .setHost(host,"iTax")
            .setDataBinder("databinder")
            .setDataField("Taxes")
            .setLeft(560)
            .setTop(40)
            .setWidth(90)
            .setTabindex(11)
            .setType("currency")
            .afterUIValueSet("_itax_afteruivalueset")
            );
            
            host.ctl_pane21.append((new xui.UI.ComboInput())
            .setHost(host,"iExtra")
            .setDataBinder("databinder")
            .setDataField("FreightCharge")
            .setLeft(560)
            .setTop(70)
            .setWidth(90)
            .setTabindex(12)
            .setType("currency")
            .afterUIValueSet("_itax_afteruivalueset")
            );
            
            host.ctl_block159.append((new xui.UI.Block())
            .setHost(host,"ctl_block22")
            .setLeft(10)
            .setTop(120)
            .setWidth(640)
            .setHeight(140)
            .setBorderType("inset")
            );
            
            host.ctl_block22.append((new xui.UI.Block())
            .setHost(host,"ctl_block23")
            .setDock("top")
            .setHeight(22)
            .setHtml("<b>Order Detail</b><br>")
            .setBorderType("none")
            .setCustomStyle({"KEY":{"text-align":"center", "$gradients":""}})
            );
            
            host.ctl_block22.append((new xui.UI.TreeGrid())
            .setHost(host,"grid")
            .setShowDirtyMark(false)
            .setTabindex(6)
            .setRowNumbered(true)
            .setEditable(true)
            .setRowHandlerWidth(26)
            .setColSortable(false)
            .setHeader([{"id":"ProductID", "width":225, "type":"cmdbox", "editorProperties":{"commandBtn":"delete"}, "caption":"Product"}, {"id":"Quantity", "width":60, "type":"spin", "editorProperties":{"increment":1, "min":1, "max":99999}, "caption":"Quantity"}, {"id":"UnitPrice", "width":80, "type":"currency", "readonly":true, "caption":"Unit Price"}, {"id":"Discount", "width":80, "type":"spin", "precision":2, "editorProperties":{"increment":0.05, "min":0, "max":100}, "caption":"Discount", "numberTpl":"* %"}, {"id":"ExtendedPrice", "width":92, "type":"currency", "readonly":true, "caption":"Extended Price"}])
            .setTreeMode(false)
            .setHotRowMode("show")
            .onInitHotRow("_grid_oninithotrow")
            .beforeHotRowAdded("_grid_beforehotrowadded")
            .afterHotRowAdded("_grid_afterhotrowadded")
            .afterCellUpdated("_grid_aftercellupdated")
            .beforeComboPop("_grid_beforecombopop")
            .onCommand("_grid_oncommand")
            );
            
            host.ctl_block159.append((new xui.UI.ComboInput())
            .setHost(host,"ctl_comboinput14")
            .setDataBinder("databinder")
            .setDataField("CustomerID")
            .setLeft(110)
            .setTop(10)
            .setWidth(140)
            .setTipsErr("Required")
            .setValueFormat("[^.*]")
            .setType("cmdbox")
            .beforeComboPop("_ctl_comboinput14_beforecombopop")
            );
            
            host.ctl_block159.append((new xui.UI.ComboInput())
            .setHost(host,"ctl_comboinput15")
            .setDataBinder("databinder")
            .setDataField("OrderDate")
            .setLeft(370)
            .setTop(10)
            .setWidth(110)
            .setTabindex(2)
            .setTipsErr("Required")
            .setValueFormat("[^.*]")
            .setType("date")
            );
            
            host.ctl_block159.append((new xui.UI.ComboInput())
            .setHost(host,"ctl_comboinput16")
            .setDataBinder("databinder")
            .setDataField("EmployeeID")
            .setLeft(110)
            .setTop(40)
            .setWidth(140)
            .setTabindex(3)
            .setTipsErr("Required")
            .setValueFormat("[^.*]")
            .setType("cmdbox")
            .beforeComboPop("_ctl_comboinput16_beforecombopop")
            );
            
            host.ctl_block159.append((new xui.UI.Input())
            .setHost(host,"ctl_input42")
            .setDataBinder("databinder")
            .setDataField("PurchaseOrderNumber")
            .setLeft(370)
            .setTop(40)
            .setWidth(110)
            .setTabindex(4)
            .setTipsErr("Required")
            .setValueFormat("[^.*]")
            );
            
            host.ctl_block159.append((new xui.UI.Input())
            .setHost(host,"ctl_input7")
            .setDataBinder("databinder")
            .setDataField("Comment")
            .setLeft(110)
            .setTop(70)
            .setWidth(540)
            .setHeight(40)
            .setTabindex(5)
            .setMultiLines(true)
            );
            
            host.mainDlg.append((new xui.UI.SButton())
            .setHost(host,"btnSave")
            .setLeft(170)
            .setTop(400)
            .setWidth(70)
            .setTabindex(13)
            .setCaption("Save")
            .onClick("_ctl_sbutton14_onclick")
            );
            
            host.mainDlg.append((new xui.UI.SButton())
            .setHost(host,"btnClose")
            .setLeft(450)
            .setTop(400)
            .setWidth(70)
            .setTabindex(14)
            .setCaption("Close")
            .onClick("_ctl_sbutton486_onclick")
            );
            
            return children;
            // ]]Code created by CrossUI RAD Tools
        },
        customAppend : function(parent, subId, left, top){
            this.mainDlg.showModal(parent, left, top);
            return false;
        },
        events:{"onRender":"_com_onrender"},
        _com_onrender:function (com, threadid){
            var recordId=this.properties.recordId;
            // clear all UI
            // for inputs
            this.databinder.setData().updateDataToUI();
            // for grid
            this.grid.removeAllRows();
            // open
            if(_.isSet(recordId)){
                this.updateUIfromService(recordId);
            }
            this.setDirty(false);
        },
        setDirty:function(dirty){
            this.__dirty=dirty;
        },
        isGridDirty:function(){
            return !!this.__dirty;
        },
        _ctl_sbutton14_onclick:function (profile,e,src,value){
            this.saveUI();
        },
        saveUI:function(callback){
            var ns=this, db=ns.databinder;
            
            // need save?
            if(db.isDirtied() || ns.isGridDirty()){
                
                // check UI valid
                if(!db.checkValid()){
                    xui.message("There are some invalid fileds!");
                    return;
                }
                var header=ns.grid.getHeader("min"),
                    gridv=ns.grid.getRows("min"),
                    details=[],detail;
                if(gridv.length<=0){
                    xui.message("There are no prduct detail in the order!");
                    return;
                }
                var recordId=this.properties.recordId,
                    hash=db.getDirtied();
                
                // remove the last one
                header.pop();
                _.arr.each(gridv,function(row){
                    details.push(detail={});
                    _.arr.each(header,function(col,i){
                        detail[col]=row[i];
                    });
                });
                // adjust data
                _.each(hash,function(o,i){
                    if(_.isDate(o)){
                        hash[i]=xui.Date.format(o, "yyyy-mm-dd hh:nn:ss");
                    }
                });
                
                if(recordId){
                    //update
                    rqsD={
                        OrderID:recordId
                    };
                    if(db.isDirtied() && hash && !_.isEmpty(hash))
                    rqsD.fields=hash;
                    if(ns.isGridDirty() && details && details.length>0)
                        rqsD.details=details;
                    
                    CONF.callService("orders","set",rqsD,function(rsp){
                        if(rsp.data==1){
                            xui.message("Data saved successful!");
                        }else{
                            xui.message(rsp);
                        }
                        
                        // update grid value
                        ns.fireEvent("afterUpdated", [recordId, db.getDirtied(true)], ns);
                        db.updateValue();
                        ns.setDirty(false);
                        if(callback)callback(ns.mainDlg);
                    },function(){
                        ns.mainDlg.busy("Saving Data ...");
                    },function(){
                        if(ns.mainDlg)
                            ns.mainDlg.free();
                    });        
                }else{
                    //create
                    CONF.callService("orders","create",{
                        fields:hash,
                        details:details
                    },function(rsp){
                        if(rsp.data){
                            xui.message("Data saved successful!");
                        }else{
                            xui.message(rsp);
                        }
                        // rsp.data
                        ns.mainDlg.free();
                        db.updateValue();
                        
                        // add to grid 
                        ns.fireEvent("afterCreated", [rsp.data], ns);
                        ns.setDirty(false);
                        if(callback)callback(ns.mainDlg);
                        
                    },function(){
                        ns.mainDlg.busy("Creating Data ...");
                    },function(){
                        if(ns.mainDlg)
                            ns.mainDlg.free();
                    });        
                }
                
                
            }else{
                xui.message("No changed.");
            }
        },
        _cacu:function(quantity,uprice,discount){
            return  parseFloat(quantity)*parseFloat(uprice)*(100-parseFloat(discount))/100;
        },
        _recacuRow:function(row){
            var cells=row.cells;
            var v = this._cacu(cells[1].value ,cells[2].value ,cells[3].value );
            this.grid.updateCell(cells[4].id, {value:v}, false, false);
            
            this._recatuAll();
        },
        _recatuAll:function(){
            var total=0;
            _.arr.each(this.grid.getRows(),function(row){
                total+=parseFloat(row.cells[4].value)||0;
            });
            this.iSubTotal.setValue(total,true);
            total += parseFloat((this.iTax.getUIValue())||0) + (parseFloat(this.iExtra.getUIValue())||0) ;
            
            this.iTotal.setValue(total,true);
        },
        updateUIfromService:function(recordId){
            var ns=this,data=ns.databinder.getData();
            // In this class, we use control's get/setValue directly
            CONF.callService("orders","get",{OrderID:recordId},function(rsp){
                var row=rsp.data.rows[0], map=rsp.data.caps,bmap=rsp.data.bools;
                _.arr.each(rsp.data.cols,function(col,i){
                    data[col]=row[i];
                    if(map && map[col]){
                        data[col]={
                            value:row[i],
                            caption:row[_.arr.indexOf(rsp.data.cols, map[col])]
                        };
                    }
                    if(bmap && bmap[col]){
                        data[col]=parseInt(data[col],10);
                    }
                });
                
                ns.databinder.setData(data).updateDataToUI();
                
                var drows=[],drow;
                _.arr.each(rsp.data.details,function(o){
                    drow={cells:[
                        {
                            value:o[0],
                            caption:o[1]
                        },o[2],o[3],parseFloat(o[4])*100,ns._cacu(o[2],o[3],parseFloat(o[4])*100)
                    ]};
                    drows.push(drow);
                });
                ns.grid.setRows(drows);
                _.asyRun(function(){
                    ns.btnClose.activate();
                });
                
                ns._recatuAll();
            },function(){
                ns.mainDlg.busy("Getting Data ...");
            },function(){
                ns.mainDlg.free();
            });
        },
        // Use the default xui.UI.List combo pop wnd
        _ctl_comboinput17_beforepopshow:function (profile, popCtl){
            var ns = this, list = popCtl.boxing();
            
            // In this class, we use control's get/setValue directly
            CONF.callService("paymentmethods","getList",{},function(rsp){
                var items=[];
                _.each(rsp.data,function(record){
                    items.push({
                        id:record[0],
                        caption:record[1]
                    });
                });
                if(!list.isDestroyed()){
                    profile.boxing().setItems(items);
                    list.setItems(items).setValue(null,true);
                }
            },function(){
                list.setItems(["Getting list ..."],true);
            },function(){
            });
        },
        // Use the default xui.UI.List combo pop wnd
        _ctl_comboinput56_beforepopshow:function (profile, popCtl){
            var ns = this, list = popCtl.boxing();
            
            // In this class, we use control's get/setValue directly
            CONF.callService("shippingmethods","getList",{},function(rsp){
                var items=[];
                _.each(rsp.data,function(record){
                    items.push({
                        id:record[0],
                        caption:record[1]
                    });
                });
                if(!list.isDestroyed()){
                    profile.boxing().setItems(items);
                    list.setItems(items).setValue(null,true);
                }
            },function(){
                list.setItems(["Getting list ..."],true);
            },function(){
            });
        },
        // Use custom combo pop wnd
        _ctl_comboinput14_beforecombopop:function (profile, pos,e ,src){
            var ns=this,ctrl=profile.boxing();
            
            // build a tree grid for selection
            xui.ComFactory.newCom('App.PopGridForSel', function(){
                this.setProperties({
                    key:"customers",
                    pos:ctrl.getRoot()
                });
                this.setEvents({
                    onCancel:function(){
                        ctrl.activate();
                    },
                    onSelect:function(id,col,row){
                        var cap=row[_.arr.indexOf(col,"CompanyName")];
                        ctrl.setUIValue(id);
                        ctrl.setCaption(cap).setTips(cap);
                        ctrl.activate();
                    }
                });
                this.show(); 
            });
            return false;
        },
        _ctl_comboinput16_beforecombopop:function (profile, pos,e ,src){
            var ns=this,ctrl=profile.boxing();
            
            // build a tree grid for selection
            xui.ComFactory.newCom('App.PopGridForSel', function(){
                this.setProperties({
                    key:"employees",
                    pos:ctrl.getRoot()
                });
                this.setEvents({
                    onCancel:function(){
                        ctrl.activate();
                    },
                    onSelect:function(id,col,row){
                        var cap=row[_.arr.indexOf(col,"EmployeeName")];
                        ctrl.setUIValue(id);
                        ctrl.setCaption(cap).setTips(cap);
                        ctrl.activate();
                    }
                });
                this.show(); 
            });
            return false;
        },
        _grid_beforecombopop:function (profile, cell, proEditor, pos, e, src){
            var ns = this, uictrl = profile.boxing(),
                rowId=uictrl.getRowbyCell(cell).id;
            // build a tree grid for selection
            xui.ComFactory.newCom('App.PopGridForSel', function(){
                this.setProperties({
                    key:"products",
                    pos:proEditor.getRoot()
                });
                this.setEvents({
                    onCancel:function(){
                        proEditor.boxing().activate();
                    },
                    onSelect:function(id,col,row){
                        
                        var cap=row[_.arr.indexOf(col,"ProductName")];
                        
                        uictrl.updateCell(cell.id, {
                            value:id,
                            caption:cap
                        }, false, false);
                        
                        uictrl.updateCellByRowCol(rowId, "UnitPrice", {
                            value:row[2]
                        }, false, true);
                        
                        proEditor.boxing().setCaption(cap ,true);
                        proEditor.boxing().activate();
                    }
                });
                this.show(); 
            });
            return false;
        },
        _grid_beforehotrowadded:function (profile, row, leaveGrid){
            return row.cells[0].value !== "" && parseInt(row.cells[1].value,10)>=1;
        },
        _grid_afterhotrowadded:function (profile, row){
            this.setDirty(true);
        },
        _grid_oninithotrow:function (profile){
            return {cells:["",1,0,0,0]};
        },
        _grid_aftercellupdated:function (profile, cell, options, isHotRow){
            var ns = this, uictrl = profile.boxing();
            
            var col=uictrl.getHeaderByCell(cell);
            if(col.id=="Quantity"||col.id=="UnitPrice"||col.id=="Discount"){
                ns._recacuRow(uictrl.getRowbyCell(cell));
            }
            if(!isHotRow){
                ns.setDirty(true);
            }
        },
        _itax_afteruivalueset:function (profile, oldValue, newValue){
            var ns = this;
            ns.iTotal.setValue((parseFloat(ns.iSubTotal.getUIValue())||0) + (parseFloat(ns.iTax.getUIValue())||0) + (parseFloat(ns.iExtra.getUIValue())||0), true);
        },
        _grid_oncommand:function (profile, cell, proEditor, src){
            var grid=this.grid,
                row=grid.getRowbyCell(cell),
                id=row.id;
            
            if(row){
                grid.removeRows(id);
                grid.offEditor();
                this._recatuAll();
                
                if(xui.UI.TreeGrid.isHotRow(id))
                return;
                
                this.setDirty(true);
            }
        },
        _databinder_afterupdatedatafromui:function (profile, dataFromUI){
            // adjust data
            _.each(dataFromUI,function(o,i){
                if(_.isDate(o)){
                    dataFromUI[i]=xui.Date.format(o, "yyyy-mm-dd hh:nn:ss", firstDayOfWeek);
                }
            });
        },
        _ctl_sbutton486_onclick:function (profile, e, src, value){
            this.mainDlg.close(true);
        },
        _maindlg_beforeclose:function (profile){
            var ns=this, db=ns.databinder;
            // need save?
            if(db.isDirtied() || ns.isGridDirty()){
                xui.confirm("Confirm", "Some fileds have been changed, do you want save them before close the form?", function(){
                    ns.saveUI(function(dlg){
                        ns.mainDlg.close(false);
                    });
                }, function(){
                    ns.mainDlg.close(false);
                });
                return false;
            }else{
                return true;
            }
        },
        _maindlg_onhotkeydown:function (profile, keyboard, e, src){
            if(keyboard.key=="esc"){
                this.mainDlg.close(true);
            }
        }
    }
});