#!/bin/bash

# Configuration
DB_NAME="imdb_database"        # Replace with your database name
DB_USER="IT490"            # Replace with your MySQL username
DB_PASSWORD="IT490"        # Replace with your MySQL password
OUTPUT_DIR="/home/qadeer/git/Project/Database" # Directory to save the dump file
DATE=$(date +"%Y-%m-%d_%H-%M-%S")   # Current timestamp
DUMP_FILE="Database.sql"

# Ensure the output directory exists
if [ ! -d "$OUTPUT_DIR" ]; then
    echo "Output directory does not exist. Creating..."
    mkdir -p "$OUTPUT_DIR"
    if [ $? -ne 0 ]; then
        echo "Failed to create output directory: $OUTPUT_DIR"
        exit 1
    fi
fi

# Change directory to the output directory
cd "$OUTPUT_DIR"
if [ $? -ne 0 ]; then
    echo "Failed to change directory to $OUTPUT_DIR"
    exit 1
fi

# Dump the database
mysqldump -u "$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" > "$DUMP_FILE"

# Verify the dump file was created in the specified directory
if [ $? -eq 0 ] && [ -f "$DUMP_FILE" ]; then
    echo "Database successfully dumped to $DUMP_FILE"
else
    echo "Failed to dump the database or save the file in $OUTPUT_DIR. Please check your credentials and configuration."
    exit 1
fi

