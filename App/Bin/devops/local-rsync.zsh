#!/bin/zsh

REMOTE_HOST_SSH_PATH="user@server.url.or.ip:/path/to/remote/folder/"
LOCAL_FOLDER="/path/to/local/folder/*"

rsync -a -e "ssh" --exclude 'Core/Bin/codegen/node_modules' --exclude 'App/Config/AppConfig.php' --exclude 'uploads/*' --exclude '.git' --exclude '.idea' --exclude '.DS_Store' ${LOCAL_FOLDER} ${REMOTE_HOST_SSH_PATH}

if [[ $? -ne 0 ]]
then
  echo "An error has occurred. Aborting..."
  exit
fi

echo "Syncing done."