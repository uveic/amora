#! /bin/bash

MYDIR="$(dirname "$(which "$0")")"

echo "Restoring the database..."
php "$MYDIR"/../core_migrate_db.php install

if [[ $? -ne 0 ]]
then
  echo "Error restoring the database. Aborting..."
  exit
fi

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

echo "Creating default admin user..."
php "$MYDIR"/../core_create_admin_user.php --email=victor@victorgonzalez.eu --pass=testuve123 --name=Victor

if [[ $? -ne 0 ]]
then
  echo "Error creating default user. Aborting..."
  exit
fi

echo "Done!"
