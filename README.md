File Modify (plugin for Omeka)
====================================


[File Modify] is a plugin for [Omeka] allows to modify (convert, compress,
watermark, rename or any other command) uploaded file before saving it in
files folder and before creating metadata in Omeka database.

The example process adds a watermark to each uploaded image.
Original files can be backup automatically if wanted.

Renaming requires [Archive Repertory] plugin.


Installation
------------

Uncompress files and rename plugin folder "FileModify".

Then install it like any other Omeka plugin and follow the config instructions.

Finally, you should adapt the files `libraries/file_modify_preprocess.php` and
`libraries/file_modify_rename.php` to your specific needs, if used.

If you use the renaming feature, you need to install and enable the plugin
[Archive Repertory]. Furthermore, you should take care with non-ascii filenames
if your server is not fully UTF-8 compliant.


Warning
-------

Use it at your own risk.

It's always recommended to backup your files and database regularly so you can
roll back if needed.


Troubleshooting
---------------

See online issues on the [File Modify issues] page on GitHub.


License
-------

This plugin is published under the [CeCILL v2.1] licence, compatible with
[GNU/GPL] and approved by [FSF] and [OSI].

In consideration of access to the source code and the rights to copy, modify and
redistribute granted by the license, users are provided only with a limited
warranty and the software's author, the holder of the economic rights, and the
successive licensors only have limited liability.

In this respect, the risks associated with loading, using, modifying and/or
developing or reproducing the software by the user are brought to the user's
attention, given its Free Software status, which may make it complicated to use,
with the result that its use is reserved for developers and experienced
professionals having in-depth computer knowledge. Users are therefore encouraged
to load and test the suitability of the software as regards their requirements
in conditions enabling the security of their systems and/or data to be ensured
and, more generally, to use and operate it in the same conditions of security.
This Agreement may be freely reproduced and published, provided it is not
altered, and that no provisions are either added or removed herefrom.


Contact
-------

Current maintainers:

* Daniel Berthereau (see [Daniel-KM] on GitHub)

First version of this plugin has been built for [École des Ponts ParisTech].
Upgrade to 2.0 has been made for [Mines ParisTech].

Copyright
---------

* Copyright Daniel Berthereau, 2012-2014


[Omeka]: http://www.omeka.org
[File Modify]: https://github.com/Daniel-KM/FileModify
[Archive Repertory]: https://github.com/Daniel-KM/ArchiveRepertory
[File Modify issues]: https://github.com/Daniel-KM/FileModify/Issues
[CeCILL v2.1]: http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html "GNU/GPL v3"
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Daniel-KM]: http://github.com/Daniel-KM "Daniel Berthereau"
[École des Ponts ParisTech]: http://bibliotheque.enpc.fr
[Mines ParisTech]: https://patrimoine.mines-paristech.fr
