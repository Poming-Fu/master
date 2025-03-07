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
declare -r MYSQL_TABLE="ipmi"
declare -r LOG_FILE="/tmp/environment_setup.log"
declare -r SHARE_PATH="/home/one/share_one"

# Get current host IP
CURRENT_IP=$(hostname -I | awk '{print $1}')

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

# Determine if current host is master
is_master() {
    log_message "Determining node type..."
    
    # Get the master IP from database 輸出只包含 IP 地址本身，沒有欄位名稱，沒有邊框，沒有表格格式。
    GET_MASTER_IP=$(mysql -u"$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" -h"$CURRENT_IP" "$MYSQL_TABLE" -s -N -e "SELECT mp_ip FROM mp510 WHERE node_type = 'master'")
    
    if [ -z "$GET_MASTER_IP" ]; then
        error_exit "Failed to get master IP from database"
    fi
    
    if [ "$CURRENT_IP" == "$GET_MASTER_IP" ]; then
        log_message "This node is MASTER"
        return 0  # Return 0 for master
    else
        log_message "This node is SLAVE. Master IP: $GET_MASTER_IP"
        return 1  # Return 1 for slave
    fi
}

# Get all slave IPs and update their status
get_slave_ips() {
    log_message "Getting slave IPs and checking their status..."
    
    # Get slave IPs from database
    SLAVE_IPS=$(mysql -u"$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" -h"$CURRENT_IP" "$MYSQL_TABLE" -s -N -e "SELECT mp_ip FROM mp510 WHERE node_type = 'slave'")
    
    if [ -z "$SLAVE_IPS" ]; then
        error_exit "Failed to get slave IPs from database or no slaves configured"
    fi
    
    # Check each slave's status and update in database
    AVAILABLE_SLAVES=""
    for slave_ip in $SLAVE_IPS; do
        if ping -c 1 -W 2 ${slave_ip} &> /dev/null; then
            # Slave is online
            log_message "Slave ${slave_ip} is ONLINE"
            mysql -u"$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" -h"$CURRENT_IP" "$MYSQL_TABLE" -e "UPDATE mp510 SET status = 'online' WHERE mp_ip = '${slave_ip}'" || \
            log_message "Warning: Failed to update status for ${slave_ip}"
            
            # Add to available slaves list
            AVAILABLE_SLAVES="${AVAILABLE_SLAVES} ${slave_ip}"
        else
            # Slave is offline
            log_message "Slave ${slave_ip} is OFFLINE"
            mysql -u"$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" -h"$CURRENT_IP" "$MYSQL_TABLE" -e "UPDATE mp510 SET status = 'offline' WHERE mp_ip = '${slave_ip}'" || \
            log_message "Warning: Failed to update status for ${slave_ip}"
        fi
    done
    
    # Trim leading space
    AVAILABLE_SLAVES=$(echo "$AVAILABLE_SLAVES" | sed 's/^ *//')
    
    if [ -z "$AVAILABLE_SLAVES" ]; then
        log_message "Warning: No slaves are currently online"
    else
        log_message "Available slaves: $AVAILABLE_SLAVES"
    fi
    
    echo "$AVAILABLE_SLAVES"
}

# Backup database (master function)
backup_database() {
    log_message "Starting database backup..."
    
    # Create backup
    mysqldump -u "$MYSQL_USER" -p"$MYSQL_USER_PASSWORD" -h"$CURRENT_IP" "$MYSQL_TABLE" > "/tmp/ipmi_database.sql" || error_exit "Database backup failed"
    
    # Set permissions 
    sudo chmod 777 "/tmp/ipmi_database.sql" || error_exit "Failed to set permissions"
    
    log_message "Database backup completed"
}

