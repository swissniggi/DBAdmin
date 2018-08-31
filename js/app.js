// --------------------------------------------------------------
// Facade
// --------------------------------------------------------------
kit = {};
kit.App = class kit_App {
        

    // --------------------------------------------------------------
    // CONSTRUCTOR
    // --------------------------------------------------------------
    constructor(config={}) {

        // RPC-Instanz
        var rpcConfig = {};
        if (config.ajaxUrl) {
            rpcConfig.url = config.ajaxUrl;
        }
        this._rpc = new kijs.gui.Rpc(rpcConfig);
        
        this.viewport = null;
    }


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
    run() {
        let _this = this;
        
        // ViewPort erstellen
        this.viewport = new kijs.gui.ViewPort({
            cls: 'kijs-flexcolumn',            
            elements: [
                {
                    xtype: 'kijs.gui.Panel',
                    name: 'mainPanel',
                    caption: 'DBAdmin',
                    footerCaption: '&copy; by Nicolas Burgunder',
                    iconCls: 'icoWizard16',
                    cls: 'kijs-flexrow',
                    elements:[
                        {
                            xtype: 'kijs.gui.DataView',
                            name: 'databases',
                            selectType: 'single',
                            rpc: this._rpc, 
                            waitMaskTargetDomProperty: 'innerDom',
                            autoLoad: true,
                            facadeFnLoad: 'dbadmin.loadDbs',                        
                            innerStyle: {
                                padding: '10px',
                                overflowY: 'auto'
                            }
                        }
                    ],
                    headerElements:[
                        {
                            xtype: 'kijs.gui.Button',
                            name: 'btnCreate',
                            html: '<img src="img/create.PNG" style="width: 25px" alt="DB erstellen"></img>',
                            on:{
                                click: function(){
                                    _this.showActionWindow('create');
                                }
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnLogout',
                            html: '<img src="img/logout.PNG" style="width: 25px" alt"Ausloggen"></img>',
                            on:{
                                click: function(){
                                    sessionStorage.removeItem('ID');
                                    _this._rpc.do('dbadmin.logout', null, 
                                    function(response) {
                                        if (response.data.success === 'true') {
                                            kijs.gui.CornerTipContainer.show('Info', 'Du wurdest erfolgreich ausgelogt', 'info');
                                            this.showLoginWindow();
                                            // DataView leeren
                                            this.viewport.elements[0].elements[0].load(null);
                                            
                                        } else {
                                            kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                        }
                                    }, _this, false, this.parent, 'dom', false);
                                }
                            }
                        }
                    ],
                    footerElements:[
                        {
                            xtype: 'kijs.gui.Button',
                            name: 'btnDelete',
                            html: '<img src="img/trash.PNG" style="width: 25px" alt="DB löschen"></img>',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    _this._rpc.do('dbadmin.delete', this.parent.parent.down('databases').getSelected().dataRow, 
                                    function(response) {
                                        if (response.data.success === 'true') {
                                            kijs.gui.CornerTipContainer.show('Info', 'Datenbank erfolgreich gelöscht', 'info');
                                            this.viewport.elements[0].elements[0].load();             
                                        } else {
                                            kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                        }
                                    }, _this, false, this.parent, 'dom', false);
                                }
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnImport',
                            html: '<img src="img/import.PNG" style="width: 25px" alt="Dump importieren"></img>',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this.parent.parent.down('databases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        _this.showSelectWindow();
                                    }
                                }
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnExport',
                            html: '<img src="img/export.PNG" style="width: 25px" alt="Dump exportieren"></img>',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this.parent.parent.down('databases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        _this._rpc.do('dbadmin.export', this.parent.parent.down('databases').getSelected().dataRow, 
                                        function(response) {
                                            if (response.data.success === 'true') {
                                                kijs.gui.CornerTipContainer.show('Info', 'Datenbank erfolgreich exportiert', 'info');
                                                this.viewport.elements[0].elements[0].load();             
                                            } else {
                                                kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                            }
                                        }, _this, false, this.parent, 'dom', false);
                                    }
                                }
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnDuplicate',
                            html: '<img src="img/duplicate.PNG" style="width: 25px" alt="DB duplizieren"></img>',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this.parent.parent.down('databases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        _this.showActionWindow('duplicate');
                                    }
                                }
                            }
                        },{
                            xtype: 'kijs.gui.Button',
                            name: 'btnRename',
                            html: '<img src="img/edit.PNG" style="width: 25px" alt="DB umbenennen"></img>',
                            style:{
                                border: 'none'
                            },
                            on:{
                                click: function(){
                                    if (this.parent.parent.down('databases').getSelected() === null) {
                                        kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                                    } else {
                                        _this.showActionWindow('rename');
                                    }
                                }
                            }
                        }                     
                    ]
                }
            ]
        });
        this.viewport.render();
        if (!sessionStorage.getItem('ID')) {
            this.showLoginWindow();
        } else {
            this.viewport.elements[0].elements[0].load();
        }
    }
    
    
    showActionWindow(action) {
        let _this = this;
        let caption = '';
        
        switch(action) {
            case 'create': caption = 'neue Datenbank erstellen'; break;
            case 'duplicate': caption = 'Datenbank duplizieren'; break;
            case 'rename': caption = 'Datenbank umbenennen'; break;
        }
        // Create-Window erstellen
        let createWindow = new dbadmin_Window({
            caption: caption,
            closable: true,
            modal: true,
            width: 400,
            defaults:{
                width: 380,
                height: 25,
                style:{
                    margin: '10px 5px 10px 5px'
                }
            },
            elements:[
                {
                    xtype: 'kijs.gui.field.Text',
                    labelWidth: 160,
                    required: true,
                    name: 'dbname',
                    label: 'neuer Datenbankname'
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'btnLogin',
                    width: 100,
                    height: 30,
                    caption: 'OK',
                    on:{
                        click: function(){
                            let newDbname = this.parent.down('dbname');
                            let oldDbname = action === 'create' ? null : _this.viewport.elements[0].elements[0].getSelected().dataRow['Datenbank'];

                            if (newDbname.validate()) {
                                _this._rpc.do('dbadmin.'+action, {
                                    newDbname : newDbname.value,
                                    oldDbname : oldDbname
                                }, 
                                function(response) {
                                    if (response.data.success === 'true') {
                                        createWindow.destruct();
                                        kijs.gui.CornerTipContainer.show('Info', '"' + caption + '" war erfolgreich', 'info');
                                        this.viewport.elements[0].elements[0].load();
                                    } else {
                                        kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                    }
                                }, _this, false, this.parent, 'dom', false);
                            } else {
                                kijs.gui.MsgBox.alert('Achtung', 'Bitte Datenbankname angeben!');
                            } 
                        }
                    }
                }
            ]
        });
        createWindow.show();
    }
    
    
    showLoginWindow() {
        let _this = this;
        // Window erstellen
        let loginWindow = new dbadmin_Window({
            caption: 'Login',
            modal: true,
            width: 300,
            defaults:{
                width: 280,
                height: 25,
                style:{
                    margin: '10px'
                }
            },
            elements:[
                {
                    xtype: 'kijs.gui.field.Text',
                    labelWidth: 80,
                    required: true,
                    name: 'username',
                    label: 'Benutzer'
                },{
                    xtype: 'kijs.gui.field.Text',
                    labelWidth: 80,
                    required: true,
                    name: 'password',
                    label: 'Passwort'
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'btnLogin',
                    width: 100,
                    height: 30,
                    caption: 'Login',
                    on:{
                        click: function(){
                            let username = this.parent.down('username');
                            let password = this.parent.down('password');
                            
                            if (username.validate() && password.validate()) {
                                _this._rpc.do('dbadmin.login', {username : username.value, password : password.value}, 
                                function(response) {
                                    if (response.data.success === 'true') {
                                        loginWindow.destruct();
                                        this.setSessionId();
                                        this.viewport.elements[0].elements[0].load();
                                    } else {
                                        kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                    }
                                }, _this, false, this.parent, 'dom', false);
                            } else {
                                kijs.gui.MsgBox.alert('Achtung', 'Bitte alle Felder ausfüllen!');
                            }
                        }
                    }
                }
            ]
        });
        loginWindow.show();
    }
    
    
    showSelectWindow() {
        let _this = this;        
        
        // Window erstellen
        let selectWindow = new dbadmin_Window({
            caption: 'Dumps',
            modal: true,
            width: 300,
            closable: true,
            defaults:{
                width: 280,
                height: 25,
                style:{
                    margin: '10px'
                }
            },
            elements:[
                {
                    xtype: 'kijs.gui.DataView',
                    name: 'dumps',
                    selectType: 'single',
                    rpc: this._rpc, 
                    waitMaskTargetDomProperty: 'innerDom',
                    autoLoad: true,
                    height: 400,                    
                    facadeFnLoad: 'dbadmin.loadDumps',
                    innerStyle: {
                        padding: '10px',
                        overflowY: 'scroll'
                    }
                },{
                    xtype: 'kijs.gui.Button',
                    name: 'btnImport',
                    width: 100,
                    height: 30,
                    caption: 'Importieren',
                    on:{
                        click: function(){
                            kijs.gui.MsgBox.confirm('Bestätigen', 'Soll der Dump nach dem Import gelöscht werden?', function(e){
                                _this._rpc.do('dbadmin.import', {
                                    'database' : _this.viewport.elements[0].elements[0].getSelected().dataRow['Datenbank'],
                                    'dump' : this.parent.down('dumps').getSelected().dataRow['Dumpname'],
                                    'delete' : e.btn === 'yes' ? true : false
                                }, 
                                function(response) {
                                    if (response.data.success === 'true') {
                                        kijs.gui.CornerTipContainer.show('Info', 'Dump erfolgreich importiert', 'info');
                                        this.viewport.elements[0].elements[0].load();
                                        selectWindow.destruct();
                                    } else {
                                        kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                    }
                                }, _this, false, this.parent, 'dom', false);
                            }, this);
                        }
                    }
                }
            ]
        });
        selectWindow.show();
    }
    
    
    setSessionId() {
        function s4() {
            return Math.floor((1 + Math.random()) * 0x10000)
                .toString(16)
                .substring(1);
        }
        let Id = s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
        sessionStorage.setItem('ID', Id); 
    }
};