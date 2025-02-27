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
declare -r SHARE_PATH="/home/one/share_one"
declare -r MASTER_HOST="10.148.175.12"

# Random delay function (0-300 seconds)
sleep_random() {
    local i=$((RANDOM % 300))
    log_message "Waiting ${i} seconds before sync..."
    sleep $i
}

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

# Check master host availability
check_master_available() {
    log_message "Checking master host connectivity..."
    if ! ping -c 1 ${MASTER_HOST} &> /dev/null; then
        error_exit "Cannot connect to master host ${MASTER_HOST}"
    fi
}

# Copy web service code
copy_web_code() {
    log_message "Updating web code..."
    
    if ! command -v sshpass &> /dev/null; then
        sudo apt-get install -y sshpass || error_exit "Failed to install sshpass"
    fi

    sudo sshpass -p "$SUDO_PASSWORD" scp -r "one@${MASTER_HOST}:/home/one/share_one/web1" "${SHARE_PATH}/" || error_exit "Failed to copy web code"

    if [ -L "/var/www/html/web1" ]; then
        sudo rm "/var/www/html/web1"
    fi
    sudo ln -s "${SHARE_PATH}/web1" "/var/www/html/web1" || error_exit "Failed to create symlink"

    sudo chown -R one:www-data "${SHARE_PATH}/web1" || error_exit "Failed to set ownership"
    sudo chmod -R 777 "${SHARE_PATH}/web1" || error_exit "Failed to set permissions"

    sudo systemctl restart apache2 || error_exit "Failed to restart Apache"
}

# Copy master database
copy_master_sql() {
    log_message "Updating database..."
    
    if ! command -v sshpass &> /dev/null; then
        sudo apt-get install -y sshpass || error_exit "Failed to install sshpass"
    fi

    sudo sshpass -p "$SUDO_PASSWORD" scp -r "one@${MASTER_HOST}:/tmp/ipmi_database.sql" /tmp/ || error_exit "Failed to copy database file"
    
    mysql -u "$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS ipmi;" || error_exit "Failed to create database"
    mysql -u "$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" ipmi < /tmp/ipmi_database.sql || error_exit "Failed to import database"
}

# Main process
main() {   
    log_message "Starting sync process..."
    
    #sleep_random
    check_sudo
    check_master_available
    
    #copy_web_code
    copy_master_sql
    
    log_message "Sync completed successfully!"
}

main "$@"