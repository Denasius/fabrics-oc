<?php
class ControllerExtensionModuleFilterapp extends Controller {
	public function index() {
    
		$this->load->model('catalog/category');
        $data['is_path'] =  isset($_GET['path']) ? true : false;

        $category_info = $this->model_catalog_category->getCategories();
        if ($category_info) {
          foreach ( $category_info as $categoryInfo ) {

            if ( $categoryInfo['category_id'] == 59 ) {
              $category_id = $categoryInfo['category_id'];
              $this->load->language('extension/module/filterapp');

              $url = '';

              if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
              }

              if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
              }

              if (isset($this->request->get['limit'])) {
                $url .= '&limit=' . $this->request->get['limit'];
              }

              //$data['action'] = str_replace('&amp;', '&', $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url));

              if (isset($this->request->get['filterapp'])) {
                $data['filter_category'] = explode(',', $this->request->get['filter']);
              } else {
                $data['filter_category'] = array();
              }

              $this->load->model('catalog/product');

              $data['filter_groups'] = array();

              $filter_groups = $this->model_catalog_category->getCategoryFilters($category_id);

              if ($filter_groups) {
                foreach ($filter_groups as $filter_group) {
                  $childen_data = array();

                  foreach ($filter_group['filter'] as $filter) {
                    $filter_data = array(
                      'filter_category_id' => $category_id,
                      'filter_filter'      => $filter['filter_id']
                    );

                    $childen_data[] = array(
                      'filter_id' => $filter['filter_id'],
                      'name'      => $filter['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : '')
                    );
                  }

                  $data['filter_groups'][] = array(
                    'filter_group_id' => $filter_group['filter_group_id'],
                    'name'            => $filter_group['name'],
                    'filter'          => $childen_data
                  );

                } // end foreach $filter_groups

              } // end if $filter_groups
            } // end if $categoryInfo['category_id'] == 59

          }// end foreach category_info

          return $this->load->view('extension/module/filterapp', $data);
		}
  }
  
  public function filter_products(){

    if($this->request->server['REQUEST_METHOD'] == 'GET'){

      $this->load->model('catalog/category');
      $this->load->language('extension/module/filterapp');
      $this->load->model('catalog/product');
      $category_info = $this->model_catalog_category->getCategories();

      $data['filtered_products'] = array();
      if ( $category_info ) {
          if ( isset($_GET['checked']) ) {
              foreach ( $_GET['checked'] as $filterID ){
                  foreach( $category_info as $info ) {
                      $cat_id = $info['category_id'];

                      $results = $this->model_catalog_product->getProducts([
                          'filter_category_id' => $cat_id,
                          'filter_filter' => $filterID
                      ]);
                      if( $results ){
                          foreach ($results as $result) {
                              $data['filtered_products'][$result['product_id']] = array(
                                  'prod_id' => $result['product_id'],
                                  'prod_name' => $result['name'],
                                  'prod_image' => $this->config->get('config_url') . 'image/' . $result['image'],
                                  'prod_href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                                  'prod_price' => $result['price'],
                                  'prod_special' => $result['special'],
                                  'prod_stock' => $result['stock_status'],
                                  'prod_quantity' => $result['quantity'],
                                  'prod_mininmum' => $result['minimum']
                              );
                          }
                      }else{
                          $data['empty'] = $this->language->get('heading_empty');
                      }
                  }
              }
              $data['class'] = $_GET['is_path'] == 1 ? 'col-xl-4 col-lg-4 col-md-6 col-sm-6' : 'col-xl-3 col-lg-3 col-md-4 col-sm-6';
              $this->response->setOutput($this->load->view('extension/module/filtered_product', $data));
          }else{
              $json = array();
              $json['error'] = 'error';
              $this->response->setOutput(json_encode($json));
          }
      }
    }
  }
}