# Push database to slave (master function)
push_database_to_slave() {
    local slave_ip="$1"
    log_message "Pushing database to slave $slave_ip..."
    
    if ! command -v sshpass &> /dev/null; then
        sudo apt-get install -y sshpass || error_exit "Failed to install sshpass"
    fi
    
    # Copy database file to slave
    sudo sshpass -p "$SUDO_PASSWORD" scp -r "/tmp/ipmi_database.sql" "one@${slave_ip}:/tmp/" || error_exit "Failed to copy database file to ${slave_ip}"
    
    # Execute import command on slave
    sudo sshpass -p "$SUDO_PASSWORD" ssh "one@${slave_ip}" "
        echo '${SUDO_PASSWORD}' | sudo -S echo 'Root access granted' && 
        mysql -u '${MYSQL_USER}' -p'${MYSQL_USER_PASSWORD}' -h localhost -e 'CREATE DATABASE IF NOT EXISTS ${MYSQL_TABLE};' && 
        mysql -u '${MYSQL_USER}' -p'${MYSQL_USER_PASSWORD}' -h localhost ${MYSQL_TABLE} < /tmp/ipmi_database.sql && 
        echo 'Database import completed on ${slave_ip}'
    " || log_message "Warning: Failed to execute database import on ${slave_ip}"
    
    log_message "Database pushed and imported on slave ${slave_ip}"
}

push_web_code_to_slave() {
    local slave_ip="$1"
    log_message "Pushing web code to slave $slave_ip..."
    
    # 確保 rsync 已安裝
    if ! command -v rsync &> /dev/null; then
        sudo apt-get install -y rsync || error_exit "Failed to install rsync"
    fi
    
    # 使用 sshpass 與 rsync 結合
    sudo sshpass -p "$SUDO_PASSWORD" rsync -avz --delete "${SHARE_PATH}/web1/" "one@${slave_ip}:${SHARE_PATH}/web1/" || error_exit "Failed to sync web code to ${slave_ip}"
    
    # 設置權限和重啟服務，但只在必要時創建符號連結
    sudo sshpass -p "$SUDO_PASSWORD" ssh "one@${slave_ip}" "
        echo '${SUDO_PASSWORD}' | sudo -S echo 'Root access granted' && 
        
        # 檢查符號連結是否已經正確設置
        if [ ! -L /var/www/html/web1 ] || [ \"\$(readlink /var/www/html/web1)\" != \"${SHARE_PATH}/web1\" ]; then
            sudo rm -f /var/www/html/web1 && 
            sudo ln -s ${SHARE_PATH}/web1 /var/www/html/web1 && 
            echo 'Symbolic link created/updated'
        else
            echo 'Symbolic link already properly configured'
        fi &&
        
        sudo chown -R one:www-data ${SHARE_PATH}/web1 && 
        sudo chmod -R 755 ${SHARE_PATH}/web1 && 
        sudo systemctl restart apache2 &&

        
        echo 'Web code update completed on ${slave_ip}'
    " || log_message "Warning: Failed to configure web code on ${slave_ip}"
    
    log_message "Web code pushed and configured on slave ${slave_ip}"
}

# Main process
main() {   
    check_sudo
    
    is_master
    IS_MASTER=$?
    
    if [ $IS_MASTER -eq 0 ]; then
        # Master operations
        log_message "Starting master operations..."
        
        # Step 1: Backup database
        backup_database
        
        # Step 2: Get all slave IPs
        SLAVE_IPS=$(get_slave_ips)
        
        # Step 3: Loop through each available slave and push updates
        if [ -z "$SLAVE_IPS" ]; then
            log_message "No online slaves available for updates"
        else
            for slave_ip in $SLAVE_IPS; do
                sleep 3
                # Push database to slave
                push_database_to_slave "$slave_ip"
                sleep 30

                # Push web code to slave (uncomment if needed)
                push_web_code_to_slave "$slave_ip"
                sleep 30
            done
        fi
        
        log_message "Master operations completed successfully!"
    else
        # Slave operations - do nothing, wait for master commands
        log_message "This is a slave node. Waiting for master to push updates."
        # No actions needed, as the master will SSH in and execute commands
    fi
}


main "$@"