#!/bin/bash
#baber 2024/02/22 add lbmc surport
#baber 2024/09/04 add obmc surport
#For Fw_release_build web 


USER="baber:baber"
BRANCH_NAME="master"
BUILD_TARGET="sx12_ast26_p"
SOC_NAME="AST2600"
RELEASE_FW_VER="01.09.09"
#EMAIL_RECIPIENTS='BaberF@supermicro.com Sam_lee@supermicro.com'
EMAIL_RECIPIENTS=$1
DO_AUTOMATION="NO"
RUN_COMBINEDECO_SAA=False
#OEM_NAME=$6
#UUID=$7


function jenkins_api() {
	#open session , output all data
	api_output=$(curl -verbose  -s 'http://10.148.20.12:8080/crumbIssuer/api/json' --user ${USER} 2>&1)
	echo $api_output
	
	#提取crumb
	CRUMB=$(echo "$api_output" | awk -F '"' '/"crumb":/ {print $8}')
	echo $CRUMB
	
	#提取cookie
	COOKIE=$(echo "$api_output" | awk -F '[:; ]+' '/Set-Cookie: JSESSIONID/ {print$3}')
	echo $COOKIE
	#jenkins server build 10.148.21.21

	#判斷BUILD_ON sx12 sx13 sh12 sh13 判斷 
	if [[ $BUILD_TARGET == *sx12* || $BUILD_TARGET == *sx13* || $BUILD_TARGET == *sh12* || $BUILD_TARGET == *sh13* || $BUILD_TARGET == *h13* || $BUILD_TARGET == *h12* || $BUILD_TARGET == *h11* || $BUILD_TARGET == *x12* || $BUILD_TARGET == *x13* ]]; then
		BUILD_ON="lbmc_rel"
	else
		BUILD_ON="obmc_rel"
	fi

	case $BUILD_ON in
	lbmc_rel)
	curl -s -X POST --max-time 5 --cookie "${COOKIE}" -H "Jenkins-Crumb:${CRUMB}" \
		http://10.148.20.12:8080/job/x12_release/buildWithParameters \
		--user $USER \
		-F BRANCH_NAME=$BRANCH_NAME \
		-F BUILD_TARGET=$BUILD_TARGET \
		-F RELEASE_FW_VER=$RELEASE_FW_VER \
		-F SOC_NAME=$SOC_NAME \
		-F "EMAIL_RECIPIENTS=$EMAIL_RECIPIENTS" \
		-F DO_AUTOMATION=$DO_AUTOMATION \
		-F RUN_COMBINEDECO_SAA=$RUN_COMBINEDECO_SAA
	
	;;
	obmc_rel)
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