#!/bin/zsh

REMOTE_DATABASE_NAME="remove_db_name"
REMOTE_HOST="user@server.url.or.ip"
REMOTE_FILEPATH="/path/to/backup/folder/in/remove/server/"

LOCAL_DATABASE_NAME="local_db_name"
LOCAL_DATABASE_PASS="local_db_pass"

MYDIR="$(dirname "$(which "$0")")"

if [[ $(date -u '+%M') -ge 20 ]]
then
  FILENAME_DATE=$(date -u '+%Y-%m-%d_%Hh')15m
else
  FILENAME_DATE=$(date -u -v-1H '+%Y-%m-%d_%Hh')15m
fi

BACKUP_FILENAME="backup_${REMOTE_DATABASE_NAME}_${FILENAME_DATE}"
COMPRESSED_FILENAME="${BACKUP_FILENAME}.sql.gz"

echo "Deleting database tables and content..."
php "$MYDIR"/../../../Core/Bin/core_migrate_db.php install

if [[ $? -ne 0 ]]
then
  echo "An error has occurred. Aborting..."
  exit
fi

echo "Getting latest database backup (${COMPRESSED_FILENAME})..."
scp ${REMOTE_HOST}:${REMOTE_FILEPATH}"${COMPRESSED_FILENAME}" "${COMPRESSED_FILENAME}"

if [[ $? -ne 0 ]]
then
  echo "An error has occurred. Aborting..."
  exit
fi

echo "Decompressing database backup..."
gzip -dkf "${COMPRESSED_FILENAME}"
rm "${COMPRESSED_FILENAME}"

if [[ $? -ne 0 ]]
then
  rm "${BACKUP_FILENAME}.sql"
  echo "An error has occurred. Aborting..."
  exit
fi

export PATH=${PATH}:/usr/local/mysql/bin/
echo "mysql -u root -p'${LOCAL_DATABASE_PASS}' ${LOCAL_DATABASE_NAME} < $MYDIR/${BACKUP_FILENAME}.sql"
echo "Restoring database..."

mysql -u root -p"${LOCAL_DATABASE_PASS}" ${LOCAL_DATABASE_NAME} < "$MYDIR/${BACKUP_FILENAME}.sql"

if [[ $? -ne 0 ]]
then
  rm "${BACKUP_FILENAME}.sql"
  echo "An error has occurred. Aborting..."
  exit
fi

rm "${BACKUP_FILENAME}.sql"
zsh "$MYDIR"/../../../Core/Bin/devops/migrate.sh
