<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

use shopack\base\common\db\Migration;

class m231219_150555_aaa_convert_vchItems extends Migration
{
  public function safeUp()
  {
    $this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
  CHANGE COLUMN `vchItems` `OLD_vchItems` JSON NULL DEFAULT NULL AFTER `vchTotalPaid`;
SQLSTR
    );

    $this->execute(<<<SQLSTR
ALTER TABLE `tbl_AAA_Voucher`
  ADD COLUMN `vchItems` JSON NULL DEFAULT NULL AFTER `OLD_vchItems`;
SQLSTR
    );
    $this->alterColumn('tbl_AAA_Voucher', 'vchItems', $this->json());

    $this->execute(<<<SQLSTR
UPDATE tbl_AAA_Voucher
  INNER JOIN (
    SELECT tbl_AAA_Voucher.vchID
        , JSON_ARRAYAGG(JSON_REMOVE(JSON_OBJECT(
            "service",       tmp1.service
          , "key",           tmp1.key
          , "slbID",         tmp1.slbID
          , "desc",          tmp1.desc
          , "qty",           tmp1.qty
          , "unit",          tmp1.unit
          , "prdType",       tmp1.prdType
          , "unitPrice",     tmp1.unitPrice
          , "params",        tmp1.params
          , "maxQty",        tmp1.maxQty
          , "qtyStep",       tmp1.qtyStep
          , "discount",      IFNULL(tmp1.discount, 0)
          , "subTotal",      tmp1.qty * tmp1.unitPrice
          , "afterDiscount", tmp1.qty * tmp1.unitPrice
          , "totalPrice",    tmp1.qty * tmp1.unitPrice
        )
        , CASE WHEN tmp1.service   IS NULL OR CONCAT(tmp1.service  , '') IN ('', '0') THEN '$.service'   ELSE '$.dummy' END
        , CASE WHEN tmp1.key       IS NULL OR CONCAT(tmp1.key      , '') IN ('', '0') THEN '$.key'       ELSE '$.dummy' END
        , CASE WHEN tmp1.slbID     IS NULL OR CONCAT(tmp1.slbID    , '') IN ('', '0') THEN '$.slbID'     ELSE '$.dummy' END
        , CASE WHEN tmp1.desc      IS NULL OR CONCAT(tmp1.desc     , '') IN ('', '0') THEN '$.desc'      ELSE '$.dummy' END
        , CASE WHEN tmp1.qty       IS NULL OR CONCAT(tmp1.qty      , '') IN ('', '0') THEN '$.qty'       ELSE '$.dummy' END
        , CASE WHEN tmp1.unit      IS NULL OR CONCAT(tmp1.unit     , '') IN ('', '0') THEN '$.unit'      ELSE '$.dummy' END
        , CASE WHEN tmp1.prdType   IS NULL OR CONCAT(tmp1.prdType  , '') IN ('', '0') THEN '$.prdType'   ELSE '$.dummy' END
        , CASE WHEN tmp1.unitPrice IS NULL OR CONCAT(tmp1.unitPrice, '') IN ('', '0') THEN '$.unitPrice' ELSE '$.dummy' END
        , CASE WHEN tmp1.params    IS NULL OR CONCAT(tmp1.params   , '') IN ('', '0') THEN '$.params'    ELSE '$.dummy' END
        , CASE WHEN tmp1.maxQty    IS NULL OR CONCAT(tmp1.maxQty   , '') IN ('', '0') THEN '$.maxQty'    ELSE '$.dummy' END
        , CASE WHEN tmp1.qtyStep   IS NULL OR CONCAT(tmp1.qtyStep  , '') IN ('', '0') THEN '$.qtyStep'   ELSE '$.dummy' END
        , CASE WHEN tmp1.discount  IS NULL OR CONCAT(tmp1.discount , '') IN ('', '0') THEN '$.discount'  ELSE '$.dummy' END
      )) AS NEW_vchItems
    FROM tbl_AAA_Voucher
      , JSON_TABLE(OLD_vchItems,
      '$[*]' COLUMNS (i FOR ORDINALITY
        , service   VARCHAR(1024) PATH '$.service'
        , `key`     VARCHAR(1024) PATH '$.key'
        , slbID     INT           PATH '$.slbid'
        , `desc`    VARCHAR(1024) PATH '$.desc'
        , qty       INT           PATH '$.qty'
        , unit      VARCHAR(1024) PATH '$.unit'
        , prdType   VARCHAR(1024) PATH '$.prdtype'
        , unitPrice INT           PATH '$.unitprice'
        , params    VARCHAR(1024) PATH '$.slbinfo'
        , maxQty    INT           PATH '$.maxqty'
        , qtyStep   INT           PATH '$.qtystep'
        , discount  INT           PATH '$.discount'
      )) AS tmp1
    WHERE vchType='B'
      AND JSON_LENGTH(IFNULL(OLD_vchItems, '[]')) > 0
    GROUP BY tbl_AAA_Voucher.vchID
  ) AS tmpJson
  ON tmpJson.vchID = tbl_AAA_Voucher.vchID
  SET vchItems = tmpJson.NEW_vchItems
;
SQLSTR
    );

  }

  public function safeDown()
  {
    echo "m231219_150555_aaa_convert_vchItems cannot be reverted.\n";
    return false;
  }

}
