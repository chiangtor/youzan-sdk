<?php
/**
 * Created by PhpStorm.
 * User: imhui
 * Date: 16/3/7
 * Time: 17:30
 */

namespace Youzan\Service;

use Youzan\Model\GoodsDetail;
use Youzan\Model\GoodsSku;
use Youzan\Model\ModelFactory;
use Youzan\Service\Parameters\GoodsParamters;

class GoodsService extends BaseService
{
    /**
     * @param int $page_no
     * @param int $page_size
     * @param null $q
     * @param string $order_by
     * @return array|null
     */
    public function itemsOnsaleGet($page_no = 1, $page_size = 20, $q = null, $order_by = 'created:desc')
    {
        $method = 'kdt.items.onsale.get';
        $params = array(
            'page_no' => $page_no,
            'page_size' => $page_size,
            'order_by' => $order_by
        );

        if (!is_null($q)) {
            $params['q'] = $q;
        }

        $response = $this->get($method, $params);
        if ($this->isResponseError($response)) {
            return null;
        }

        $total = $response['response']['total_results'];
        $list = ModelFactory::objectListFromData($response['response']['items'], GoodsDetail::class);
        return array($list, $total);

    }

    /**
     * @param int $page_no
     * @param int $page_size
     * @param null $q
     * @param null $banner
     * @param null $tag_id
     * @param string $order_by
     * @return array|null
     */
    public function itemsInventoryGet($page_no = 1, $page_size = 20, $q = null, $banner = null, $tag_id = null, $order_by = 'created:desc')
    {
        $method = 'kdt.items.inventory.get';
        $params = array(
            'page_no' => $page_no,
            'page_size' => $page_size,
            'order_by' => $order_by
        );

        if (!is_null($q)) {
            $params['q'] = $q;
        }

        if (!is_null($banner)) {
            $params['banner'] = $banner;
        }

        if (!is_null($tag_id)) {
            $params['tag_id'] = $tag_id;
        }

        $response = $this->get($method, $params);
        if ($this->isResponseError($response)) {
            return null;
        }

        $total = $response['response']['total_results'];
        $list = ModelFactory::objectListFromData($response['response']['items'], GoodsDetail::class);
        return array($list, $total);
    }

    /**
     * @param $outer_id
     * @return array|null
     */
    public function itemsCustomGet($outer_id)
    {
        $method = 'kdt.items.custom.get';
        $params = array(
            'outer_id' => $outer_id,
        );

        $response = $this->get($method, $params);
        if ($this->isResponseError($response)) {
            return null;
        }

        $list = ModelFactory::objectListFromData($response['response']['items'], GoodsDetail::class);
        return $list;
    }


    /**
     * @param $num_iid int
     * @return bool
     */
    public function itemGet($num_iid)
    {
        $method = 'kdt.item.get';

        $params = array(
            'num_iid' => $num_iid
        );

        $response = $this->get($method, $params);
        if ($this->isResponseError($response)) {
            return null;
        }

        return ModelFactory::objectFromData($response['response']['item'], GoodsDetail::class);
    }

    /**
     * @param GoodsParamters $parameters
     * @return bool|null|GoodsDetail
     */
    public function itemAdd(GoodsParamters $parameters)
    {
        $method = 'kdt.item.add';

        $params = array();
        foreach (GoodsParamters::allKeys() as $key) {
            if (!is_null($parameters->$key)) {
                $params[$key] = $parameters->$key;
            }
        }

        $filenames = $this->saveRemoteImages($parameters->images);

        $files = array();
        foreach ($filenames as $filename) {
            $files[] = [
                'url' => $filename,
                'field' => 'images[]',
            ];
        }
        $response = $this->post($method, $params, $files);
        $this->removeFiles($filenames);

        if ($this->isResponseError($response)) {
            return false;
        }
        return ModelFactory::objectFromData($response['response']['item'], GoodsDetail::class);

    }

