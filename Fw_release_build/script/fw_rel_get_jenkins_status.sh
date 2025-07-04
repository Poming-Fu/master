#!/bin/bash

db_server=$(hostname -I | awk '{print $1}')
db_user="one"
db_password="1234"
database="ipmi"
table="fw_r_form_history"
USERNAME="baber"
PASSWORD="baber"

mysql_conn="mysql -u"$db_user" -p"$db_password" -h"$db_server" "$database""

function get_build_to_update() {
    $mysql_conn -se "SELECT UUID, build_url FROM $table WHERE status IN ('pending', 'in_progress')"
}


function get_build_info() {
    local build_url=$1
    curl -s -u "${USERNAME}:${PASSWORD}" "${build_url}/api/json" | jq '{
        result: .result,
        inProgress: .inProgress,
        building: .building
    }'
}

# update table : fw_r_form_history => status
function update_build_status() {
    local UUID=$1
    local status=$2
    $mysql_conn -e "UPDATE $table SET status='$status' WHERE UUID='$UUID'"
}

function main() {
    while read -r UUID build_url; do
        if [ -n "$build_url" ]; then
            build_info=$(get_build_info "$build_url")
            in_progress=$(echo "$build_info" | jq -r '.inProgress')
            building=$(echo "$build_info" | jq -r '.building')
            result=$(echo "$build_info" | jq -r '.result')

            if [ "$in_progress" = "true" ] || [ "$building" = "true" ]; then
                update_build_status "$UUID" "in_progress"
            elif [ "$result" = "SUCCESS" ]; then
                update_build_status "$UUID" "completed"
            elif [ "$result" = "FAILURE" ]; then
                update_build_status "$UUID" "failed"
            fi
        fi
    done < <(get_build_to_update) #function 有讀到 UUID build_url 就往下做
    
    if [[ $? != 0 ]];then
        echo "get jenkins status script error !"
    fi
}

main