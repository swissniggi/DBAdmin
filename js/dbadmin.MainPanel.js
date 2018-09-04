/* global kijs */

// --------------------------------------------------------------
// kijs.gui.Rpc
// --------------------------------------------------------------
// Erweiterung von kijs.Rpc, der die Meldungsfenster anzeigt
dbadmin.MainPanel = class dbadmin_MainPanel {


    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------
   
    /**
     * Erstellt ein MainPanel
     * @param {Object}                  context Kontext für Funktionen
     * @param {boolean} btnCreate       true = Create-Button erstellen
     * @param {boolean} btnDelete       true = Delete-Button erstellen
     * @param {boolean} btnImport       true = Import-Button erstellen
     * @param {boolean} btnExport       true = Export-Button erstellen
     * @param {boolean} btnDuplicate    true = Duplicate-Button erstellen
     * @param {boolean} btnRename       true = Rename-Button erstellen
     * @param {Object|null}             dataView Instanz der DataView,
                                        NULL = keine DataView vorhanden
     * @returns {mainPanel}
     */
    create(context, btnCreate, btnDelete, btnImport, btnExport, btnDuplicate, btnRename, dataView = null) {
        // gewünschte Buttons erstellen
        let buttons = [];
        
        // Create-Button definieren
        if (btnCreate) {
            let buttonCreate = new kijs.gui.Button({
                name: 'btnCreate',
                iconChar: '&#xf067',
                toolTip: 'leere Datenbank erstellen',
                on:{
                    click: function(){
                        this.showActionWindow('create');
                    },
                    context: context
                }
            });
            
            buttons.push(buttonCreate);
        }
        
        // Delete-Button definieren
        if (btnDelete) {
            let buttonDelete = new kijs.gui.Button({
                name: 'btnDelete',
                iconChar: '&#xf1f8',
                toolTip: 'selektierte Datenbank löschen',
                style:{
                    border: 'none'
                },
                on:{
                    click: function(){
                        if (this._viewport.down('dvDatabases').getSelected() === null) {
                            kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                        } else {
                            this._rpc.do('dbadmin.delete', this._viewport.down('dvDatabases').getSelected().dataRow, 
                            function(response) {
                                if (response.data.success === 'true') {
                                    kijs.gui.CornerTipContainer.show('Info', 'Datenbank erfolgreich gelöscht.', 'info');
                                    this._viewport.down('dvDatabases').load();             
                                } else {
                                    kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                }
                            }, this, false, this._viewport, 'dom', false);
                        }
                    },
                    context: context
                }
            });
            
            buttons.push(buttonDelete);
        }
        
        // Import-Button definieren
        if (btnImport) {
            let buttonImport = new kijs.gui.Button({
                name: 'btnImport',
                iconChar: '&#xf019',
                toolTip: 'Dump in selektierte Datenbank importieren',
                style:{
                    border: 'none'
                },
                on:{
                    click: function(){
                        if (this._viewport.down('dvDatabases').getSelected() === null) {
                            kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                        } else {
                            this.showSelectWindow();
                        }
                    },
                    context: context
                }
            });
            
            buttons.push(buttonImport);
        }
        
        // Export-Button definieren
        if (btnExport) {
            let buttonExport = new kijs.gui.Button({
                name: 'btnExport',
                iconChar: '&#xf093',
                toolTip: 'Dump von selektierter Datenbank erstellen',
                style:{
                    border: 'none'
                },
                on:{
                    click: function(){
                        if (this._viewport.down('dvDatabases').getSelected() === null) {
                            kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                        } else {
                            this._rpc.do('dbadmin.export', this._viewport.down('dvDatabases').getSelected().dataRow, 
                            function(response) {
                                if (response.data.success === 'true') {
                                    kijs.gui.CornerTipContainer.show('Info', 'Datenbank erfolgreich exportiert.', 'info');
                                    this._viewport.down('dvDatabases').load();             
                                } else {
                                    kijs.gui.MsgBox.error('Fehler', response.errorMsg);       
                                }
                            }, this, false, this._viewport, 'dom', false);
                        }
                    },
                    context: context
                }
            });
            
            buttons.push(buttonExport);
        }
        
        // Duplicate-Button definieren
        if (btnDuplicate) {
            let buttonDuplicate = new kijs.gui.Button({
                name: 'btnDuplicate',
                iconChar: '&#xf0c5',
                toolTip: 'selektierte Datenbank dumplizieren',
                style:{                                
                    border: 'none'
                },
                on:{
                    click: function(){
                        if (this._viewport.down('dvDatabases').getSelected() === null) {
                            kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                        } else {
                            this.showActionWindow('duplicate');
                        }
                    },
                    context: context
                }
            });
            
            buttons.push(buttonDuplicate);
        }
        
        // Rename-Button definieren
        if (btnRename) {
            let buttonRename = new kijs.gui.Button({
                name: 'btnRename',
                iconChar: '&#xf044',
                toolTip: 'selektierte Datenbank umbenennen',
                style:{
                    border: 'none'
                },
                on:{
                    click: function(){
                        if (this._viewport.down('dvDatabases').getSelected() === null) {
                            kijs.gui.MsgBox.alert('Achtung','Keine Datenbank ausgewählt!');
                        } else {
                            this.showActionWindow('rename');
                        }
                    },
                    context: context
                }
            });
            
            buttons.push(buttonRename);
        }
        
        // Panel definieren
        let mainPanel = new kijs.gui.Panel({
            name: 'mainPanel',
            caption: 'DBAdmin',
            footerCaption: '&copy; by Nicolas Burgunder',
            iconCls: 'icoWizard16',
            cls: 'kijs-flexrow',
            style:{
                flex: 1
            },
            elements:[
                dataView
            ],                    
            headerBarElements:[
                {
                    // Logout-Button definieren
                    xtype: 'kijs.gui.Button',
                    name: 'btnLogout',
                    iconChar: '&#xf011',
                    on:{
                        click: function(){
                            localStorage.removeItem('ID');
                            this._rpc.do('dbadmin.logout', null, 
                            function() {
                                kijs.gui.CornerTipContainer.show('Info', 'Du wurdest erfolgreich ausgelogt', 'info');
                                this.showLoginWindow();
                                // DataView leeren
                                this._viewport.down('dvDatabases').load(null);
                            }, this, false, this._viewport, 'dom', false);
                        },
                        context: context
                    }
                }
            ]
        });
        
        mainPanel.header.add(buttons);
        
        return mainPanel;
    }
};
