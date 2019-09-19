<?php
class ControllerExtensionModuleCategoryproduct extends Controller {
	public function index() {
		// TODO добавить в админку оповещение о запрете удаления кактегории Каталог с id 59
		$this->load->language('extension/module/categoryproduct');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 59;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 59;
		}

		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

    $categories = $this->model_catalog_category->getCategories($data['category_id']);

		foreach ($categories as $category) {
			

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'column'   => $category['column'] ? $category['column'] : 1,
					'id' 	   => $category['category_id'],
					'href'     => $this->url->link('product/category', 'path=' . $category['parent_id'] . '_' . $category['category_id'])
				);
			
		}
		return $this->load->view('extension/module/categoryproduct', $data);
	}
}