<?php
/**
 * User : wujian
 * Notice : 用户优惠券查询
 */
namespace Models;

use \Phalcon\Di;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Models\NoticeModel;
use Models\ScenarioModel;
use Models\CouponModel;

class UserCouponModel extends BaseModel
{
    public static $tableName = 'cp_user_coupons';

    CONST CACHE_CONNECTION = 'cache_coupon';

    CONST CACHE_KEY_USER_COUPONS = 'user_coupon:%s:%s';                           // userId:status
    CONST CACHE_KEY_USER_COUPON_BY_ORDERID = 'user_coupon_by_orderid:%s';       // userId:orderId
    CONST CACHE_KEY_USER_COUPON_BY_SCENARIO_VALUE = 'user_coupon_by_scenarid_value:%s';   // userId:code:value
    CONST CACHE_KEY_USER_COUPON_BY_SCENARIO_UID = 'user_coupon_by_scenarid_uid:%s';       // userId:code

    CONST K_USED = 'USED';
    CONST K_ACTIVED = 'ACTIVED';
    CONST K_STARTED = 'STARTED';
    CONST K_FINISHED = 'FINISHED';

    CONST V_YES = 1;
    CONST V_NO = 0;
    CONST V_NONEED = 2;

    private static $real_keys = [
        '1012',
        '1022'
    ];

    /*private static $sql_template = "SELECT 
uc.id AS user_coupon_id,
uc.user_id,
uc.coupon_id,
uc.created_at,
uc.used,
uc.actived,
uc.scenario_code,
uc.scenario_value,
cp.name,
cp.description,
uc.order_id,
cp.cover,
cp.ttl,
cp.amount,
cp.is_discount,
cp.started_at,
cp.finished_at
FROM cp_user_coupons AS uc 
INNER JOIN cp_coupon AS cp 
ON uc.coupon_id = cp.id  ";*/

    private static $sql_template = "SELECT 
cp.id AS coupon_id,
uc.id AS user_coupon_id,
uc.user_id,
uc.coupon_id,
uc.created_at,
uc.used,
uc.actived,
uc.scenario_code,
uc.scenario_value,
uc.order_id
FROM cp_user_coupons AS uc 
INNER JOIN cp_coupon AS cp 
ON uc.coupon_id = cp.id  ";

    protected static $_inst = NULL;

    public function initialize()
    {
        $this->setReadConnectionService('db_coupon_slave');
        $this->setWriteConnectionService('db_coupon');
    }

    public function getSource()
    {
        return 'cp_user_coupons';
    }

    public static function getInstance()
    {
        if (self::$_inst == NULL) {
            self::$_inst = new UserCouponModel();
        }
        return self::$_inst;
    }

    // ! 清除指定UID的所有優惠券
    public static function clearUserCache($userId)
    {
        // 清空根據用戶UID查詢的緩存
        foreach (self::$real_keys as $k => $status) {
            $cacheKey = sprintf(self::CACHE_KEY_USER_COUPONS, $userId, $status);
            Di::getDefault()->get(self::CACHE_CONNECTION)->destroy($cacheKey);
        }

        // 清空根據場景回溯的緩存
        Di::getDefault()->get(self::CACHE_CONNECTION)->destroy(sprintf(self::CACHE_KEY_USER_COUPON_BY_ORDERID, $userId));
        Di::getDefault()->get(self::CACHE_CONNECTION)->destroy(sprintf(self::CACHE_KEY_USER_COUPON_BY_SCENARIO_VALUE, $userId));
        Di::getDefault()->get(self::CACHE_CONNECTION)->destroy(sprintf(self::CACHE_KEY_USER_COUPON_BY_SCENARIO_UID, $userId));
    }

