#!/bin/bash
# Baber 2025 01
# For new mp510 setting via web service & database

# Color definitions
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Global variables
declare -r SUDO_PASSWORD="1234"
declare -r MYSQL_ROOT_PASSWORD="1234"
declare -r MYSQL_USER="one"
declare -r MYSQL_USER_PASSWORD="1234"
declare -r LOG_FILE="/tmp/environment_setup.log"
declare -r SHARE_PATH="/home/one/share_one"
declare -r NAS_USER="sam"
declare -r NAS_PASSWORD="sam"
declare -r NAS_HOST="10.148.165.16"
declare ROLE=""

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

#check master or slave
check_role() {
    while true; do
        read -p "Please enter role (master/slave): " char
        case "$char" in
            "slave")
                echo "Setting up as SLAVE node"
                ROLE="slave"
                break
                ;;
            "master")
                echo "Setting up as MASTER node"
                ROLE="master"
                break
                ;;
            *)
                echo "Invalid input. Please enter 'master' or 'slave'"
                ;;
        esac
    done
}


# Check root privileges
check_sudo() {
    log_message "Checking root privileges..."
    if [[ $(whoami) != "root" ]]; then
        echo "${SUDO_PASSWORD}" | sudo -S echo "Root access granted" || error_exit "Failed to get root access"
    fi
    log_message "Root access confirmed"
}

# Check if service is running
check_installed() {
    local service_name=$1
    
    if systemctl is-active --quiet "$service_name"; then
        log_message "$service_name service is already running"
        return 0
    fi
    return 1
}

#Install sshpass
install_sshpass() {
    log_message "Installing sshpass..."
    
    if ! command -v sshpass &> /dev/null; then
        sudo apt-get install -y sshpass || error_exit "Failed to install sshpass"
    fi

    log_message "sshpass installation completed"
}


# Install PHP
install_php() {
    log_message "Installing PHP..."
    
    if check_installed "apache2"; then
        return  
    fi

    sudo apt-get update || error_exit "apt-get update failed"
    sudo apt-get install -y software-properties-common || error_exit "Failed to install dependencies"
    sudo add-apt-repository -y ppa:ondrej/php || error_exit "Failed to add PHP repository"
    sudo apt-get update || error_exit "Failed to update package list"
    
    local php_packages=(
        "php8.2"
        "php8.2-cli"
        "php8.2-fpm"
        "php8.2-mysql"
        "php8.2-xml"
        "php8.2-mbstring"
        "php8.2-curl"
        "php8.2-zip"
        "php-ldap"
    )

    for package in "${php_packages[@]}"; do
        sudo apt-get install -y "$package" || error_exit "Failed to install $package"
    done

    sudo update-alternatives --set php /usr/bin/php8.2 || error_exit "Failed to set PHP version"
    sudo a2enmod ssl || error_exit "Failed to enable SSL module"
    sudo a2ensite default-ssl.conf || error_exit "Failed to enable SSL config"
    sudo systemctl restart apache2 || error_exit "Failed to restart Apache"
    
    log_message "PHP installation completed"
    php -v
}

# Install Node.js
install_nodejs() {
    log_message "Installing Node.js..."
    
    if command -v node > /dev/null; then
        log_message "Node.js is already installed"
        return
    fi

    curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash - || error_exit "Failed to setup Node.js repository"
    sudo apt-get install -y nodejs || error_exit "Failed to install Node.js"
    sudo apt-get install -y nodemon || error_exit "Failed to install nodemon"
    
    log_message "Node.js installation completed"
    node --version
    npm --version
}

# Install MariaDB
install_mariadb() {
    log_message "Installing MariaDB..."
    
    if check_installed "mariadb"; then
        return
    fi

    sudo apt-get install -y mariadb-server || error_exit "Failed to install MariaDB"
    sudo mysql_install_db || error_exit "Failed to initialize MariaDB"
    
    sudo mysql -e "
        USE mysql;
        ALTER USER 'root'@'localhost' IDENTIFIED VIA mysql_native_password USING PASSWORD('${MYSQL_ROOT_PASSWORD}');
        CREATE USER '${MYSQL_USER}'@'%' IDENTIFIED BY '${MYSQL_USER_PASSWORD}';
        GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_USER}'@'%';
        FLUSH PRIVILEGES;
    " || error_exit "Failed to configure MariaDB users"
    
    sudo sed -i 's/bind-address.*=.*/bind-address = 0.0.0.0/' /etc/mysql/mariadb.conf.d/50-server.cnf || error_exit "Failed to update bind-address"
    sudo systemctl restart mariadb || error_exit "Failed to restart MariaDB"
    
    log_message "MariaDB installation completed"
}

# Install ser2net
install_ser2net() {
    log_message "Installing ser2net..."
    
    if check_installed "ser2net"; then
        log_message "ser2net already installed"
        return
    fi

    sudo apt-get install -y ser2net || error_exit "Failed to install ser2net"
    
    # 備份原配置文件
    if [ -f "/etc/ser2net.yaml" ]; then
        sudo cp /etc/ser2net.yaml /etc/ser2net.yaml.backup || error_exit "Failed to backup ser2net config"
    fi
    
    # 複製新配置
    sudo cp /home/one/share_one/web1/Mp510_control/mp510/ser2net.yaml /etc/ser2net.yaml || error_exit "Failed to copy ser2net config"
    
    # 重啟服務
    sudo systemctl restart ser2net || error_exit "Failed to restart ser2net"
    sudo systemctl enable ser2net || error_exit "Failed to enable ser2net"
    
    log_message "ser2net installation completed"
}

