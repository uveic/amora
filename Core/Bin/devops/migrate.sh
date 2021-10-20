#! /bin/bash

MYDIR="$(dirname "$(which "$0")")"

echo "Running database migrations..."
php "$MYDIR"/../core_migrate_db.php migrate

if [[ $? -ne 0 ]]
then
  echo "Error running database migrations. Aborting..."
  exit
fi

echo "Running lookup tables sync..."
php "$MYDIR"/../core_sync_lookup_tables.php

if [[ $? -ne 0 ]]
then
  echo "Error syncing lookup tables. Aborting..."
  exit
fi

echo "Done!"
