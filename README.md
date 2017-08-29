# [osTicket](https://github.com/osTicket/osTicket) NoteFirst Plugin

Ensures that when you view a ticket, the "Post Internal Note" tab is highlighted where possible.

## To install
* First: install https://github.com/clonemeagain/attachment_preview as this relies on it.
* Then [download](https://github.com/clonemeagain/osticket-plugin-notefirst/archive/master.zip)/clone this into your /include/plugins directory as a directory
* Then visit /scp/plugins.php to install & enable it.

Should work with 1.9+ Please test and let me know. Conceivable that it works with 1.8, if that is still around.

## Can be made much simpler, if you are willing to change core a touch!

* Edit include/class.osticket.php
* Go right to the bottom to the "start" function, add at the beggining: ```global $ost;```
* Effectively, apply https://github.com/osTicket/osTicket/pull/2907 
* Now we don't need the attachments_preview api! :-)
* Now just rename the bootstrap method to "old_bootstrap" and the "new_bootstrap" method should be renamed "bootstrap"


I've subscribed to that pull-request, so I'll update this when/if that get's merged.
