<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Product extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('queries');
        $this->load->database();
    }

    public function add_cart_value($where) {
        $this->db->select("id");
        $this->db->from('add_cart');
        $this->db->where('userId', $where['userId']);
        $this->db->where('productId', $where['productId']);
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function cart_info($where) {
        $this->db->select("add_cart.*,product.name,product_img.productImg,user.email,user.gstn,user.discount,product.dimensions");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->join('user', 'user.userId=add_cart.userId', 'LEFT');
        $this->db->where('add_cart.userId', $where['userId']);
        $this->db->where('add_cart.status', '0');
        $this->db->GROUP_BY('add_cart.id');

        $query = $this->db->get();
        $result = $query->result_array();

        foreach ($result as $key => $value) {
            $result[$key]['productImg'] = base_url() . $value['productImg'];
        }

        return $result;
    }

    public function product_id($data) {
        $this->db->select("add_product_id");
        $this->db->from('user');
        $this->db->where('userId', $data['userId']);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function Address_search($data) {
        $this->db->select("address.*,user.gstn,user.name as retailerName");
        $this->db->from('address');
        $this->db->join('user', 'user.userId=address.userId', 'LEFT');
        $this->db->where('id=', $data['addressId']);
//        $this->db->like('state', $data['']);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function city_search($data) {
        $this->db->select("*");
        $this->db->from('city');
        $this->db->where('state=', $data['state']);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function user_info($where) {
        $this->db->select("*");
        $this->db->from('user');
        $this->db->where('user.userId', $where['userId']);
        $query = $this->db->get();
        $result = $query->result_array();
        return $result;
    }

    public function productCut($city_search, $cart_info) {
        $this->db->select("id,quantity");
        $this->db->from('godan');
        $this->db->where('cityId=', $city_search);
        $this->db->where('productId=', $cart_info['productId']);
        $this->db->where('quantity>=', $cart_info['quantity']);
        $query = $this->db->get();
        $i = $query->row_array();
//print_r($i);die('sfafd');
        if (!empty($i)) {
            $lastq = $i['quantity'] - $cart_info['quantity'];
            $this->db->where('id', $i['id']);
            $result = $this->db->update('godan', array('quantity' => $lastq));
            return TRUE;
        } else {

            $this->db->select("id,quantity");
            $this->db->from('godan');
            $this->db->where('cityId=', $city_search);
            $this->db->where('productId=', $cart_info['productId']);
            $query = $this->db->get();
            $quan = $query->row_array();

            $req = $cart_info['quantity'] - $quan['quantity'];

            $data = array('productId' => $cart_info['productId'], 'fromCity' => '1', 'toCity' => $city_search, 'quantity' => $req);
            $this->db->insert('transfer', $data);
            $insert_id = $this->db->insert_id();

            $this->db->where('id', $quan['id']);
            $result = $this->db->update('godan', array('quantity' => '0'));
            return TRUE;
        }
    }

    public function productAdd($add_product_id, $value) {
        $this->db->select("id,quantity");
        $this->db->from('godan');
        $this->db->where('cityId=', $add_product_id[0]['add_product_id']);
        $this->db->where('productId=', $value['productId']);
        $query = $this->db->get();
        $i = $query->row_array();

        if (!empty($i)) {
            $lastq = $i['quantity'] + $value['quantity'];
            $this->db->where('id', $i['id']);
            $result = $this->db->update('godan', array('quantity' => $lastq));
            return TRUE;
        } else {

            $req = $value['quantity'];

            $data = array('userId' => $value['userId'], 'productId' => $value['productId'], 'cityId' => $add_product_id[0]['add_product_id'], 'quantity' => $req);
            $this->db->insert('godan', $data);
            $insert_id = $this->db->insert_id();

            return TRUE;
        }
    }

    public function Add_invoce($total, $invoice_no, $userId, $add_cart_ids) {
        $value = array('invoice_no' => $invoice_no, 'total' => $total, 'userId' => $userId, 'add_cart_id' => $add_cart_ids);
        $this->db->insert('invoice_detail', $value);
        $id = $this->db->insert_id();
        return TRUE;
    }

    public function update_product_status($data) {
        $datas = array('product_status' => '1');
        $this->db->where('productId', $data['id']);
        $result = $this->db->update('product', $datas);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('product');
        return $query;
    }

    public function gold_foil($where) {
        $this->db->select("add_cart.*,product.name,product_img.productImg,user.email,user.gstn,user.discount,product.dimensions");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->join('user', 'user.userId=add_cart.userId', 'LEFT');
        $this->db->where('add_cart.userId', $where['userId']);
        $this->db->where('add_cart.status', '0');
        $this->db->where('product.category_id', '2');
        $this->db->GROUP_BY('add_cart.id');
        $query = $this->db->get();
        $result = $query->result_array();
        foreach ($result as $key => $value) {
            $result[$key]['productImg'] = base_url() . $value['productImg'];
        }
        return $result;
    }

    public function order_placed($user) {
        $time = time();

        $this->db->where('userId', $user['userId']);
        $this->db->where('status', '0');
        $this->db->update('add_cart', array('status' => '1', 'placedBy' => $user['addressId'], 'unique' => $time));
        return TRUE;
    }

    public function products_lists($id, $where) {
        $result = array();
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();
        
        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();

        $this->db->select("*");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('product.sub_category_id', $id);
        $query = $this->db->get();
        $result['products'] = $query->result_array();
        return $result;
    }

    public function search($id, $where) {
        $result = array();
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();
        
        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();

        $this->db->select("*");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->like('product.name', $id['search']);
        $this->db->or_like('product.makingPrice', $id['search']);
//        $this->db->where('product.name', $id);
        $query = $this->db->get();
        $result['products'] = $query->result_array();
        return $result;
    }
    
    public function add_carts($where) {
        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function subcategory_list($where) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();

        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();
        return $result;
    }

    public function product_detail($where) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();

        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();

        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $this->db->limit('6');
        $query = $this->db->get();
        $result['sub_category'] = $query->result_array();

        $this->db->select("product.*,product_img.productImg");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('category_id', '2');
        $this->db->where('productImg!=', '');
        $this->db->order_by("lastModifyTime", "DESC");
        $this->db->limit('8');
        $query = $this->db->get();
        $result['latest_product'] = $query->result_array();

        $this->db->select("product.*,product_img.productImg");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('category_id', '2');
        $this->db->where('productImg!=', '');
        $this->db->order_by("lastModifyTime", "DESC");
        $this->db->limit('1');
        $query = $this->db->get();
        $result['latest_products'] = $query->result_array();

        $this->db->select("product.*,product_img.productImg");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('category_id', '2');
        $this->db->where('productImg!=', '');
        $this->db->order_by("lastModifyTime", "DESC");
        $this->db->limit('2');
        $query = $this->db->get();
        $result['latest_productss'] = $query->result_array();

        $this->db->select("product.*,product_img.productImg");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('productImg!=', '');
        $this->db->where('category_id', '2');
        $this->db->limit('8');
        $query = $this->db->get();
        $result['products'] = $query->result_array();
        return $result;
    }

    public function remarks_lists($link) {
        $this->db->select("remarks.*,u.userName as user_name,added_user.userName as added_user");
        $this->db->from('remarks');
        $this->db->join('user as added_user', 'remarks.userId=added_user.userId', 'LEFT');
        $this->db->join('user as u', 'remarks.added_by=u.userId', 'LEFT');
        $this->db->where('added_by', $link);
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function remarks_users() {
        $this->db->select("remarks.*,u.userName as user_name,added_user.userName as added_user");
        $this->db->from('remarks');
        $this->db->join('user as added_user', 'remarks.userId=added_user.userId', 'LEFT');
        $this->db->join('user as u', 'remarks.added_by=u.userId', 'LEFT');
        $this->db->GROUP_BY('remarks.added_by');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function remarks_lists_backup_21_03() {
        $this->db->select("remarks.*,u.userName as user_name,added_user.userName as added_user");
        $this->db->from('remarks');
        $this->db->join('user as added_user', 'remarks.userId=added_user.userId', 'LEFT');
        $this->db->join('user as u', 'remarks.added_by=u.userId', 'LEFT');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function ledger_lists_backup_31_03() {
        $this->db->select("invoice_detail.*,u.name as user_name");
        $this->db->from('invoice_detail');
//        $this->db->join('user as added_user', 'payment.userId=added_user.userId', 'LEFT');
        $this->db->join('user as u', 'invoice_detail.userId=u.userId', 'LEFT');
        $this->db->where('userType', '1');
        $this->db->GROUP_BY('invoice_detail.userId');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function update_user_approvel($data) {
        $datas = array('product_approvel' => '1');
        $this->db->where('userId', $data['userId']);
        $result = $this->db->update('user', $datas);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('user');
        return $query;
    }

    public function ledger_lists() {
        $this->db->select("user.userId,name as user_name");
        $this->db->from('user');
        $this->db->where('userType', '1');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function ledger_list($data) {
        $this->db->select("id,invoice_no,total,lastModifyTime");
        $this->db->from('invoice_detail');
        $this->db->where('userId', $data);
        $query = $this->db->get();
        $result = $query->result_array();
        $total = '';
        foreach ($result as $key => $value) {
            $result[$key]['date'] = date('d-m-y', strtotime($value['lastModifyTime']));
            $result[$key]['types'] = 'Invoice';
            $totals = $value['total'];
            $total = $total + $totals;
            $results['final'] = $total;
//echo $total;echo '<br>';
        }
//echo '<pre>';  print_r($result);  print_r($results);die('qweqwe');
        $this->db->select("id,amount,type,lastModifyTime as date");
        $this->db->from('payment');
        $this->db->where('userId', $data);
        $this->db->where('status', '1');
        $query = $this->db->get();
        $results['payment'] = $query->result_array();

        $payment = '';
        foreach ($results['payment'] as $key => $value) {
            $results['payment'][$key]['date'] = date('d-m-y', strtotime($value['date']));
            $results['payment'][$key]['types'] = 'Payment';
            $payments = $value['amount'];
            $payment = $payment + $payments;
            $results['finals'] = $payment;
        }
        $ishan = array_merge($result, $results['payment']);
        // echo '<pre>';    print_r($results);
//print_r($result);
//echo $results['final'];echo '<br>';
//echo $results['finals']; echo '<br>';
        $outstanding['outstanding'] = @$results['final'] - @$results['finals'];
        unset($ishan['payment']);
        $outstanding['types'] = "outstanding";
        $ishan[]['outstanding'] = $outstanding;
//die('eeeee');
        return $ishan;
    }

    public function ledger_list_backup_15_03($data) {
        $this->db->select("*");
        $this->db->from('invoice_detail');
        $this->db->where('userId', $data);
        $query = $this->db->get();
        $result['invoice'] = $query->result_array();
        $total = '';
        foreach ($result['invoice'] as $key => $value) {
            $total = $value['total'];
            $total = $total + $total;
            $results['final'] = $total;
        }

        $this->db->select("*");
        $this->db->from('payment');
        $this->db->where('userId', $data);
        $query = $this->db->get();
        $result['payment'] = $query->result_array();

        $payment = '';
        foreach ($result['payment'] as $key => $value) {
            $payment = $value['amount'];
            $payment = $payment + $payment;
            $results['finals'] = $payment;
        }
        $outstanding = $results['final'] - $results['finals'];

        $result['outstanding'] = $outstanding;
        return $result;
    }

    public function update_order_backup_17_01($user) {
        $this->db->where('userId', $user['id']);
        $this->db->update('add_cart', array('status' => '2'));
        return TRUE;
    }

    public function update_payment($user) {
        $this->db->where('id', $user['id']);
        $this->db->update('payment', array('status' => '1'));
        return TRUE;
    }

    public function payment_lists() {
        $this->db->select("payment.*,u.name as user_name,added_user.name as added_user");
        $this->db->from('payment');
        $this->db->join('user as added_user', 'payment.userId=added_user.userId', 'LEFT');
        $this->db->join('user as u', 'payment.added_by=u.userId', 'LEFT');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function products_list_backup_12_06() {
        $this->db->select("*");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function products_list($where) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();
        
        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();

        $this->db->select("*");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('productImg!=', '');
        $this->db->limit('30');
        $query = $this->db->get();
        $result['products'] = $query->result_array();
        return $result;
    }
    
    public function next_products_list($where) {
        $this->db->select("*");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('productImg!=', '');
        $this->db->where('product.productId>=', $where);
        $this->db->limit('30');
        $query = $this->db->get();
         return $query->result_array();
//         $result;
    }

    public function products_details($id,$where) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();
        
        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();

        $this->db->select("product.*,product_img.productImg,sub_category.name as sub_category_name");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->join('sub_category', 'sub_category.id=product.sub_category_id', 'LEFT');
        $this->db->where('product.productId', $id);
        $query = $this->db->get();
        $result['product_detail'] = $query->result_array();
        return $result;
    }

    public function products_details_backup_12_06($id) {
        $this->db->select("*");
        $this->db->from('product');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('product.productId', $id);
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function update_order($user) {
        $unique = rand();
        $this->db->where('userId', $user['id']);
        $this->db->update('add_cart', array('status' => '2', 'unique' => $unique));
        return TRUE;
    }

    public function product_lists() {
        $branches = array();
        $this->db->select("p.*,user.userName,product_img.productImg, category.name as category_name,sub_category.name as subcategory_name");
        $this->db->from('product as p');
        $this->db->join('user', 'p.addedBy=user.userId', 'LEFT');
        $this->db->join('category', 'category.id=p.category_id', 'LEFT');
        $this->db->join('sub_category', 'sub_category.id=p.sub_category_id', 'LEFT');
        $this->db->join('product_img', 'p.productId=product_img.productId', 'LEFT');
        $this->db->where('p.product_status=', '0');
        $this->db->group_by("productId");
        $this->db->order_by("productId", "DESC");
        $query = $this->db->get();

        $branches['reservation_detail'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('category');
        $query = $this->db->get();
        $branches['category'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('sub_category');
        $query = $this->db->get();
        $branches['sub_category'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('sub_sub_category');
        $query = $this->db->get();
        $branches['sub_sub_category'] = $result = $query->result_array();

        return $branches;
    }

    public function product_list($type, $userId, $data) {
        $branches = array();
        $this->db->select("p.*,user.userName,product_img.productImg, category.name as category_name,sub_category.name as subcategory_name,sub_sub_category.name as product_category");
        $this->db->from('product as p');
        $this->db->join('user', 'p.addedBy=user.userId', 'LEFT');
        $this->db->join('category', 'category.id=p.category_id', 'LEFT');
        $this->db->join('sub_category', 'sub_category.id=p.sub_category_id', 'LEFT');
        $this->db->join('sub_sub_category', 'sub_sub_category.id=p.sub_sub_category_id', 'LEFT');

        $this->db->join('product_img', 'p.productId=product_img.productId', 'LEFT');
//        $this->db->where('r.status=', '4');
        if ($type != '0') {
            $this->db->where('p.addedBy', $userId);
        }
        $this->db->where('p.sub_sub_category_id', $data);
        $this->db->group_by("productId");
        $this->db->order_by("productId", "DESC");
        $query = $this->db->get();

        $branches['reservation_detail'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('category');
        $query = $this->db->get();
        $branches['category'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('sub_category');
        $query = $this->db->get();
        $branches['sub_category'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('sub_sub_category');
        $query = $this->db->get();
        $branches['sub_sub_category'] = $result = $query->result_array();

        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $branches['all_sub_category'] = $query->result_array();

        return $branches;
    }

    public function product_list_backup() {
        $branches = array();
        $this->db->select("p.*,user.userName,product_img.productImg");
        $this->db->from('product as p');
        $this->db->join('user', 'p.addedBy=user.userId', 'LEFT');
        $this->db->join('product_img', 'p.productId=product_img.productId', 'LEFT');
//        $this->db->where('r.status=', '4');
        $this->db->group_by("productId");
        $this->db->order_by("productId", "DESC");
        $query = $this->db->get();

        $branches['reservation_detail'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('category');
        $query = $this->db->get();
        $branches['category'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('sub_category');
        $query = $this->db->get();
        $branches['sub_category'] = $result = $query->result_array();

        $this->db->select("name,id");
        $this->db->from('sub_sub_category');
        $query = $this->db->get();
        $branches['sub_sub_category'] = $result = $query->result_array();

        return $branches;
    }

    public function selectsubcat($data) {
        $this->db->select("name,id");
        $this->db->from('sub_category');
        $this->db->where('category_id=', $data['id']);
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function selectsubsubcat($data) {
        $this->db->select("name,id");
        $this->db->from('sub_sub_category');
        $this->db->where('sub_category_id=', $data['id']);
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function profile($data) {
        $this->db->select("*");
        $this->db->from('user');
        $this->db->where('userId=', $data['userId']);
        $query = $this->db->get();
        return $result = $query->row_array();
    }

    public function order_list_add_cart_id($data) {
        $this->db->select("add_cart_id,id");
        $this->db->from('invoice_detail');
        $this->db->where('id', $data);
        $query = $this->db->get();
        return $result = $query->row_array();
    }

    public function order_list($data) {
        $this->db->select("add_cart.*,user.userName,product.name,product.makingPrice,product.weight,product_img.productImg as img");
        $this->db->from('add_cart');
        $this->db->join('user', 'add_cart.userId=user.userId', 'LEFT');
        $this->db->join('product', 'add_cart.productId=product.productId', 'LEFT');
        $this->db->join('product_img', 'add_cart.productId=product_img.productId', 'LEFT');
//        $this->db->where('add_cart.status!=', '0');
        $this->db->where('add_cart.id', $data);
//        $this->db->GROUP_BY('add_cart.unique');
        $query = $this->db->get();
        return $result = $query->row_array();
    }

    public function my_orders($where) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();

        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('add_cart.status!=', '0');
        $query = $this->db->get();
        $result['order'] = $query->result_array();
        return $result;
    }

    public function my_order($where) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();
        
        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();

        $this->db->select("invoice_detail.*,add_cart.userId,add_cart.placedBy,add_cart.unique,add_cart.status,user.name as username,manufacture.name as manufacturer");
        $this->db->from('invoice_detail');
        $this->db->join('add_cart', 'add_cart.id=invoice_detail.add_cart_id', 'LEFT');
        $this->db->join('user', 'user.userId=add_cart.userId', 'LEFT');
        $this->db->join('user as manufacture', 'manufacture.userId=add_cart.placedBy', 'LEFT');
        $this->db->where('invoice_detail.userId', $where);
        $query = $this->db->get();
        $result['invoice'] = $query->result_array();
        return $result;



//        $this->db->select("add_cart.*,product.name,product_img.productImg");
//        $this->db->from('add_cart');
//        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
//        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
//        $this->db->where('userId', $where);
//        $this->db->where('add_cart.status!=', '0');
//        $query = $this->db->get();
//        $result['order'] = $query->result_array();
//        return $result;
    }

    public function my_ledger($data) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $ishan['all_sub_category'] = $query->result_array();

        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $data);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $ishan['add_cart'] = $query->result_array();

        $this->db->select("id,invoice_no,total,lastModifyTime");
        $this->db->from('invoice_detail');
        $this->db->where('userId', $data);
        $query = $this->db->get();
        $result = $query->result_array();
        $total = '';
        foreach ($result as $key => $value) {
            $result[$key]['date'] = date('d-m-y', strtotime($value['lastModifyTime']));
            $result[$key]['types'] = 'Invoice';
            $totals = $value['total'];
            $total = $total + $totals;
            $results['final'] = $total;
//echo $total;echo '<br>';
        }
//echo '<pre>';  print_r($result);  print_r($results);die('qweqwe');
        $this->db->select("id,amount,type,lastModifyTime as date");
        $this->db->from('payment');
        $this->db->where('userId', $data);
        $this->db->where('status', '1');
        $query = $this->db->get();
        $results['payment'] = $query->result_array();

        $payment = '';
        foreach ($results['payment'] as $key => $value) {
            $results['payment'][$key]['date'] = date('d-m-y', strtotime($value['date']));
            $results['payment'][$key]['types'] = 'Payment';
            $payments = $value['amount'];
            $payment = $payment + $payments;
            $results['finals'] = $payment;
        }
        $ishan['ledger'] = array_merge($result, $results['payment']);
        // echo '<pre>';    print_r($results);
//print_r($result);
//echo $results['final'];echo '<br>';
//echo $results['finals']; echo '<br>';
        $outstanding['outstanding'] = @$results['final'] - @$results['finals'];
        unset($ishan['payment']);
        $outstanding['types'] = "outstanding";
        $ishan[]['outstanding'] = $outstanding;
//die('eeeee');
        return $ishan;
    }

    public function addressId($data) {
        $this->db->select("id");
        $this->db->from('address');
        $this->db->where('userId=', $data['userId']);
        $query = $this->db->get();
        return $result = $query->row_array();
    }

    public function reservationDetail($data) {
        $branches = array();
        $this->db->select("r.*,user.emailId,user.picPath,user.instaLink,user.description,business.name as business_name,business.address as business_address,category.name");
        $this->db->from('reservation as r');
        $this->db->join('user', 'r.userId=user.userId', 'LEFT');
        $this->db->join('business', 'r.businessId=business.businessId', 'LEFT');
        $this->db->join('category', 'r.categoryId=category.categoryId', 'LEFT');
//        $this->db->join('reservationImages', 'reservationImages.reservationId=r.reservationId', 'LEFT');
        $this->db->where('r.reservationId=', $data);
        $query = $this->db->get();
        $branches['reservation_detail'] = $query->row_array();

        $this->db->select('reservationImg');
        $this->db->from('reservationImages');
        $this->db->where('reservationId', $data);
        $query = $this->db->get();
        $branches['reservation_images'] = $query->result_array();

        $this->db->select('*');
        $this->db->from('reported_reservation');
        $this->db->where('reservationId', $data);
        $query = $this->db->get();
        $branches['reported_users'] = $query->num_rows();

        return $branches;
    }

    public function filter_list($data) {
        $this->db->select("reservation.title,reservation.locationName,reservation.price,reservation.startDate,reservation.startTime,reservation.status,user.emailId,reservation.userId,reservation.reservationId");
        $this->db->from('reservation');
        $this->db->join('user', 'reservation.userId=user.userId', 'LEFT');
        $this->db->where('reservation.status=', $data);
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function category_22_04() {
        $this->db->select("*");
        $this->db->from('category');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $result = $query->result_array();
    }

    public function category() {
        $this->db->select("*");
        $this->db->from('category');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $result = $query->result_array();
    }

    public function categorys($type) {
        $this->db->select("*");
        $this->db->from('category');

        if ($type != '0') {
            $this->db->where('id!=', '2');
        }
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $result = $query->result_array();
    }

    public function godanList($id) {
        $result = array();
        $this->db->select("godan.*,product.name as productname,product.dimensions,product.makingPrice,city.name as cityname");
        $this->db->from('godan');
        $this->db->join('product', 'product.productId=godan.productId', 'LEFT');
        $this->db->join('city', 'city.id=godan.cityId', 'LEFT');
        $this->db->where('godan.cityId=', $id);
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        $result['data'] = $query->result_array();

        $this->db->select("productId,name,dimensions,makingPrice");
        $this->db->from('product');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        $result['product'] = $query->result_array();

        $this->db->select("city.name as cityname");
        $this->db->from('city');
        $this->db->where('city.id=', $id);
        $query = $this->db->get();
        $result['city'] = $query->result_array();

        return $result;
    }

    public function cityList() {
        $this->db->select("*");
        $this->db->from('city');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $query->result_array();
    }

    public function expensesList() {
        $this->db->select("userId,userName,lastModifyTime");
        $this->db->from('user');
        $this->db->where('userType=', '3');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $query->result_array();
    }

    public function expensesLists($id) {
        $this->db->select("id,amount,detail,img,lastModifyTime,status");
        $this->db->from('expenses');
        $this->db->where('userId=', $id);
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        $result = $query->result_array();

//        foreach ($result as $key => $value) {
//            $result[$key]['img'] = base_url() . $value['img'];
//        }
        return $result;
    }

    public function transferList() {
        $result = array();
        $this->db->select("transfer.*,user.userName,product.name as pname,city.name as toCity,c.name as fromCity");
        $this->db->from('transfer');
        $this->db->join('user', 'transfer.userId=user.userId', 'LEFT');
        $this->db->join('product', 'transfer.productId=product.productId', 'LEFT');
        $this->db->join('city as c', 'transfer.fromCity=c.id', 'LEFT');
        $this->db->join('city', 'transfer.toCity=city.id', 'LEFT');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        $result['data'] = $query->result_array();

        $this->db->select("productId,name");
        $this->db->from('product');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        $result['product'] = $query->result_array();

        $this->db->select("id,name");
        $this->db->from('city');
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        $result['city'] = $query->result_array();
        return $result;
    }

    public function subcategory($id) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('sub_category.category_id=', $id);
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $result = $query->result_array();
    }

    public function subsubcategory($id) {
        $this->db->select("*");
        $this->db->from('sub_sub_category');
        $this->db->where('sub_sub_category.sub_category_id=', $id);
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $result = $query->result_array();
    }

    public function commision_cat() {
        $this->db->select("*");
        $this->db->from('sub_sub_category');
//        $this->db->where('sub_sub_category.sub_category_id=', $id);
        $query = $this->db->get();
        $this->db->order_by("lastModifyTime", "DESC");
        return $result = $query->result_array();
    }

    public function user_list() {
        $this->db->select("*");
        $this->db->from('user');
        $this->db->where('userType!=', '0');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function cityname($id) {
        $this->db->select("state");
        $this->db->from('city');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $result = $query->row_array();
    }

    public function order_lists($id) {
        $ids = $id['state'];
        $this->db->select("*");
        $this->db->from('invoice_detail');
        $this->db->join('add_cart', 'add_cart.id=invoice_detail.add_cart_id', 'LEFT');
        $this->db->where("(`invoice_no` LIKE '%$ids%')");
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function cityname_backup_23_03($id) {
        $this->db->select("state");
        $this->db->from('city');
        $this->db->where('id', $id);
        $query = $this->db->get();
        return $result = $query->row_array();
    }

    public function order_lists_backup_23_03($id) {
        $this->db->select("add_cart.*,user.userName,product.name,product.makingPrice,product.weight");
        $this->db->from('add_cart');
        $this->db->join('user', 'add_cart.userId=user.userId', 'LEFT');
        $this->db->join('product', 'add_cart.productId=product.productId', 'LEFT');
        $this->db->join('address', 'address.id=add_cart.placedBy', 'LEFT');
        $this->db->where('add_cart.status!=', '0');
        $this->db->where('address.state', $id['state']);
        $this->db->GROUP_BY('add_cart.unique');
        $query = $this->db->get();
        return $result = $query->result_array();
//       echo '<pre>';  print_r($result);die('adfafad');
    }

    public function order_lists_backup_21_03() {
        $this->db->select("add_cart.*,user.userName,product.name,product.makingPrice");
        $this->db->from('add_cart');
        $this->db->join('user', 'add_cart.userId=user.userId', 'LEFT');
        $this->db->join('product', 'add_cart.productId=product.productId', 'LEFT');
        $this->db->where('add_cart.status!=', '0');
        $this->db->GROUP_BY('add_cart.unique');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function order_list12($data) {
        $this->db->select("add_cart.*,user.userName,product.name,product.makingPrice,product.weight,product_img.productImg as img");
        $this->db->from('add_cart');
        $this->db->join('user', 'add_cart.userId=user.userId', 'LEFT');
        $this->db->join('product', 'add_cart.productId=product.productId', 'LEFT');
        $this->db->join('product_img', 'add_cart.productId=product_img.productId', 'LEFT');
//        $this->db->where('add_cart.status!=', '0');
        $this->db->where('add_cart.unique', $data);
//        $this->db->GROUP_BY('add_cart.unique');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function order_list_backup_13_01() {
        $this->db->select("add_cart.*,user.userName,product.name,product.makingPrice");
        $this->db->from('add_cart');
        $this->db->join('user', 'add_cart.userId=user.userId', 'LEFT');
        $this->db->join('product', 'add_cart.productId=product.productId', 'LEFT');
        $this->db->where('add_cart.status!=', '0');
//        $this->db->GROUP_BY('add_cart.unique');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function order_data($id) {
        $this->db->select("add_cart.*,user.userName,product.name,product.makingPrice,product.dimensions,user.email,address.zip,address.city,address.state,address.mobile,address.address,city.name as cityName,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('user', 'add_cart.userId=user.userId', 'LEFT');
        $this->db->join('product', 'add_cart.productId=product.productId', 'LEFT');
        $this->db->join('address', 'add_cart.placedBy=address.id', 'LEFT');
        $this->db->join('city', 'city.state=address.state', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=product.productId', 'LEFT');
        $this->db->where('add_cart.userId', $id['id']);
//        $this->db->GROUP_BY('add_cart.unique');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function order_data_backup($id) {
        $this->db->select("add_cart.*,user.userName,product.name,product.makingPrice,product.dimensions,user.email,address.zip,address.city,address.state,address.mobile,address.address");
        $this->db->from('add_cart');
        $this->db->join('user', 'add_cart.userId=user.userId', 'LEFT');
        $this->db->join('product', 'add_cart.productId=product.productId', 'LEFT');
        $this->db->join('address', 'add_cart.placedBy=address.id', 'LEFT');
        $this->db->where('add_cart.userId', $id['id']);
        $this->db->where('add_cart.status', '1');
//        $this->db->GROUP_BY('add_cart.unique');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function state_data($data) {
        $this->db->select("*");
        $this->db->from('city');
        $this->db->where('state=', $data);
        $query = $this->db->get();
        return $result = $query->row_array();
    }

    public function order_list_backup_25_12() {
        $this->db->select("order.*,user.userName,product.name,product.makingPrice");
        $this->db->from('order');
        $this->db->join('user', 'order.userId=user.userId', 'LEFT');
        $this->db->join('product', 'order.productId=product.productId', 'LEFT');
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function subcategoryDetail($data) {
        $this->db->select("*");
        $this->db->from('subCategory');
        $this->db->where('categoryId=', $data);
        $query = $this->db->get();
        return $result = $query->result_array();
    }

    public function addsubcategory($data, $photo) {
        unset($data['categoryImage']);
        $data['img'] = $photo;
        $this->db->insert('sub_category', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function add_cart_values($where) {
        $this->db->select("*");
        $this->db->from('sub_category');
        $this->db->where('category_id', '2');
        $query = $this->db->get();
        $result['all_sub_category'] = $query->result_array();
        
        $this->db->select("*");
        $this->db->from('address');
        $this->db->where('userId', $where);
        $query = $this->db->get();
        $result['address'] = $query->row_array();

        $this->db->select("add_cart.*,product.name,product_img.productImg");
        $this->db->from('add_cart');
        $this->db->join('product', 'product.productId=add_cart.productId', 'LEFT');
        $this->db->join('product_img', 'product_img.productId=add_cart.productId', 'LEFT');
        $this->db->where('userId', $where);
        $this->db->where('userId!=', '0');
        $this->db->where('add_cart.status', '0');
        $query = $this->db->get();
        $result['add_cart'] = $query->result_array();
        return $result;
    }

    public function update_cart($data, $id) {
        $this->db->where('id', $id[0]['id']);
        $this->db->update('add_cart', $data);

        $this->db->select("*");
        $this->db->from('add_cart');
        $this->db->where('id=', $id[0]['id']);
        $query = $this->db->get();
        return $query->row_array();
    }

    public function add_cart($data) {
        $data['site'] = '1';
        $data['cartDate'] = date('Y-m-d');
        $this->db->insert('add_cart', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function add_cart_bacup_13_06($data) {
        $this->db->insert('add_cart', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function addsubsubcategory($data, $photo) {
        unset($data['categoryImage']);
        $data['img'] = $photo;
        $this->db->select("category_id");
        $this->db->from('sub_category');
        $this->db->where('id=', $data['sub_category_id']);
        $query = $this->db->get();
        $i = $query->row_array();
        $data['category_id'] = $i['category_id'];
//        print_r($data);die('aa');
        $this->db->insert('sub_sub_category', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function addcategory($data, $photo) {
        $data['img'] = $photo;
        $this->db->insert('category', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function addgodan($data, $product, $quantity, $msl) {
        unset($data['productId']);
        $data['productId'] = $product;
        unset($data['quantity']);
        $data['quantity'] = $quantity;
        $data['msl'] = $msl;
//        echo '<pre>';
//        print_r($data);
//        die('weeeeeeeeeee');
        $this->db->select("id,quantity");
        $this->db->from('godan');
        $this->db->where('cityId=', $data['cityId']);
        $this->db->where('productId=', $data['productId']);
        $query = $this->db->get();
        $i = $query->row_array();

        if (empty($i)) {

            $this->db->insert('godan', $data);
            $insert_id = $this->db->insert_id();
            return $insert_id;
        } else {
            $lastq = $i['quantity'] + $data['quantity'];
            $this->db->where('id', $i['id']);
            return $result = $this->db->update('godan', array('quantity' => $lastq, 'msl' => $data['msl']));
//             $insert_id;
        }
    }

    public function addcity($data) {
        $this->db->select("id");
        $this->db->from('city');
        $this->db->where('priority=', $data['priority']);
        $query = $this->db->get();
        $i = $query->row_array();

        if (empty($i)) {
            $this->db->insert('city', $data);
            $insert_id = $this->db->insert_id();
            return $insert_id;
        } else {
            return FALSE;
        }
    }

    public function addtransfer($data, $productId, $from, $to, $quantity) {
        unset($data['productId']);
        $data['productId'] = $productId;
        unset($data['fromCity']);
        $data['fromCity'] = $from;
        unset($data['toCity']);
        $data['toCity'] = $to;
        unset($data['quantity']);
        $data['quantity'] = $quantity;

        $this->db->select("id,quantity");
        $this->db->from('godan');
        $this->db->where('cityId=', $data['fromCity']);
        $this->db->where('productId=', $data['productId']);
        $this->db->where('quantity>=', $data['quantity']);
        $query = $this->db->get();
        $i = $query->row_array();

        $this->db->select("id,quantity");
        $this->db->from('godan');
        $this->db->where('cityId=', $data['toCity']);
        $this->db->where('productId=', $data['productId']);
        $query = $this->db->get();
        $j = $query->row_array();

        if (!empty($i)) {
            $this->db->insert('transfer', $data);
            $insert_id = $this->db->insert_id();
            if (!empty($j)) {
                $lastq = $i['quantity'] - $data['quantity'];
                $this->db->where('id', $i['id']);
                $result = $this->db->update('godan', array('quantity' => $lastq));
                return TRUE;
            } else {
                $value = array('userId' => $data['userId'], 'cityId' => $data['toCity'], 'productId' => $data['productId'], 'quantity' => $data['quantity']);
                $this->db->insert('godan', $value);
                $insert_id = $this->db->insert_id();
                return TRUE;
            }
        } else {
            return False;
        }
    }

    public function adduser($data, $user) {
        $data['createdBy'] = $user;
        $pass = mt_rand(100000, 999999);
//                
        $split = explode("@", $data['email']);
//        print_r($split);die('asdfadfs');
        $user = array('name' => $data['name'], 'userName' => $split[0], 'password' => @$pass, 'email' => @$data['email'], 'mobile' => @$data['mobile'], 'userType' => @$data['userType'], 'gstn' => @$data['gstn'], 'discount' => @$data['discount']);
        $this->db->insert('user', $user);
        $insert_id = $this->db->insert_id();

        $ms = '
<div style="color: #000000;padding: 20px;">
    <p style="color: #000000;">Dear ' . $split[0] . ',<br><br>Your request for a new password has been received.Your password is reset to :' . @$pass . '</p>
    
 
    <p style="color:#000000">Thanks & Regards<br> Team Golden Touch</p>
</div>';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: <support@goldentouch.com>' . "\r\n";
        mail(@$data['email'], "Golden touch password", $ms, $headers);

        if (@$data['state'] != '') {
            $address = array('city' => @$data['city'], 'state' => @$data['state'], 'address' => @$data['address'], 'userId' => @$insert_id, 'name' => @$data['name'], 'email' => @$data['email'], 'mobile' => @$data['mobile']);
            $this->db->insert('address', $address);
            $insert_id = $this->db->insert_id();
        }
        return $insert_id;
    }

    public function adduser_20_03_backup($data, $user) {
        $data['createdBy'] = $user;
        $this->db->insert('user', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function addproduct($data) {
        if (@$data['purity'][0] != '' && @$data['purity'][1] != '') {
            $data['purity'] = @$data['purity'][0] . ',' . @$data['purity'][1];
        } else if (@$data['purity'][0] != '') {
            $data['purity'] = @$data['purity'][0];
        } else {
            $data['purity'] = @$data['purity'][1];
        }
        if (@$data['color'][0] != '' && @$data['color'][1] != '') {
            $data['color'] = @$data['color'][0] . ',' . @$data['color'][1];
        } else if (@$data['color'][0] != '') {
            $data['color'] = @$data['color'][0];
        } else {
            @$data['color'] != $data['color'][1];
        }
        if ($data['category_id'] == '2') {
            $data['product_status'] = '1';
        }
//        $data['color'] = @$data['color'][0] . ',' . @$data['color'][1];
//        echo '<pre>';echo $purity; print_r($data);die('ererer');
        $this->db->insert('product', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function updateproduct($data) {
        if (@$data['purity'][0] != '' && @$data['purity'][1] != '') {
            $data['purity'] = @$data['purity'][0] . ',' . @$data['purity'][1];
        } else if (@$data['purity'][0] != '') {
            $data['purity'] = @$data['purity'][0];
        } else {
            $data['purity'] = @$data['purity'][1];
        }
        if (@$data['color'][0] != '' && @$data['color'][1] != '') {
            $data['color'] = @$data['color'][0] . ',' . @$data['color'][1];
        } else if (@$data['color'][0] != '') {
            $data['color'] = @$data['color'][0];
        } else {
            @$data['color'] != $data['color'][1];
        }
        if ($data['category_id'] == '2') {
            $data['product_status'] = '1';
        }
        $this->db->where('productId', $data['productId']);
        $result = $this->db->update('product', $data);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('product');
        return $query;
    }

    public function addproduct_backup_10_03($data) {
        $this->db->insert('product', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function product_image($data) {
        $this->db->insert('product_img', $data);
        $insert_id = $this->db->insert_id();
        return $insert_id;
    }

    public function product_images($data) {
        $this->db->where('productId', $data['productId']);
        $result = $this->db->update('product_img', $data);
        $query = $this->db->get('product_img');
        return $query;
    }

    public function deletecategory($data) {
        $this->db->where('id', $data['categoryId']);
        return $this->db->delete('category');
    }

    public function deleteuser($data) {
        $this->db->where('userId', $data['userId']);
        return $this->db->delete('user');
    }

    public function deletecity($data) {
        $this->db->where('id', $data['id']);
        return $this->db->delete('city');
    }

    public function deletegodan($data) {
        $this->db->where('id', $data['id']);
        return $this->db->delete('godan');
    }

    public function deleteproduct($data) {
        $this->db->where('productId', $data['productId']);
        $this->db->delete('product');

        $this->db->where('productId', $data['productId']);
        return $this->db->delete('product_img');
    }

    public function deletesubcategory($data) {
        $this->db->where('id', $data['id']);
        return $this->db->delete('sub_category');
    }

    public function deletereservation($data) {
        $this->db->where('reservationId', $data['reservationId']);
        $result = $this->db->update('reservation', array('status' => '5'));
        $query = $this->db->get('reservation');
        return $query;
    }

    public function updatecategory($data, $photo) {
        $data['img'] = $photo;
        $this->db->where('id', $data['id']);
//        $result = $this->db->update('category', array('categoryName' => $data['categoryName'],'categoryImage' => $data['categoryImage']));
        $result = $this->db->update('category', $data);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('category');
        return $query;
    }

    public function updategodan($data) {
        $datas = array('productId' => $data['productId'][0], 'quantity' => $data['quantity'][0], 'msl' => $data['msl'][0], 'id' => $data['id'], 'cityId' => $data['cityId']);
        $this->db->where('id', $data['id']);
        $result = $this->db->update('godan', $datas);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('godan');
        return $query;
    }

    public function updatecity($data) {
        $this->db->select("id");
        $this->db->from('city');
        $this->db->where('priority=', $data['priority']);
        $this->db->where('id!=', $data['id']);
        $query = $this->db->get();
        $i = $query->row_array();

        if (empty($i)) {
            $this->db->where('id', $data['id']);
//        $result = $this->db->update('category', array('categoryName' => $data['categoryName'],'categoryImage' => $data['categoryImage']));
            $result = $this->db->update('city', $data);
            $this->db->order_by("lastModifyTime", "DESC");
            $query = $this->db->get('city');
            return $query;
        } else {
            return FALSE;
        }
    }

    public function updatetransfer($data) {
        $this->db->where('id', $data['id']);
        $result = $this->db->update('transfer', $data);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('transfer');
        return $query;
    }

    public function updateuser($data) {
        $this->db->where('userId', $data['userId']);
//        $result = $this->db->update('category', array('categoryName' => $data['categoryName'],'categoryImage' => $data['categoryImage']));
        $result = $this->db->update('user', $data);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('user');
        return $query;
    }

    public function update_expenses($data) {
        $datas = array('status' => '1');
        $this->db->where('id', $data['id']);
        $result = $this->db->update('expenses', $datas);
        return $query;
    }

    public function update_commision($data) {
        unset($data['action']);
//        $result = $this->db->update('category', array('categoryName' => $data['categoryName'],'categoryImage' => $data['categoryImage']));
        $this->db->where('id', $data['id']);
        $result = $this->db->update('sub_sub_category', $data);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('sub_sub_category');
        return $query;
    }

    public function updatesubcategory($data, $photo) {
        $this->db->where('id', $data['id']);
        $data['img'] = $photo;
//        $result = $this->db->update('category', array('categoryName' => $data['categoryName'],'categoryImage' => $data['categoryImage']));
        $result = $this->db->update('sub_category', $data);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('sub_category');
        return $query;
    }

    public function updatesubsubcategory($data, $photo) {
        $this->db->where('id', $data['id']);
        $data['img'] = $photo;
        $result = $this->db->update('sub_sub_category', $data);
        $this->db->order_by("lastModifyTime", "DESC");
        $query = $this->db->get('sub_sub_category');
        return $query;
    }

    public function updatereservation($data) {
        $this->db->where('reservationId', $data['reservationId']);
        $result = $this->db->update('reservation', array('status' => '0'));
        $query = $this->db->get('reservation');
        return $query;
    }

    public function updatecommission($data) {
        $this->db->where('commissionId', $data['commissionId']);
        $result = $this->db->update('commission', array('commission' => $data['commission']));
        $query = $this->db->get('commission');
        return $query;
    }

//    public function updatesubcategory($data) {
//        $this->db->where('subCategoryId', $data['subCategoryId']);
//        $result = $this->db->update('subCategory', array('name' => $data['name']));
//        $query = $this->db->get('subCategory');
//        return $query;
//    }
}
