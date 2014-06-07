New Member Widget
=================

With this addon you can enable a widget for the sidebar of the network tab,
which will be displayed for new members. It contains a linkt to friendicas
introduction pages at /newmember and optionally

 * a link to the global support forum
 * a link to an eventually existing local support forum
 * a welcome message you might want to send to your new members.

There is no extra styling added for this added, so it should work with any
theme you have selected, or your user selects. But it was only tested with
duepuntozero,quattro and clean.

Testing it
----------
You want to test it, but you don't want to create a new account? Take your fav
text editor (yeah emacs will do as well) and edit the newmemberwidget.php file.
In line 21 you will find a if clause, just add a ! infront of the

	x(!_SESSION 

that will negate the requirement of being a new member, so don't forget the
remove that ! again after testing ;-)

Translations
------------
If you want to translate this addon, please grab a copy of lang/C/message.po
and copy it to lang/XX/message.po where XX is the language you want to
translate for (i.e. de, fr, nb-no). After you translated the content go to the
root of your friendica directory and run

	php util/po2php.php addon/newmember/lang/XX/message.po

to translate the message.po file into the strings.php file needed by friendica
to catch your translations.

With the translated files, either do a pull request at github, or send me the
two files (message.po and strings.php) you just worked with as attachment to an
email.

Author
------
 * Tobias Diekershoff <tobias.diekershoff(at)gmx.net>

History
-------
 * **Version 1** (2014-06-01): Initial Release

License
-------
This addon is licensed under the [MIT License](http://opensource.org/licenses/MIT).

> Copyright (c) 2014 Tobias Diekershoff

> Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
> 
> The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
> 
> THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
