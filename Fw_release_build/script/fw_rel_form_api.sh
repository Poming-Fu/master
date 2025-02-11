#!/bin/bash
#baber 2024/02/22 add lbmc surport
#baber 2024/09/04 add obmc surport
#For Fw_release_build web 


USER=$1
BRANCH_NAME=$2
BUILD_TARGET=$3
RELEASE_FW_VER=$4
BUILD_OPTION=$5
OEM_NAME=$6
UUID=$7


function jenkins_api() {
	#open session , output all data
	api_output=$(curl -verbose  -s 'http://10.148.21.21:8080/crumbIssuer/api/json' --user ${USER} 2>&1)
	echo $api_output
	
	#提取crumb
	CRUMB=$(echo "$api_output" | awk -F '"' '/"crumb":/ {print $8}')
	echo $CRUMB
	
	#提取cookie
	COOKIE=$(echo "$api_output" | awk -F '[:; ]+' '/Set-Cookie: JSESSIONID/ {print$3}')
	echo $COOKIE
	#jenkins server build 10.148.21.21

	#判斷BUILD_ON sx12 sx13 sh12 sh13 判斷 
	if [[ $BUILD_TARGET == *sx12* || $BUILD_TARGET == *sx13* || $BUILD_TARGET == *sh12* || $BUILD_TARGET == *sh13* || $BUILD_TARGET == *h13* || $BUILD_TARGET == *h12* || $BUILD_TARGET == *h11* ]]; then
		BUILD_ON="lbmc_rel_2"
	else
		BUILD_ON="obmc_rel_1"
	fi

	case $BUILD_ON in
	lbmc_rel_2)
	curl -s -X POST --cookie "${COOKIE}" -H "Jenkins-Crumb:${CRUMB}" \
		http://10.148.21.21:8080/job/X12%20Codebase%20Release_2/buildWithParameters \
		--user $USER \
		-F BRANCH_NAME=$BRANCH_NAME \
		-F BUILD_TARGET=$BUILD_TARGET \
		-F RELEASE_FW_VER=$RELEASE_FW_VER \
		-F BUILD_OPTION=$BUILD_OPTION \
		-F OEM_NAME=$OEM_NAME \
		-F UUID=$UUID
	
	;;
	obmc_rel_1)
	curl -s -X POST --cookie "${COOKIE}" -H "Jenkins-Crumb:${CRUMB}" \
		http://10.148.21.21:8080/job/Obmc%20Codebase%20Release_1/buildWithParameters \
		--user $USER \
		-F BRANCH_NAME=$BRANCH_NAME \
		-F BUILD_TARGET=$BUILD_TARGET \
		-F RELEASE_FW_VER=$RELEASE_FW_VER \
		-F BUILD_OPTION=$BUILD_OPTION \
		-F UUID=$UUID

	;;
	*)
	echo "wrong BUILD_ON"
	exit 1
	;;
	esac
}

jenkins_api