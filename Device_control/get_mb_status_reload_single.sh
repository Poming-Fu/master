#!/bin/bash

# 設定資料庫連線參數
db_server="10.148.175.12"
db_user="one"
db_password="1234"
database="ipmi"
table="boards"
account="ADMIN"
password="ADMIN"
ip=$1
# 連接資料庫並執行 SQL 
# mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database"

function ping_and_get_ip_status() {
    # 測試testmb 
    select_db_IP="select IP from $table ORDER BY mp_num"
    #-s -N 去表格
    get_ips=$(mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "$select_db_IP" -s -N)


    
        if ping -c 1 -W 4 "$ip" > /dev/null; then
            status="online"
            echo "$ip $status" #測試先echo出來
        else
            status="offline"
            echo "$ip $status" #測試先echo出來
        fi
        mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "UPDATE $table SET status='$status' WHERE ip='$ip'"
}

function get_board_id_and_ver() {
    # 測試testmb , table = testmb
    select_db_IP="select IP from $table ORDER BY mp_num"
    #-s -N 去表格
    get_ips=$(mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "$select_db_IP" -s -N)
        #id => ipmitool -I lanplus -H 10.148.175.133 -U ADMIN -P ADMIN raw 0x6 0x1 |awk -F ' ' '{print $11 $10}'
        #fw => ipmitool -I lanplus -H 10.148.175.133 -U ADMIN -P ADMIN raw 0x6 0x1 |awk -F ' ' '{print $3 "." $4 "." $12}'
        get_status=$(mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "select status from $table WHERE ip='$ip'" -s -N)
        if [ "$get_status" == "online" ];then
            get_bmc_info="$(ipmitool -I lanplus -H $ip -U $account -P $password raw 0x6 0x1)"
            board_id=$(echo "$get_bmc_info" |awk -F ' ' '{print $11 $10}')
            version=$(echo "$get_bmc_info" |awk -F ' ' '{print $3 "." $4 "." $12}')
            echo "$ip $get_status $board_id  $version"
            mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "UPDATE $table SET B_id='$board_id' WHERE ip='$ip'"
            mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "UPDATE $table SET version='$version' WHERE ip='$ip'"
        else
            echo "$ip $get_status"
            mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "UPDATE $table SET B_id=NULL WHERE ip='$ip'"
            mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database" -e "UPDATE $table SET version=NULL WHERE ip='$ip'"
        fi
}

ping_and_get_ip_status
get_board_id_and_ver





