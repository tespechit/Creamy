#!/bin/bash

if [ $# -ne 2 ]; then
  	echo "Usage: send_birthday_mail.sh <email> <name>"  
	exit 1
fi

content=`cat ./content.html`
realname=$2
modified="${content/nameofcontact/$realname}"

export EMAIL=info@tumejorseguromedico.com && echo ${modified} | mutt -e 'set content_type="text/html"' -e 'set from=info@tumejorseguromedico.com' $1 -s "¡Feliz cumpleaños $2!"
