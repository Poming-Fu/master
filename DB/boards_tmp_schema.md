# boards_tmp 資料表建立

在其他資料庫上執行以下 SQL 來建立 `boards_tmp` 資料表。

## 建立資料表

```sql
CREATE TABLE IF NOT EXISTS boards_tmp (
    b_id        VARCHAR(10)                        NOT NULL PRIMARY KEY,
    b_name      VARCHAR(60)                        DEFAULT NULL,
    guid        VARCHAR(10)                        DEFAULT NULL,
    pbid        INT(11)                            DEFAULT NULL,
    pbid_oem    INT(11)                            DEFAULT NULL,
    bmc_chip    VARCHAR(20)                        DEFAULT NULL,
    bmc_type    ENUM('legacybmc','openbmc')         DEFAULT NULL,
    rot_pfr     VARCHAR(20)                        DEFAULT NULL,
    redfish     VARCHAR(20)                        DEFAULT NULL,
    target      VARCHAR(20)                        DEFAULT NULL,
    fw_size     VARCHAR(10)                        DEFAULT NULL,
    owner       VARCHAR(30)                        DEFAULT NULL,
    gitlab_type VARCHAR(20)                        DEFAULT NULL,
    gitlab_id   INT(11)                            DEFAULT NULL,
    notes       VARCHAR(30)                        DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## 確認

```sql
DESCRIBE boards_tmp;
SELECT COUNT(*) FROM boards_tmp;
```
