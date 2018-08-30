<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="stylesheet" type="text/css" href="../kijs/css/kijs.gui.php">
        <link rel="stylesheet" type="text/css" href="css/DBAdmin_style.css">

        <!-- Achtung: Reihenfolge beachten -->     
        
        <script type="text/javascript" src="../kijs/js/kijs.js"></script>
        <script type="text/javascript" src="../kijs/js/kijs.String.js"></script>
        <script type="text/javascript" src="../kijs/js/kijs.Array.js"></script>
        <script type="text/javascript" src="../kijs/js/kijs.Object.js"></script>
        <script type="text/javascript" src="../kijs/js/kijs.Date.js"></script>
        <script type="text/javascript" src="../kijs/js/kijs.Dom.js"></script>
        
        <script type="text/javascript" src="../kijs/js/kijs.Observable.js"></script>
        
        <script type="text/javascript" src="../kijs/js/kijs.Ajax.js"></script>
        <script type="text/javascript" src="../kijs/js/kijs.Rpc.js"></script>
        
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Dom.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.ToolTip.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Element.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Container.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.ViewPort.js"></script>
        
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Icon.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Button.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.ButtonGroup.js"></script>
        
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.HeaderBar.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.FooterBar.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Resizer.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Splitter.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Panel.js"></script>

        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Mask.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.LayerManager.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Window.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.MsgBox.js"></script>
        
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.CornerTipContainer.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.DataView.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.DataViewElement.js"></script>

        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.Rpc.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/kijs.gui.FormPanel.js"></script>
        
        <script type="text/javascript" src="../kijs/js/gui/field/kijs.gui.field.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/field/kijs.gui.field.Field.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/field/kijs.gui.field.Text.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/field/kijs.gui.field.Memo.js"></script>
        <script type="text/javascript" src="../kijs/js/gui/field/kijs.gui.field.Password.js"></script>
        
        <script type="text/javascript" src="js/app.js"></script>
        <script type="text/javascript" src="js/login.Window.js"></script>
    </head>
    <body>
        <script type="text/javascript">
            kijs.isReady(function(){
                var app = new kit.App({
                    ajaxUrl: 'php/ajax.php'
                });
                app.run();
            });
        </script>               
    </body>
</html>