    private function buildWheres($statusParams)
    {
        $wheres = '';
        switch ($statusParams[self::K_USED]) {
            case self::V_YES:
                $wheres .= 'uc.used = 1';
                break;
            case self::V_NO:
                $wheres .= 'uc.used = 0';
                break;
            case self::V_NONEED:
            default:
                $wheres .= '1=1';
                break;
        }

        $wheres .= ' AND ';

        switch ($statusParams[self::K_ACTIVED]) {
            case self::V_YES:
                $wheres .= 'uc.actived = 1';
                break;
            case self::V_NO:
                $wheres .= 'uc.actived = 0';
                break;
            case self::V_NONEED:
            default:
                $wheres .= '1=1';
                break;
        }

        $wheres .= ' AND ';

        $now = date("Y-m-d H:i:s");
        switch ($statusParams[self::K_STARTED]) {
            case self::V_YES:
                $wheres .= "cp.started_at <='" . $now . "'";     // 已开始
                break;
            case self::V_NO:
                $wheres .= "cp.started_at >'" . $now . "'";      // 未开始
                break;
            case self::V_NONEED:
            default:
                $wheres .= '1=1';
                break;
        }

        $wheres .= ' AND ';

        switch ($statusParams[self::K_FINISHED]) {
            case self::V_YES:
                $wheres .= "cp.finished_at <='" . $now . "'";     // 已结束
                break;
            case self::V_NO:
                $wheres .= "cp.finished_at >'" . $now . "'";      // 未结束
                break;
            case self::V_NONEED:
            default:
                $wheres .= '1=1';
                break;
        }

        return $wheres;
    }

    private function queryBySql($sql, $cacheKey, $hashKey = '')
    {
        $records = $this->getReadConnection()->query($sql);
        $resultSet = new Resultset(NULL, $this, $records);

        $userCoupons = [];
        foreach ($resultSet as $row) {
            $hashField = $row->user_coupon_id;
            $userCoupons[$hashField] = [
                'user_id' => $row->user_id,
                'user_coupon_id' => $row->user_coupon_id,
                'coupon_id' => $row->coupon_id,
                'actived' => $row->actived,
                'used' => $row->used,
                'created_at' => $row->created_at
            ];
        }

        if (empty($userCoupons)) {
            Di::getDefault()->get(self::CACHE_CONNECTION)->hashMultiSet($cacheKey, ['empty' => 1]);
        } else {
            if (empty($hashKey)) {
                Di::getDefault()->get(self::CACHE_CONNECTION)->hashMultiSet($cacheKey, $userCoupons);
            } else {
                Di::getDefault()->get(self::CACHE_CONNECTION)->hashSet($cacheKey, $hashKey, $userCoupons);
            }
        }
        Di::getDefault()->get(self::CACHE_CONNECTION)->expire($cacheKey, \Cache\MyRedis::TODAY_TTL);

        return $userCoupons;
    }

    private function genStatusCacheInOrder($statusParams)
    {
        $str = '';
        $params = $statusParams;

        $params[self::K_ACTIVED] = array_key_exists(self::K_ACTIVED, $params) ? $params[self::K_ACTIVED] : self::V_NONEED;
        $params[self::K_USED] = array_key_exists(self::K_USED, $params) ? $params[self::K_USED] : self::V_NONEED;
        $params[self::K_STARTED] = array_key_exists(self::K_STARTED, $params) ? $params[self::K_STARTED] : self::V_NONEED;
        $params[self::K_FINISHED] = array_key_exists(self::K_FINISHED, $params) ? $params[self::K_FINISHED] : self::V_NONEED;

        $str .= $params[self::K_ACTIVED] . $params[self::K_USED] . $params[self::K_STARTED] . $params[self::K_FINISHED];
        return $str;
    }


    public function getCouponOfScenario($userId, $scenarioCode, $scenarioValue)
    {   
        if(intval($userId)<1 || !$scenarioCode){
            return [];
        }

        $hashKey = $scenarioCode . ':' . $scenarioValue;
        $cacheKey = sprintf(self::CACHE_KEY_USER_COUPON_BY_SCENARIO_VALUE, $userId);
        $userCoupons = Di::getDefault()->get(self::CACHE_CONNECTION)->hashGet($cacheKey, $hashKey, TRUE);

        if (isset($userCoupons['empty'])) {
            return [];
        }

        if (!$userCoupons) {
            $sql = self::$sql_template . " WHERE uc.user_id = {$userId} AND uc.scenario_code ='{$scenarioCode}' AND uc.scenario_value ='{$scenarioValue}'";
                // 排序
            $sql .= ' order by uc.id desc';
            $userCoupons = $this->queryBySql($sql, $cacheKey, $hashKey);  // 会设置缓存
        } 

        return $this->verifyUserCouponsByCache($userCoupons);
    }

