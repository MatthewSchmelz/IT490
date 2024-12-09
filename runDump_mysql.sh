#!/bin/bash

# Configuration
DB_NAME="imdb_database"         # Replace with your database name
DB_USER="IT490"             # Replace with your MySQL username
DB_PASSWORD="IT490"         # Replace with your MySQL password
DUMP_FILE="/home/qadeer/git/Project/Database/Database.sql"       # Full path to your dump.sql file

# Log the start of the script
echo "Starting MySQL dump execution at $(date)" >> /var/log/mysql_dump_execution.log

# Check if the dump file exists
if [ ! -f "$DUMP_FILE" ]; then
    echo "Error: Dump file $DUMP_FILE does not exist!" >> /var/log/mysql_dump_execution.log
    exit 1
fi

# Execute the dump.sql file
mysql -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$DUMP_FILE"
MYSQL_STATUS=$?

# Log the result of the execution
if [ $MYSQL_STATUS -eq 0 ]; then
    echo "Successfully executed $DUMP_FILE on $DB_NAME at $(date)" >> /var/log/mysql_dump_execution.log
else
    echo "Failed to execute $DUMP_FILE on $DB_NAME. Exit status: $MYSQL_STATUS" >> /var/log/mysql_dump_execution.log
    exit $MYSQL_STATUS
fi

# Exit successfully
exit 0

