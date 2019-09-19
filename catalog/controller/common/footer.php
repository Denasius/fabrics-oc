<?php
class ControllerCommonFooter extends Controller {
	public function index() {
		$this->load->language('common/footer');

		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
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
		// Условия
		$data['general_terms_link'] = $this->url->link('information/information', 'information_id=7');
		// Обратная связь
		$data['feedback_link'] = $this->url->link('information/information', 'information_id=8');

		// Соц сети иконки
		$data['social_1_icon'] = $this->config->get('config_social_1_title');
		$data['social_2_icon'] = $this->config->get('config_social_2_title');
		$data['social_3_icon'] = $this->config->get('config_social_3_title');
		$data['social_4_icon'] = $this->config->get('config_social_4_title');
		$data['social_5_icon'] = $this->config->get('config_social_5_title');

		// Ссылки на соцсети
		$data['social_1_link'] = $this->config->get('config_social_1_link');
		$data['social_2_link'] = $this->config->get('config_social_2_link');
		$data['social_3_link'] = $this->config->get('config_social_3_link');
		$data['social_4_link'] = $this->config->get('config_social_4_link');
		$data['social_5_link'] = $this->config->get('config_social_5_link');


		$data['email'] = $this->config->get('config_email');
		$data['telephone_link'] = str_replace([' ', '+', '-', '(', ')'], '', $this->config->get('config_telephone'));
		$data['telephone'] = $this->config->get('config_telephone');
		$data['additional_telephone'] = $this->config->get('config_fax');
		$data['additional_telephone_link'] = str_replace([' ', '+', '-', '(', ')'], '', $this->config->get('config_fax'));

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name'), date('Y', time()));
		$data['current_year'] = date('Y', time());

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		$data['scripts'] = $this->document->getScripts('footer');
		
		return $this->load->view('common/footer', $data);
	}
}