    protected function verifyUserCouponsByCache($userCoupons){
        $verifiedUserCoupons = [];
        foreach ($userCoupons as $userCouponId => $userCoupon) {
            $couponId = $userCoupon['coupon_id'];
            $couponCacheKey = CouponModel::CACHE_KEY_COUPONS_ONLINE;
            $coupon = Di::getDefault()->get(self::CACHE_CONNECTION)->hashGet($couponCacheKey,$couponId,TRUE);
            if(empty($coupon)){ // 当前优惠券并不存在于缓存中（比如被清缓存）,则去数据库中找最新的
                $coupon = CouponModel::queryOnlineCouponById($couponId);
                if(empty($coupon)){
                    // 仍然为空，说明优惠券已下线（online=0），那么这一条优惠券就不返回
                    continue;
                }
                Di::getDefault()->get(self::CACHE_CONNECTION)->hashSet($couponCacheKey, $couponId,$coupon);
            }

            // 针对 绝对有效期/相对有效期 的优惠券, 设置其到期时间: left_time
            if (empty($coupon['ttl'])) {
                // ttl为空，优惠券到期时间为绝对时间
                $userCoupon['left_time'] = strtotime($coupon['finished_at']);
            } else {
                // ttl不为空，按相对时间算（领取日期+生命周期）
                $userCoupon['left_time'] = strtotime($userCoupon['created_at']) + intval($coupon['ttl']);
            }

            // 合并数据用于返回
            $verifiedUserCoupons[$userCouponId] = [
                'user_id' => $userCoupon['user_id'],
                'user_coupon_id' => $userCoupon['user_coupon_id'],
                'coupon_id' => $userCoupon['coupon_id'],
                'actived' => $userCoupon['actived'],
                'used' => $userCoupon['used'],
                'created_at' => $userCoupon['created_at'],
                'name' => $coupon['name'],
                'description' => $coupon['description'],
                'cover' => $coupon['cover'],
                'amount' => $coupon['amount'],
                'is_discount' => $coupon['is_discount'],
                'started_at' => $coupon['started_at'],
                'finished_at' => $coupon['finished_at'],
                'left_time' => $userCoupon['left_time']
            ];
        }
        return $verifiedUserCoupons;
    }

    public function getCouponOfScenarioByUid($userId, $scenarioCode)
    {

        if(intval($userId)<1 || !$scenarioCode){
            return [];
        }

        $cacheKey = sprintf(self::CACHE_KEY_USER_COUPON_BY_SCENARIO_UID, $userId);
        $userCoupons = Di::getDefault()->get(self::CACHE_CONNECTION)->hashGet($cacheKey, $scenarioCode, TRUE);
        if (isset($userCoupons['empty'])) {
            return [];
        }

        if (!$userCoupons) {
            $sql = self::$sql_template . " WHERE uc.user_id = {$userId} AND uc.scenario_code ='{$scenarioCode}'";
            $sql .= ' order by uc.id desc';
            $userCoupons = $this->queryBySql($sql, $cacheKey);  // 会设置缓存
        } 

        return $this->verifyUserCouponsByCache($userCoupons);
    }

    public function getCouponDetailByOrderId($userId, $orderId)
    {
        if(intval($userId)<1){
            return [];
        }

        $cacheKey = sprintf(self::CACHE_KEY_USER_COUPON_BY_ORDERID, $userId);
        $userCoupons = Di::getDefault()->get(self::CACHE_CONNECTION)->hashGet($cacheKey, $orderId, TRUE);

        if (!$userCoupons) {
            $sql = self::$sql_template . " WHERE uc.user_id = {$userId} AND uc.order_id ='{$orderId}'";
            $userCoupons = $this->queryBySql($sql, $cacheKey, $orderId);  // 会设置缓存
        }

        return $this->verifyUserCouponsByCache($userCoupons);
    }

