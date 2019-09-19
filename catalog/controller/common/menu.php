<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {
					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$children_data[] = array(
						'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
					);
				}

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('catalog/category', 'path=' . $category['category_id'])
				);
			}
		}

		$data['contact_link'] = $this->url->link('information/contact');
		// Доставка
		$data['delivery_link'] = $this->url->link('information/information', 'information_id=3');
		// О нас
		$data['about_link'] = $this->url->link('information/information', 'information_id=4');
		// Оплата
		$data['payment_link'] = $this->url->link('information/information', 'information_id=5');
		// Производство
		$data['production_link'] = $this->url->link('blog/category', 'blog_category_id=78');

		return $this->load->view('common/menu', $data);
	}
}