    /**
     * @param $num_iid
     * @param GoodsParamters $parameters
     * @return bool|null|GoodsDetail
     */
    public function itemUpdate($num_iid, GoodsParamters $parameters)
    {
        $method = 'kdt.item.update';

        $params = array(
            'num_iid' => $num_iid,
            'tag_ids' => $parameters->tag_ids,
            'price' => $parameters->price,
            'title' => $parameters->title,
            'desc' => $parameters->desc,
            'keep_item_img_ids' => $parameters->keep_item_img_ids,
            'post_fee' => $parameters->post_fee,
            'skus_with_json' => $parameters->skus_with_json,
            'origin_price' => $parameters->origin_price,
            'buy_url' => $parameters->buy_url,
            'outer_id' => $parameters->outer_id,
            'buy_quota' => $parameters->buy_quota,
            'quantity' => $parameters->quantity,
            'hide_quantity' => $parameters->hide_quantity,
            'auto_listing_time' => $parameters->auto_listing_time,
            'join_level_discount' => $parameters->join_level_discount
        );
        if ($parameters->cid) {
            $params['cid'] = $parameters->cid;
        }
        if ($parameters->promotion_cid) {
            $params['promotion_cid'] = $parameters->promotion_cid;
        }

        $filenames = $this->saveRemoteImages($parameters->images);

        $files = array();
        foreach ($filenames as $filename) {
            $files[] = [
                'url' => $filename,
                'field' => 'images[]',
            ];
        }
        $response = $this->post($method, $params, $files);
        $this->removeFiles($filenames);
        if ($this->isResponseError($response)) {
            return false;
        }
        return ModelFactory::objectFromData($response['response']['item'], GoodsDetail::class);

    }

    /**
     * @param $num_iid
     * @return bool
     */
    public function itemDelete($num_iid)
    {
        $method = 'kdt.item.delete';

        $params = array(
            'num_iid' => $num_iid
        );

        $response = $this->post($method, $params);
        if ($this->isResponseError($response)) {
            return false;
        }
        return $response['response']['is_success'] == true;
    }

    /**
     * @param $num_iid
     * @return null|GoodsDetail
     */
    public function itemUpdateListing($num_iid)
    {
        $method = 'kdt.item.update.listing';

        $params = array(
            'num_iid' => $num_iid
        );

        $response = $this->post($method, $params);
        if ($this->isResponseError($response)) {
            return null;
        }
        return ModelFactory::objectFromData($response['response']['item'], GoodsDetail::class);

    }

    /**
     * @param $num_iid
     * @return null|GoodsDetail
     */
    public function itemUpdateDelisting($num_iid)
    {
        $method = 'kdt.item.update.delisting';

        $params = array(
            'num_iid' => $num_iid
        );

        $response = $this->post($method, $params);
        if ($this->isResponseError($response)) {
            return null;
        }

        return ModelFactory::objectFromData($response['response']['item'], GoodsDetail::class);

    }

    /**
     * 批量上架商品
     * @param array $num_iids
     * @return bool
     */
    public function batchItemsUpdateListing($num_iids = array())
    {
        $method = 'kdt.items.update.listing';

        $itemIds = '';
        foreach ($num_iids as $id) {
            $itemIds .= $id . ',';
        }
        trim($itemIds, ',');
        $params = array(
            'num_iids' => $itemIds
        );

        $response = $this->post($method, $params);
        if ($this->isResponseError($response)) {
            return false;
        }
        return $response['response']['is_success'] == true;
    }

    /**
     * 批量下架商品
     * @param array $num_iids
     * @return bool
     */
    public function batchItemsUpdateDelisting($num_iids = array())
    {
        $method = 'kdt.items.update.delisting';

        $itemIds = '';
        foreach ($num_iids as $id) {
            $itemIds .= $id . ',';
        }
        trim($itemIds, ',');
        $params = array(
            'num_iids' => $itemIds
        );

        $response = $this->post($method, $params);
        if ($this->isResponseError($response)) {
            return false;
        }
        return $response['response']['is_success'] == true;
    }

    /**
     * @param $outer_id
     * @param $num_iid
     * @return GoodsSku[]|bool
     */
    public function skusCustomGet($outer_id, $num_iid)
    {
        $method = 'kdt.skus.custom.get';

        $params = array(
            'outer_id' => $outer_id,
            'num_iid' => $num_iid
        );

        $response = $this->get($method, $params);
        if ($this->isResponseError($response)) {
            return null;
        }

        return ModelFactory::objectListFromData($response['response']['skus'], GoodsSku::class);
    }

    /**
     * @param $num_iid
     * @param $sku_id
     * @param null|int $quantity
     * @param null|int $price
     * @param null|string $outer_id
     * @return bool|null|GoodsSku
     */
    public function itemSkuUpdate($num_iid, $sku_id, $quantity = null, $price = null, $outer_id = null)
    {
        $method = 'kdt.skus.custom.get';

        $params = array(
            'num_iid' => $num_iid,
            'sku_id' => $sku_id,
        );

        if ($quantity != null) {
            $params['quantity'] = $quantity;
        }
        if ($quantity != null) {
            $params['price'] = $price;
        }
        if ($quantity != null) {
            $params['outer_id'] = $outer_id;
        }

        $response = $this->post($method, $params);
        if ($this->isResponseError($response)) {
            return false;
        }

        return ModelFactory::objectFromData($response['response']['sku'], GoodsSku::class);
    }


}
