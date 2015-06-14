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

Finally, you should adapt the files `libraries/FileModify/Preprocess.php` and
`libraries/FileModify/Rename.php` to your specific needs, if used.

If you use the renaming feature, you need to install and enable the plugin
[Archive Repertory]. Furthermore, you should take care with non-ascii filenames
if your server is not fully UTF-8 compliant.


Usage
-----

Two mechanisms are provided in order to transform files.

* Simple append to ImageMagick "convert" command
A field allows to add simple parameters to "convert". Two examples: The first
reduces the quality of the original and the second adds a watermark.

```
    -resample 96x96 -quality 50 -resize 50%
    -pointsize 120 -draw "gravity South fill black text 0,12 'Powered by Omeka' fill blue text 1,11 'Powered by Omeka'"
```

Warning: parameters are not escaped.

* Use of a library

The default library adds a watermark to an image. Parameters can be, for this
library, for a watermark from a file or a text:

```
    /www/plugins/FileModify/views/shared/images/qrcode.png, Center, fixe, 85
    Powered by Omeka, Center, fixe, 85
```


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

* Copyright Daniel Berthereau, 2012-2015


[Omeka]: https://omeka.org
[File Modify]: https://github.com/Daniel-KM/FileModify
[Archive Repertory]: https://github.com/Daniel-KM/ArchiveRepertory
[File Modify issues]: https://github.com/Daniel-KM/FileModify/issues
[CeCILL v2.1]: https://www.cecill.info/licences/Licence_CeCILL_V2.1-en.html
[GNU/GPL]: https://www.gnu.org/licenses/gpl-3.0.html "GNU/GPL v3"
[FSF]: https://www.fsf.org
[OSI]: http://opensource.org
[Daniel-KM]: https://github.com/Daniel-KM "Daniel Berthereau"
[École des Ponts ParisTech]: http://bibliotheque.enpc.fr
[Mines ParisTech]: https://patrimoine.mines-paristech.fr
