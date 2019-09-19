<?php
class ControllerExtensionModuleCats extends Controller {
	public function index() {

		// TODO добавить в админку оповещение о запрете удаления кактегории Каталог с id 59
		$this->load->language('extension/module/cats');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories();


		foreach ($categories as $category) {
			// Если id категории 59, значит это Каталог
			if ($category['top'] && $category['category_id'] == 59) {
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
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
						'id' => $child['category_id'],
						'image' => $this->config->get('config_url') . 'image/' . $child['image']
					);
				}

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'id' 	   => $category['category_id'],
					'href'     => $this->url->link('catalog/category', 'path=' . $category['category_id'])
				);
			}
		}
		return $this->load->view('extension/module/cats', $data);
	}
}