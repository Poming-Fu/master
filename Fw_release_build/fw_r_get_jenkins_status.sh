#!/bin/bash
#baber 0919
JENKINS_URL="http://10.148.21.21:8080"
USERNAME="baber"
PASSWORD="baber"
JOB_NAME=$1
BUILD_NUMBER=$2

case $JOB_NAME in
obmc_rel_1)
    JOB_NAME="Obmc%20Codebase%20Release"
    ;;
obmc_rel_2)
    JOB_NAME="Obmc%20Codebase%20Release_2"
    ;;
lbmc_rel_1)
    JOB_NAME="X12%20Codebase%20Release"
    ;;
lbmc_rel_2)
    JOB_NAME="X12%20Codebase%20Release_2"
    ;;
*)
    echo "No job_name in case"
    ;;
esac

function get_build_info() {
    last_build_info=$(curl -s -u "${USERNAME}:${PASSWORD}" "${JENKINS_URL}/job/${JOB_NAME}/${BUILD_NUMBER}/api/json?pretty=true")
    echo "$last_build_info" | jq "{
        builtOn: .builtOn,
        fullDisplayName: .fullDisplayName,
        build_number: .number,
        result: .result,
        inProgress: .inProgress,
        building: .building
    }"
}
#test
#echo "usage : ./get_build_info obmc_rel_1 lastBuild"
get_build_info

#執行示意圖
#./fw_r_get_jenkins_status.sh obmc_rel_1 lastBuild
#{
#  "fullDisplayName": "Obmc Codebase Release #90",
#  "build_number": 90,
#  "result": null,
#  "inProgress": true,
# "building": true
#}