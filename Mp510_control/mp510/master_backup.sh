#!/bin/bash

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Global variables
declare -r SUDO_PASSWORD="1234"
declare -r MYSQL_USER="one"
declare -r MYSQL_USER_PASSWORD="1234"
declare -r LOG_FILE="/tmp/environment_setup.log"

# Error handling
error_exit() {
    echo -e "${RED}Error: $1${NC}" >&2
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Error: $1" >> "$LOG_FILE"
    exit 1
}

# Logging
log_message() {
    echo -e "${GREEN}$1${NC}"
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> "$LOG_FILE"
}

# Check root privileges
check_sudo() {
    log_message "Checking root privileges..."
    if [[ $(whoami) != "root" ]]; then
        echo "${SUDO_PASSWORD}" | sudo -S echo "Root access granted" || error_exit "Failed to get root access"
    fi
    log_message "Root access confirmed"
}

# Backup database
backup_database() {
    log_message "Starting database backup..."
    
    # Create backup
    mysqldump -u "$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" ipmi > "/tmp/ipmi_database.sql" || error_exit "Database backup failed"
    
    # Set permissions 
    sudo chmod 777 "/tmp/ipmi_database.sql" || error_exit "Failed to set permissions"
    
    log_message "Database backup completed"
}

# Main process
main() {   
    log_message "Starting master backup process..."
    
    check_sudo
    backup_database
    
    log_message "Backup completed successfully!"
}

main "$@"