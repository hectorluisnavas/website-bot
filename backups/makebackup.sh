#!/bin/sh

current_time=$(date +"%Y-%m-%d-%H-%M-%S")
file_name=$(date +"%Y-%m-%d-%H-%M-%S")"_dump.sql"

echo Starting Backup...

rm -f latest.dump

heroku pg:backups:capture --remote heroku-prod
heroku pg:backups:download --remote heroku-prod

pg_restore latest.dump > "$current_time""_dump.sql"

echo Backup saved to $file_name

echo Finished!
