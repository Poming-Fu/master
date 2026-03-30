#!/bin/bash
# gen_rel_note.sh - 產生 release note 並存檔
# 參數: build_type build_mode branch platform ver option oemname b_id board_name bmc_type guid pbid gitlab_type gitlab_id

MP_PASSWORD="$1"
BUILD_TYPE="$2"
BUILD_MODE="$3"
BRANCH="$4"
PLATFORM="$5"
VER="$6"
OPTION="$7"
OEMNAME="$8"
B_ID="$9"
BOARD_NAME="${10}"
BMC_TYPE="${11}"
GUID="${12}"
PBID="${13}"
GITLAB_TYPE="${14}"
GITLAB_ID="${15}"

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
FILENAME="release_note_${PLATFORM}_${VER}.txt"
TMP_PATH="${SCRIPT_DIR}/../tmp/${FILENAME}"
RELEASE_DIR="/mnt/DB/release/${BMC_TYPE}"

# 產生 release note 內容
cat > "$TMP_PATH" <<EOF
========================================
  Release Note
========================================
Build Type:    ${BUILD_TYPE}
Build Mode:    ${BUILD_MODE}
Branch:        ${BRANCH}
Platform:      ${PLATFORM}
Version:       ${VER}
Option:        ${OPTION}
OEM Name:      ${OEMNAME}
----------------------------------------
Board ID:      ${B_ID}
Board Name:    ${BOARD_NAME}
BMC Type:      ${BMC_TYPE}
GUID:          ${GUID}
PBID:          ${PBID}
Gitlab Type:   ${GITLAB_TYPE}
Gitlab ID:     ${GITLAB_ID}
========================================
EOF

# 複製到 release 目錄 (NAS 需要 sudo)
if [ -d "$RELEASE_DIR" ]; then
    echo "$MP_PASSWORD" | sudo -S cp "$TMP_PATH" "${RELEASE_DIR}/${FILENAME}"
fi

# 輸出內容供 PHP 讀取
cat "$TMP_PATH"
