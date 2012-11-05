File Modify (plugin for Omeka)
====================================


Summary
-------

This plugin allows to modify (convert, compress, watermark, rename or any other
command) uploaded file before saving it in archive folder and before creating
metadata in Omeka database.

Renaming requires Archive Repertory plugin and the "keep original filename"
parameter set. Files imported via the user interface (add content) are not
renamed.

For more information on Omeka, see [Omeka][1].


Installation
------------

Uncompress files and rename plugin folder "FileModify".

Then install it like any other Omeka plugin and follow the config instructions.

Finally, you should adapt the files `libraries/file_modify_command.php` and
`libraries/file_modify_rename.php` to your specific needs.

You can allow user to set a command to execute in the plugin.ini: simply change
`file_modify_allow_command` to "TRUE". **Warning**: it can be a security gap if
you don't trust the admin of the site. That's why the use of this parameter is
disabled by default. To avoid this risk, you can hard code the command in
`libraries/file_modify_command.php`.

If you use the renaming feature, you need to install the
[Archive Repertory plugin][2] and to enable it.


Warning
-------

Use it at your own risk.

It's always recommended to backup your database so you can roll back if needed.


Troubleshooting
---------------

See online issues on [GitHub][3].


License
-------

This plugin is published with a double licence:

### [CeCILL][4]

In consideration of access to the source code and the rights to copy,
modify and redistribute granted by the license, users are provided only
with a limited warranty and the software's author, the holder of the
economic rights, and the successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying
and/or developing or reproducing the software by the user are brought to
the user's attention, given its Free Software status, which may make it
complicated to use, with the result that its use is reserved for
developers and experienced professionals having in-depth computer
knowledge. Users are therefore encouraged to load and test the
suitability of the software as regards their requirements in conditions
enabling the security of their systems and/or data to be ensured and,
more generally, to use and operate it in the same conditions of
security. This Agreement may be freely reproduced and published,
provided it is not altered, and that no provisions are either added or
removed herefrom.

### [GNU/GPL][5]

This program is free software; you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation; either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT
ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
details.

You should have received a copy of the GNU General Public License along with
this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


Contact
-------

Current maintainers:

* Daniel Berthereau (see [Daniel_KM][6])

First version of this plugin has been built for École des Ponts ParisTech
(see [ENPC][7]).


Copyright
---------

Copyright Daniel Berthereau for École des Ponts ParisTech, 2012


[1]: http://www.omeka.org "Omeka.org"
[2]: https://github.com/Daniel-KM/ArchiveRepertory "GitHub ArchiveRepertory"
[3]: https://github.com/Daniel-KM/FileModify/Issues "GitHub FileModify"
[4]: http://www.cecill.info/licences/Licence_CeCILL_V2-en.html "CeCILL"
[5]: https://www.gnu.org/licenses/gpl-3.0.html "GNU/GPL"
[6]: http://github.com/Daniel-KM "Daniel_KM"
[7]: http://bibliotheque.enpc.fr "École des Ponts ParisTech"