    public function getUserCouponsByStatus($userId, $statusParams = NULL)
    {
        if(intval($userId)<1 || NULL == $statusParams){
            return [];
        }

        $cacheKey = sprintf(self::CACHE_KEY_USER_COUPONS, $userId, $this->genStatusCacheInOrder($statusParams));
        $userCoupons = Di::getDefault()->get(self::CACHE_CONNECTION)->hashGetAll($cacheKey);
        if (isset($userCoupons['empty'])) {
            return [];
        }

        if (!$userCoupons) {
            $wheres = $this->buildWheres($statusParams) . " AND cp.online=1 AND uc.user_id={$userId}";
            $sql = self::$sql_template . " WHERE {$wheres}";
            $userCoupons = $this->queryBySql($sql, $cacheKey);  // 会设置缓存
        } 

        return $this->verifyUserCouponsByCache($userCoupons);
    }

    public function sendCoupons($userId, $couponList, $scenarioCode, $scenarioValue)
    {
        $success = TRUE;

        if (empty($couponList) || empty($scenarioCode)) {
            return FALSE;
        }

        if (intval($userId) <= 0) {
            return FALSE;
        }

        if (empty($scenarioValue)) {
            $scenarioValue = '-';
        }

        $active = 1;    // 默认自动激活！
        $scenario = ScenarioModel::getDetail($scenarioCode);
        if (!empty($scenario) && isset($scenario['active'])) {
            $active = intval($scenario['active']);     // 是否自动激活
        }

        foreach ($couponList as $key => $couponId) {
            $this->getWriteConnection()->begin();
            try {
                $newUserCoupon = new UserCouponModel();
                $newUserCoupon->user_id = intval($userId);
                $newUserCoupon->coupon_id = intval($couponId);
                $newUserCoupon->used = 0;
                $newUserCoupon->order_id = 0;
                $newUserCoupon->actived = $active;
                $newUserCoupon->scenario_code = $scenarioCode;
                $newUserCoupon->scenario_value = $scenarioValue;

                if ($newUserCoupon->save() == FALSE) {
                    // 插入失败
                    \Phalcon\DI::getDefault()->get('log')->error('insert user coupon failed\nUserId:' . $userId . ' Coupons:' . json_encode($couponId));
                    $success = FALSE;
                    $this->getWriteConnection()->rollback();
                    break;
                } else {
                    // 插入成功，继续插入Notice
                    $newNotice = new NoticeModel();
                    $newNotice->day = intval(date("Ymd"));
                    $newNotice->coupon_id = intval($couponId);
                    $newNotice->user_id = intval($userId);
                    //$newNotice->user_id = 'TEXT'; // 造成插入失败  for test
                    if ($newNotice->save() == FALSE) {
                        // 插入失败
                        \Phalcon\DI::getDefault()->get('log')->error('insert user coupon failed\nUserId:' . $userId . ' Coupons:' . json_encode($couponId));
                        $success = FALSE;
                        $this->getWriteConnection()->rollback();
                        break;
                    }
                }

                $this->getWriteConnection()->commit();
            } catch (Exception $e) {
                \Phalcon\DI::getDefault()->get('log')->error($e->getMessage());
                $this->getWriteConnection()->rollback();
                return FALSE;
            }
        }
        return $success;
    }

    /**
     * @desc 更新优惠券
     * @param $userCouponId
     * @param $orderId
     * @return array
     */
    public static function setCouponUsed($userCouponId, $orderId = 0)
    {
        if (intval($userCouponId)) {
            $record = [
                'used' => 1,
                'order_id' => $orderId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $where = [
                'id' => $userCouponId,
            ];
            return self::updateRecord($record, $where);

        } else {
            return FALSE;
        }
    }

    /**
     * @desc 根据用户ID 和订单ID恢复优惠券
     * @param $userId
     * @param $orderId
     * @return bool
     */
    public static function setCouponRestoredWithUserIdAndOrderId($userId, $orderId)
    {
        if ($userId && $orderId) {
            $record = [
                'used' => 0,
                'order_id' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $where = [
                'user_id' => $userId,
                'order_id' => $orderId,
            ];
            return self::updateRecord($record, $where);

        } else {
            return FALSE;
        }
    }

    /**
     * @desc 根据优惠券ID恢复优惠券
     * @param $userId
     * @param $userCouponId
     * @return bool
     */
    public static function setCouponActived($userId, $userCouponId)
    {
        if ($userId && $userCouponId) {
            $record = [
                'actived' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $where = [
                'user_id' => $userId,
                'id' => $userCouponId,
            ];
            return self::updateRecord($record, $where);

        } else {
            return FALSE;
        }
    }
}
