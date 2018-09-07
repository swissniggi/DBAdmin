
// --------------------------------------------------------------
// dbadmin.MainPanel
// --------------------------------------------------------------
// Klasse zum erstellen eines MainPanels

dbadmin.MainPanel = class dbadmin_MainPanel {
    // --------------------------------------------------------------
    // MEMBERS
    // --------------------------------------------------------------

    /**
     * Erstellt ein MainPanel
     * @param {Object} context          Kontext für Funktionen
     * @param {Object} buttons          Enthält für jeden Button true oder false
     * @param {Object|null}             dataView Instanz der DataView,
                                        NULL = keine DataView vorhanden
     * @returns {mainPanel}
     */
    create(context, buttons, dataView = null) {
        // gewünschte Buttons erstellen
        let buttonsArray = [];

        if (buttons['btnCreate']) {
            // Create-Button definieren
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

            buttonsArray.push(buttonCreate);
        }

        if (buttons['btnDelete']) {
            // Delete-Button definieren
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
                            kijs.gui.MsgBox.confirm('Wirklich löschen?','Willst du die Datenban wirklich löschen?', function(e) {
                                if (e.btn === 'yes') {
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
                            }, context);
                        }
                    },
                    context: context
                }
            });

            buttonsArray.push(buttonDelete);
        }

        if (buttons['btnImport']) {
            // Import-Button definieren
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

            buttonsArray.push(buttonImport);
        }

        if (buttons['btnExport']) {
            // Export-Button definieren
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
                            kijs.gui.MsgBox.confirm('Wirklich exportieren?', 'Willst du die Datenbank wirklich exportieren?', function(e) {
                                if (e.btn === 'yes') {
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
                            }, context);
                        }
                    },
                    context: context
                }
            });

            buttonsArray.push(buttonExport);
        }

        if (buttons['btnDuplicate']) {
            // Duplicate-Button definieren
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

            buttonsArray.push(buttonDuplicate);
        }

        if (buttons['btnRename']) {
            // Rename-Button definieren
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
            
            buttonsArray.push(buttonRename);
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
                            localStorage.clear();
                            this._rpc.do('dbadmin.logout', null, 
                            function() {
                                kijs.gui.CornerTipContainer.show('Info', 'Du wurdest erfolgreich ausgelogt.', 'info');
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

        mainPanel.header.add(buttonsArray);

        return mainPanel;
    }
};