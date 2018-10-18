# DBAdmin Version 2.0

(based on https://github.com/swissniggi/DBAdminOld)

!! requires the new kijs-Framework // mail me for more details !!

==> additional folder 'dumps' must be created in root directory on install

Features yet to come:

- ~actual password field in login window~<br />~solved with class _dbadmin.PasswordField_ extending _kijs.gui.field.Text_.<br />Class __kijs.gui.field.Password__ will be used as soon as it works.~<br />
__kijs.gui.field.Password__ works now
- ~activation of click event on button on enter~<br />solved

#Project from trial IPA, June 2018

#Refined in August and September 2018

__2018/09/14__<br />
Changed the name of the repository from 'DBAdminJS' to 'DBAdmin'.</br>
The base repository was changed from 'DBAdminPHP' to 'DBAdminOld' accordingly.

__Important Information__<br />
All mysql functions are executed with the logged-in user.<br />
Therefore it is recommended that standard users are given rights as follows<br />
- USAGE ON \*.\*<br />
- ALL PRIVILEDGES ON databases that begin with their username

All users need select rights on database 'dbadmin'