# Install Samba
install_samba() {
    log_message "Installing Samba..."

    if check_installed "smbd"; then
        return
    fi

    sudo apt-get update || error_exit "Failed to update package list"
    sudo apt-get install -y samba || error_exit "Failed to install Samba"

    if [ ! -d "$SHARE_PATH" ]; then
        sudo mkdir -p "$SHARE_PATH" || error_exit "Failed to create share directory"
    fi
    
    sudo chmod 777 -R "$SHARE_PATH" || error_exit "Failed to set directory permissions"
    sudo chown one:one "$SHARE_PATH" || error_exit "Failed to set directory owner"
    echo -e "1234\n1234" | sudo smbpasswd -s -a one || error_exit "Failed to set Samba password"

    sudo cp /etc/samba/smb.conf /etc/samba/smb.conf.backup || error_exit "Failed to backup Samba config"

    sudo tee -a /etc/samba/smb.conf > /dev/null << EOF || error_exit "Failed to update Samba config"
[share_one]
path = $SHARE_PATH
browseable = yes
writeable = yes
read only = no
create mask = 0755
directory mask = 0755
EOF

    testparm -s || error_exit "Samba config syntax check failed"
    sudo systemctl restart smbd
    sudo systemctl restart nmbd
    sudo systemctl enable smbd
    sudo systemctl enable nmbd 

    log_message "Samba installation completed"
    local IP=$(hostname -I | cut -d' ' -f1)
    log_message "Samba path: \\\\${IP}\\share_one"
}

# Mount NAS
mount_nas() {
    log_message "Mounting NAS..."
    sudo mkdir -p /mnt/{DB,Golden_FW}

    # 檢查 Golden_FW 掛載
    if mountpoint -q /mnt/Golden_FW; then
        log_message "Golden_FW is already mounted"
    else
        sudo mount -o username=$NAS_USER,password=$NAS_PASSWORD,iocharset=utf8 //$NAS_HOST/Golden_FW /mnt/Golden_FW || error_exit "Failed to mount Golden_FW"
    fi

    # 檢查 DB 掛載
    if mountpoint -q /mnt/DB; then
        log_message "DB is already mounted"
    else
        sudo mount -o username=$NAS_USER,password=$NAS_PASSWORD,iocharset=utf8 //$NAS_HOST/DB /mnt/DB || error_exit "Failed to mount DB"
    fi

    log_message "NAS mounted successfully"
}

# Setup Crontab
setup_slave_crontab() {
    log_message "Setting up slave Crontab..."

    # 執行一次
    bash slave_backup.sh
    
    crontab_slave_backup="0 12 * * * /home/one/share_one/mp510/slave_backup.sh"
    crontab_bmc_console="@reboot nodemon /home/one/share_one/web1/Device_control/websocket-terminal/bmc-console-backend.js"
    
    # 檢查crontab 是否已存在
    if ! crontab -l 2>/dev/null | grep -q "slave_backup.sh"; then
        (crontab -l 2>/dev/null; echo "${crontab_slave_backup}") | crontab -
        log_message "Crontab slave_backup.sh added"
    else
        log_message "Crontab slave_backup.sh exists"
    fi

    if ! crontab -l 2>/dev/null | grep -q "bmc-console-backend.js"; then
        (crontab -l 2>/dev/null; echo "${crontab_bmc_console}") | crontab -
        log_message "Crontab bmc-console-backend added"
    else
        log_message "Crontab bmc-console-backend exists"
    fi

    log_message "Crontab setup completed"
}

setup_master_crontab() {
    log_message "Setting up master Crontab..."

    crontab_master_backup="50 11 * * * /home/one/share_one/mp510/master_backup.sh"
    crontab_bmc_console="@reboot nodemon /home/one/share_one/web1/Device_control/websocket-terminal/bmc-console-backend.js"
    
    # 檢查crontab 是否已存在
    if ! crontab -l 2>/dev/null | grep -q "master_backup.sh"; then
        (crontab -l 2>/dev/null; echo "${crontab_master_backup}") | crontab -
        log_message "Crontab master_backup.sh added"
    else
        log_message "Crontab master_backup.sh exists"
    fi

    if ! crontab -l 2>/dev/null | grep -q "bmc-console-backend.js"; then
        (crontab -l 2>/dev/null; echo "${crontab_bmc_console}") | crontab -
        log_message "Crontab bmc-console-backend added"
    else
        log_message "Crontab bmc-console-backend exists"
    fi

    log_message "Crontab setup completed"
}

# Main installation process
main() {

    log_message "Starting environment setup..."
    check_role
    check_sudo
    
    install_sshpass

    install_php
    install_nodejs
    install_mariadb
    install_samba
    install_ser2net

    mount_nas

    # 根據ROLE設定 crontab
    if [ "$ROLE" == "slave" ]; then
        setup_slave_crontab
        log_message "Slave setup completed. Will sync with master at 12:00 daily."
    elif [ "$ROLE" == "master" ]; then
        setup_master_crontab
        log_message "Master setup completed. Will backup at 11:50 daily."
    else
        error_exit "wrong ROLE"
    fi

    log_message "Environment setup completed!"
}

main "$@"