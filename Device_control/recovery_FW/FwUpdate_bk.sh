#!/bin/bash
# 2021-05-27 Create by JerryShih
# 2021-10-15 Add newbios type for support new HII
# 2023-06-29 Support new FirmwareInventory target

ARGUNUM=$#
TYPE=$1
IP=$2
FW_IMAGE=$(cd "$(dirname "$3")"; pwd)/$(basename "$3")

PY=python3

# -- USER/PWD --
NAME=ADMIN
PASSWORD=ADMIN
cred="$( echo -n $NAME:$PASSWORD | base64 )"

# OnStartUpdateRequest or Immediate
APPLYTIME=Immediate
#APPLYTIME=OnStartUpdateRequest
#Note: POST /redfish/v1/UpdateService/Actions/UpdateService.StartUpdate to start update

# -- BMC Parameters --
PRESERVE_CFG=false
PRESERVE_SDR=false
PRESERVE_SSL=false
BACKUP_BMC=false

# -- BIOS Parameters --
PRESERVE_SMBIOS=true
PRESERVE_ME=true
PRESERVE_NVRAM=true
BACKUP_BIOS=false

# -- New BIOS Parameters --
PRESERVE_SMBIOS_N=true
PRESERVE_OA=true
PreserveSETUPCONF=true
PreserveSETUPPWD=true
PreserveSECBOOTKEY=true
PreserveBOOTCONF=true

# -- Target --
BMC_TARGET="/redfish/v1/Managers/1"
BIOS_TARGET="/redfish/v1/Systems/1/Bios"
BMC_FWIT_TARGET="/redfish/v1/UpdateService/FirmwareInventory/BMC"
BIOS_FWIT_TARGET="/redfish/v1/UpdateService/FirmwareInventory/BIOS"

usage () {
    echo -e ""
    echo -e "Usage:"
    echo -e ""
    echo -e "   FwUpdate.sh (bmc|bios|newbios|cpld) IP_ADDR FW_IMAGE"
    echo -e ""
}

if [ $ARGUNUM != 3 ]; then
    usage
    exit
fi

if expr "$IP" : '[0-9][0-9]*\.[0-9][0-9]*\.[0-9][0-9]*\.[0-9][0-9]*$' >/dev/null; then
    for i in 1 2 3 4; do
        if [ $(echo "$IP" | cut -d. -f$i) -gt 255 ]; then
            echo "Invalid IP ($IP)"
            usage
            exit
        fi
    done
else
    echo "Invalid IP ($IP)"
    usage
    exit
fi

echo [$TYPE] [$IP] [$cred] [$FW_IMAGE]

#BMC
if [ $TYPE == "bmc" ]; then
    echo  -e "\e[38;5;120m BMC FW image upload...\e[0m"
    curl \
    -k -X POST https://$IP/redfish/v1/UpdateService/upload \
    -H "Authorization: Basic $cred" \
    -H 'cache-control: no-cache' \
    -H 'content-type: multipart/form-data' \
    -F UpdateFile=@//$FW_IMAGE \
    -F "UpdateParameters={
            \"Targets\":[\"$BMC_TARGET\"],
            \"@Redfish.OperationApplyTime\":\"$APPLYTIME\",
            \"Oem\":{\"Supermicro\": {\"BMC\": {
                                       \"PreserveCfg\": $PRESERVE_CFG,
                                       \"PreserveSdr\": $PRESERVE_SDR,
                                       \"PreserveSsl\": $PRESERVE_SSL,
                                       \"BackupBMC\": $BACKUP_BMC
                                     }}}
       }" \
    | $PY -m json.tool

    echo ""
    exit;
fi

#BIOS
if [ $TYPE == "bios" ]; then
    echo  -e "\e[38;5;120m BIOS FW image upload...\e[0m"
    curl \
    -k -X POST https://$IP/redfish/v1/UpdateService/upload \
    -H "Authorization: Basic $cred" \
    -H 'cache-control: no-cache' \
    -H 'content-type: multipart/form-data' \
    -F UpdateFile=@//$FW_IMAGE \
    -F "UpdateParameters={
            \"Targets\":[\"$BIOS_TARGET\"],
            \"@Redfish.OperationApplyTime\":\"$APPLYTIME\",
            \"Oem\":{\"Supermicro\": {\"BIOS\": {
                                        \"PreserveME\": $PRESERVE_ME,
                                        \"PreserveNVRAM\": $PRESERVE_NVRAM,
                                        \"PreserveSMBIOS\": $PRESERVE_SMBIOS,
                                        \"BackupBIOS\": $BACKUP_BIOS
                                     }}}
        }" \
    | $PY -m json.tool

    echo ""
    exit;
fi

#New BIOS (New HII)
if [ $TYPE == "newbios" ]; then
    echo  -e "\e[38;5;120m New BIOS FW image upload...\e[0m"
    curl \
    -k -X POST https://$IP/redfish/v1/UpdateService/upload \
    -H "Authorization: Basic $cred" \
    -H 'cache-control: no-cache' \
    -H 'content-type: multipart/form-data' \
    -F UpdateFile=@//$FW_IMAGE \
    -F "UpdateParameters={
            \"Targets\":[\"$BIOS_TARGET\"],
            \"@Redfish.OperationApplyTime\":\"$APPLYTIME\",
            \"Oem\":{\"Supermicro\": {\"BIOS\": {
                                        \"PreserveME\": $PRESERVE_ME,
                                        \"PreserveNVRAM\": $PRESERVE_NVRAM,
                                        \"PreserveSMBIOS\": $PRESERVE_SMBIOS_N,
                                        \"BackupBIOS\": $BACKUP_BIOS,
                                        \"PreserveOA\": $PRESERVE_OA,
                                        \"PreserveSETUPCONF\": $PreserveSETUPCONF,
                                        \"PreserveSETUPPWD\": $PreserveSETUPPWD,
                                        \"PreserveSECBOOTKEY\": $PreserveSECBOOTKEY,
                                        \"PreserveBOOTCONF\": $PreserveBOOTCONF
                                    }}}}" \
    | $PY -m json.tool

    echo ""
    exit;
fi

#CPLD
if [ $TYPE == "cpld" ]; then
    echo  -e "\e[38;5;120m CPLD FW image upload...\e[0m"
    curl \
    -k -X POST https://$IP/redfish/v1/UpdateService/upload \
    -H "Authorization: Basic $cred" \
    -H 'cache-control: no-cache' \
    -H 'content-type: multipart/form-data' \
    -F UpdateFile=@//$FW_IMAGE \
    -F 'UpdateParameters={
            "Targets":["/redfish/v1/UpdateService/FirmwareInventory/CPLD_Motherboard"],
            "@Redfish.OperationApplyTime":"Immediate"
        }' \
    | $PY -m json.tool

    echo ""
    exit;
fi

usage
echo ""
