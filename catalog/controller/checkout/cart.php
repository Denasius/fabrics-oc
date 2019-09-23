<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCheckoutCart extends Controller {
	public function index() {
		$this->load->language('checkout/cart');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,follow');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home'),
			'text' => $this->language->get('text_home')
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('checkout/cart'),
			'text' => $this->language->get('heading_title')
		);
        if (!$this->customer->isLogged()) {
            $data['customer_email'] = '';
        } else {
            $data['customer_email'] = $this->customer->getEmail();
        }

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
			if (!$this->cart->hasStock() && (!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning'))) {
				$data['error_warning'] = $this->language->get('error_stock');
			} elseif (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if ($this->config->get('config_customer_price') && !$this->customer->isLogged()) {
				$data['attention'] = sprintf($this->language->get('text_login'), $this->url->link('account/login'), $this->url->link('account/register'));
			} else {
				$data['attention'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			$data['action'] = $this->url->link('checkout/cart/edit', '', true);

			if ($this->config->get('config_cart_weight')) {
				$data['weight'] = $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point'));
			} else {
				$data['weight'] = '';
			}

			$this->load->model('tool/image');
			$this->load->model('tool/upload');
			$this->load->model('catalog/product');


			$data['products'] = array();

			$products = $this->cart->getProducts();
			//print_r($products);
			foreach ($products as $product) {

                $product_total = 0;

                foreach ($products as $product_2) {
					if ($product_2['product_id'] == $product['product_id']) {
						$product_total += $product_2['quantity'];
					}
				}

				if ($product['minimum'] > $product_total) {
					$data['error_warning'] = sprintf($this->language->get('error_minimum'), $product['name'], $product['minimum']);
				}

				if ($product['image']) {
					$image = $this->model_tool_image->resize($product['image'], 77, 77);
				} else {
					$image = '';
				}

				$option_data = array();

				foreach ($product['option'] as $option) {
					if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

						if ($upload_info) {
							$value = $upload_info['name'];
						} else {
							$value = '';
						}
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
                        'type' => $option['type'],
                        'id' => $option['option_id']
					);
				}

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));


                    $price = $this->currency->format($unit_price, $this->session->data['currency']);
					$total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
				} else {
					$price = false;
					$total = false;
				}

				$recurring = '';

				if ($product['recurring']) {
					$frequencies = array(
						'day'        => $this->language->get('text_day'),
						'week'       => $this->language->get('text_week'),
						'semi_month' => $this->language->get('text_semi_month'),
						'month'      => $this->language->get('text_month'),
						'year'       => $this->language->get('text_year')
					);

					if ($product['recurring']['trial']) {
						$recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
					}

					if ($product['recurring']['duration']) {
						$recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					} else {
						$recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
					}
				}


				$data['products'][] = array(
					'cart_id'   => $product['cart_id'],
					'thumb'     => $image,
					'name'      => $product['name'],
					'model'     => $product['model'],
					'option'    => $option_data,
					'recurring' => $recurring,
					'quantity'  => $product['quantity'],
					'stock'     => $product['stock'] ? true : !(!$this->config->get('config_stock_checkout') || $this->config->get('config_stock_warning')),
					'reward'    => ($product['reward'] ? sprintf($this->language->get('text_points'), $product['reward']) : ''),
					'price'     => $price,
					'total'     => $total,
					'href'      => $this->url->link('product/product', 'product_id=' . $product['product_id'])
				);
			}
			// Gift Voucher
			$data['vouchers'] = array();

			if (!empty($this->session->data['vouchers'])) {
				foreach ($this->session->data['vouchers'] as $key => $voucher) {
					$data['vouchers'][] = array(
						'key'         => $key,
						'description' => $voucher['description'],
						'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency']),
						'remove'      => $this->url->link('checkout/cart', 'remove=' . $key)
					);
				}
			}

			// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;
			
			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);
			
			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);
						
						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$data['totals'] = array();

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
				);
			}

			$data['continue'] = $this->url->link('common/home');

			$data['checkout'] = $this->url->link('checkout/checkout', '', true);

			$this->load->model('setting/extension');

			$data['modules'] = array();
			
			$files = glob(DIR_APPLICATION . '/controller/extension/total/*.php');

			if ($files) {
				foreach ($files as $file) {
					$result = $this->load->controller('extension/total/' . basename($file, '.php'));
					
					if ($result) {
						$data['modules'][] = $result;
					}
				}
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$data['supplier_1'] = $this->config->get('config_supplier_1');
			$data['supplier_2'] = $this->config->get('config_supplier_2');
			$data['supplier_3'] = $this->config->get('config_supplier_3');
            for( $i = 0; $i <= 200; $i++ ){
                $data['config_supplier_1_' .$i] = $this->config->get('config_supplier_1_' .$i);
            }

            for( $j = 0; $j <= 200; $j++ ){
                $data['config_supplier_2_' .$j] = $this->config->get('config_supplier_2_' .$j);
            }

            for( $k = 0; $k <= 200; $k++ ){
                $data['config_supplier_3_' .$k] = $this->config->get('config_supplier_3_' .$k);
            }

            $this->response->setOutput($this->load->view('checkout/cart', $data));
		} else {
			$data['text_error'] = $this->language->get('text_empty');
			
			$data['continue'] = $this->url->link('common/home');

			unset($this->session->data['success']);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/cart_not_found', $data));
		}
	}

	public function add() {

        $this->load->language('checkout/cart');

		$json = array();

		if (isset($this->request->post['product_id'])) {
			$product_id = (int)$this->request->post['product_id'];
		} else {
			$product_id = 0;
		}

		$this->load->model('catalog/product');

		$product_info = $this->model_catalog_product->getProduct($product_id);

		if ($product_info) {
			if (isset($this->request->post['quantity'])) {
				$quantity = (int)$this->request->post['quantity'];
			} else {
				$quantity = 1;
			}

			if (isset($this->request->post['option'])) {
                $option = array_filter($this->request->post['option']);

            } else {
				$option = array();
			}


			$product_options = $this->model_catalog_product->getProductOptions($this->request->post['product_id']);

			foreach ($product_options as $product_option) {
				if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
					$json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
				}
			}

			if (isset($this->request->post['recurring_id'])) {
				$recurring_id = $this->request->post['recurring_id'];
			} else {
				$recurring_id = 0;
			}

			$recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

			if ($recurrings) {
				$recurring_ids = array();

				foreach ($recurrings as $recurring) {
					$recurring_ids[] = $recurring['recurring_id'];
				}

				if (!in_array($recurring_id, $recurring_ids)) {
					$json['error']['recurring'] = $this->language->get('error_recurring_required');
				}
			}

			if (!$json) {
                $this->cart->add($this->request->post['product_id'], $quantity, $option, $recurring_id);

                $json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));

				// Unset all shipping and payment methods
				unset($this->session->data['shipping_method']);
				unset($this->session->data['shipping_methods']);
				unset($this->session->data['payment_method']);
				unset($this->session->data['payment_methods']);

				// Totals
				$this->load->model('setting/extension');

				$totals = array();
				$taxes = $this->cart->getTaxes();
				$total = 0;

				// Because __call can not keep var references so we put them into an array.

				$total_data = array(
					'totals' => &$totals,
					'taxes'  => &$taxes,
					'total'  => &$total,
				);

				// Display prices
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$sort_order = array();

					$results = $this->model_setting_extension->getExtensions('total');

					foreach ($results as $key => $value) {
						$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
					}

					array_multisort($sort_order, SORT_ASC, $results);

					foreach ($results as $result) {
						if ($this->config->get('total_' . $result['code'] . '_status')) {
							$this->load->model('extension/total/' . $result['code']);

							// We have to put the totals in an array so that they pass by reference.
							$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
						}
					}

					$sort_order = array();

					foreach ($totals as $key => $value) {
						$sort_order[$key] = $value['sort_order'];
					}

					array_multisort($sort_order, SORT_ASC, $totals);
				}
                /*
                 * Сюда нельзя добавлять другие атрибуты, так как наличие !!!выбранных атрибутов будет автоматически выводить товар как образец товара
                 * */
//                if ( isset($product_options) && !empty($product_options) ) {
//                    $example_price = !empty($product_info['mpn']) ? $product_info['mpn'] : 1;
//                    $json['curr'] = sprintf($this->language->get('text_curr'), $this->currency->format($example_price, $this->session->data['currency']));
//                }else{
//                    $json['curr'] = sprintf($this->language->get('text_curr'), $this->currency->format($total, $this->session->data['currency']));
//                }

				$json['total'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
                $json['curr'] = sprintf($this->language->get('text_curr'), $this->currency->format($total, $this->session->data['currency']));

				// $json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
			} else {
				$json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function edit() {
		$this->load->language('checkout/cart');

		$json = array();

		// Update
		if (!empty($this->request->post['quantity'])) {
			foreach ($this->request->post['quantity'] as $key => $value) {
				$this->cart->update($key, $value);
			}

			$this->session->data['success'] = $this->language->get('text_remove');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			$this->response->redirect($this->url->link('checkout/cart'));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function remove() {
		$this->load->language('checkout/cart');

		$json = array();

		// Remove
		if (isset($this->request->post['key'])) {
			$this->cart->remove($this->request->post['key']);

			unset($this->session->data['vouchers'][$this->request->post['key']]);

			$json['success'] = $this->language->get('text_remove');

			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['reward']);

			// Totals
			$this->load->model('setting/extension');

			$totals = array();
			$taxes = $this->cart->getTaxes();
			$total = 0;

			// Because __call can not keep var references so we put them into an array. 			
			$total_data = array(
				'totals' => &$totals,
				'taxes'  => &$taxes,
				'total'  => &$total
			);

			// Display prices
			if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
				$sort_order = array();

				$results = $this->model_setting_extension->getExtensions('total');

				foreach ($results as $key => $value) {
					$sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
				}

				array_multisort($sort_order, SORT_ASC, $results);

				foreach ($results as $result) {
					if ($this->config->get('total_' . $result['code'] . '_status')) {
						$this->load->model('extension/total/' . $result['code']);

						// We have to put the totals in an array so that they pass by reference.
						$this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
					}
				}

				$sort_order = array();

				foreach ($totals as $key => $value) {
					$sort_order[$key] = $value['sort_order'];
				}

				array_multisort($sort_order, SORT_ASC, $totals);
			}

			$json['total'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
			$json['curr'] = sprintf($this->language->get('text_curr'), $this->currency->format($total, $this->session->data['currency']));

			//$json['total'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function makeOrder(){

        if($this->request->server['REQUEST_METHOD'] == 'POST'){
            /*
             * Валидирую обязательные поля
             * */
            $this->load->language('checkout/cart');
            $json = array();
            //print_r($_POST);exit;
            if ( isset($this->request->post['name']) && ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) ) {
                $json['error'][] = $this->language->get('error_name');
            }
            if ( isset($this->request->post['email']) && ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL))) {
                $json['error'][] = $this->language->get('error_email');
            }
            if ( isset($this->request->post['company']) && ( (utf8_strlen($this->request->post['company']) < 3) || (utf8_strlen($this->request->post['company']) > 25) )) {
                $json['error'][] = $this->language->get('error_company');
            }
            if ( isset($this->request->post['phone']) && ( (utf8_strlen($this->request->post['phone']) < 8) || (utf8_strlen($this->request->post['phone']) > 25) )) {
                $json['error'][] = $this->language->get('error_phone');
            }
            if ( isset($this->request->post['legal_address']) && ( (utf8_strlen($this->request->post['legal_address']) < 3) || (utf8_strlen($this->request->post['legal_address']) > 25) )) {
                $json['error'][] = $this->language->get('error_legal_address');
            }
            if ( isset($this->request->post['country']) && ( (utf8_strlen($this->request->post['country']) < 3) || (utf8_strlen($this->request->post['country']) > 25) )) {
                $json['error'][] = $this->language->get('error_country');
            }
            if ( isset($this->request->post['region']) && ( (utf8_strlen($this->request->post['region']) < 3) || (utf8_strlen($this->request->post['region']) > 25) )) {
                $json['error'][] = $this->language->get('error_region');
            }
            if ( isset($this->request->post['town']) && ( (utf8_strlen($this->request->post['town']) < 3) || (utf8_strlen($this->request->post['town']) > 25) )) {
                $json['error'][] = $this->language->get('error_town');
            }
            if ( isset($this->request->post['index']) && ( (utf8_strlen($this->request->post['index']) < 3) || (utf8_strlen($this->request->post['index']) > 25) )) {
                $json['error'][] = $this->language->get('error_index');
            }
            if ( isset($this->request->post['street']) && ( (utf8_strlen($this->request->post['street']) < 3) || (utf8_strlen($this->request->post['street']) > 25) )) {
                $json['error'][] = $this->language->get('error_street');
            }
            if ( isset($this->request->post['build']) && ( (utf8_strlen($this->request->post['build']) < 1) || (utf8_strlen($this->request->post['build']) > 25) )) {
                $json['error'][] = $this->language->get('error_build');
            }
            if ( isset($this->request->post['apartment']) && ( (utf8_strlen($this->request->post['apartment']) < 1) || (utf8_strlen($this->request->post['apartment']) > 25) )) {
                $json['error'][] = $this->language->get('error_apartment');
            }
            if ( isset($this->request->post['comment']) && ( (utf8_strlen($this->request->post['comment']) < 5) || (utf8_strlen($this->request->post['comment']) > 1000) )) {
                $json['error'][] = $this->language->get('error_comment');
            }
            if (( isset($this->request->post['type']) && $this->request->post['type'] == $this->language->get('legal_persone') ) && !isset($this->request->post['confirm'])) {
                $json['error'][] = $this->language->get('error_confirm');
            }
            if (( isset($this->request->post['type']) && $this->request->post['type'] == $this->language->get('nat_persone') ) && !isset($this->request->post['confirm_2'])) {
                $json['error'][] = $this->language->get('error_confirm');
            }
            if ( ! $json ) {
                /*
                 * Ложу заказ в БД
                 * */

                $this->load->model('catalog/product');
                $language = new Language($this->language->get('code'));
                $language->load($this->language->get('code'));
                $language->load('mail/order_add');

                $order_data = array();
                $totals = array();
                $taxes = $this->cart->getTaxes();
                $total = 0;

                // Because __call can not keep var references so we put them into an array.
                $total_data = array(
                    'totals' => &$totals,
                    'taxes'  => &$taxes,
                    'total'  => &$total
                );

                $this->load->model('setting/extension');

                $sort_order = array();

                $results = $this->model_setting_extension->getExtensions('total');

                foreach ($results as $key => $value) {
                    $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
                }

                array_multisort($sort_order, SORT_ASC, $results);

                foreach ($results as $result) {
                    if ($this->config->get('total_' . $result['code'] . '_status')) {
                        $this->load->model('extension/total/' . $result['code']);

                        // We have to put the totals in an array so that they pass by reference.
                        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                    }
                }

                $sort_order = array();

                foreach ($totals as $key => $value) {
                    $sort_order[$key] = $value['sort_order'];
                }

                array_multisort($sort_order, SORT_ASC, $totals);

                $order_data['totals'] = $totals;

                $this->load->language('checkout/checkout');

                $order_data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
                $order_data['store_id'] = $this->config->get('config_store_id');
                $order_data['store_name'] = $this->config->get('config_name');

                if ($order_data['store_id']) {
                    $order_data['store_url'] = $this->config->get('config_url');
                } else {
                    if ($this->request->server['HTTPS']) {
                        $order_data['store_url'] = HTTPS_SERVER;
                    } else {
                        $order_data['store_url'] = HTTP_SERVER;
                    }
                }

                $this->load->model('account/customer');

                if ($this->customer->isLogged()) {
                    $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

                    $order_data['customer_id'] = $this->customer->getId();
                    $order_data['firstname'] = $customer_info['firstname'];
                } elseif (isset($this->session->data['guest'])) {
                    $order_data['customer_id'] = 0;
                    $order_data['firstname'] = $this->request->post['name'];
                }else{
                    $order_data['customer_id'] = 0;
                    $order_data['firstname'] = $this->request->post['name'];
                }
                $order_data['customer_group_id'] = 1;
                $order_data['lastname'] = ' ';
                $order_data['email'] = $this->request->post['email'];
                $order_data['telephone'] = $this->request->post['phone'];
                $order_data['custom_field'] = ' ';

                $order_data['payment_firstname'] = $this->request->post['name'];
                $order_data['payment_lastname'] = '';
                $order_data['payment_company'] = $this->request->post['company'];
                $order_data['payment_address_1'] = isset($this->request->post['country']) ? $this->request->post['country'] . ' - ' . $this->request->post['region'] . ' - ' . $this->request->post['town'] : ' ';
                $order_data['payment_address_2'] = isset($this->request->post['legal_address']) ? $this->request->post['legal_address'] : ' ';
                $order_data['payment_city'] = $this->request->post['town'];
                $order_data['payment_postcode'] = $this->request->post['index'];
                $order_data['payment_zone'] = $this->request->post['region'];
                $order_data['payment_zone_id'] = 339;
                $order_data['payment_country'] = $this->request->post['country'];
                $order_data['payment_country_id'] = 20;
                $order_data['payment_address_format'] = ' ';
                $order_data['payment_custom_field'] = (isset($this->session->data['payment_address']['custom_field']) ? $this->session->data['payment_address']['custom_field'] : array());
                $order_data['payment_method'] = isset($this->request->post['n']) ? $this->request->post['n'] . '( ' . $this->request->post['shipping_address'] . ' )' : ' ';
                $order_data['payment_code'] = 'cod';
                $order_data['shipping_firstname'] = '';
                $order_data['shipping_lastname'] = '';
                $order_data['shipping_company'] = '';
                $order_data['shipping_address_1'] = '';
                $order_data['shipping_address_2'] = '';
                $order_data['shipping_city'] = isset($this->request->post['town']) ? $this->request->post['town'] : '';
                $order_data['shipping_postcode'] = '';
                $order_data['shipping_zone'] = '';
                $order_data['shipping_zone_id'] = '';
                $order_data['shipping_country'] = '';
                $order_data['shipping_country_id'] = '';
                $order_data['shipping_address_format'] = '';
                $order_data['shipping_custom_field'] = array();
                $order_data['shipping_method'] = isset($this->request->post['n']) ? $this->request->post['n']  . '( ' . $this->request->post['shipping_address'] . ' )' : '';
                $order_data['shipping_code'] = '';

                $order_data['products'] = array();

                foreach ($this->cart->getProducts() as $product) {
                    $option_data = array();

                    foreach ($product['option'] as $option) {
                        $option_data[] = array(
                            'product_option_id'       => $option['product_option_id'],
                            'product_option_value_id' => $option['product_option_value_id'],
                            'option_id'               => $option['option_id'],
                            'option_value_id'         => $option['option_value_id'],
                            'name'                    => $option['name'],
                            'value'                   => $option['value'],
                            'type'                    => $option['type']
                        );
                    }

                    $order_data['products'][] = array(
                        'product_id' => $product['product_id'],
                        'name'       => $product['name'],
                        'model'      => $product['model'],
                        'option'     => $option_data,
                        'download'   => $product['download'],
                        'quantity'   => $product['quantity'],
                        'subtract'   => $product['subtract'],
                        'price'      => $product['price'],
                        'total'      => $product['total'],
                        'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                        'reward'     => $product['reward']
                    );
                }

                $order_data['comment'] = $this->session->data['comment'];
                $order_data['total'] = $total_data['total'];
                $order_data['affiliate_id'] = 0;
                $order_data['commission'] = 0;
                $order_data['marketing_id'] = 0;
                $order_data['tracking'] = '';

                $order_data['language_id'] = $this->config->get('config_language_id');
                $order_data['currency_id'] = $this->currency->getId($this->session->data['currency']);
                $order_data['currency_code'] = $this->session->data['currency'];
                $order_data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
                $order_data['ip'] = $this->request->server['REMOTE_ADDR'];

                if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
                    $order_data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
                } elseif (!empty($this->request->server['HTTP_CLIENT_IP'])) {
                    $order_data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
                } else {
                    $order_data['forwarded_ip'] = '';
                }

                if (isset($this->request->server['HTTP_USER_AGENT'])) {
                    $order_data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
                } else {
                    $order_data['user_agent'] = '';
                }

                if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
                    $order_data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
                } else {
                    $order_data['accept_language'] = '';
                }

                $this->load->model('checkout/order');

                $this->session->data['order_id'] = $this->model_checkout_order->addOrder($order_data);

                $this->load->model('tool/upload');

                $data['products'] = array();

                foreach ($this->cart->getProducts() as $product) {
                    $option_data = array();

                    foreach ($product['option'] as $option) {
                        if ($option['type'] != 'file') {
                            $value = $option['value'];
                        } else {
                            $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                            if ($upload_info) {
                                $value = $upload_info['name'];
                            } else {
                                $value = '';
                            }
                        }

                        $option_data[] = array(
                            'name'  => $option['name'],
                            'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
                        );
                    }

                    $recurring = '';

                    if ($product['recurring']) {
                        $frequencies = array(
                            'day'        => $this->language->get('text_day'),
                            'week'       => $this->language->get('text_week'),
                            'semi_month' => $this->language->get('text_semi_month'),
                            'month'      => $this->language->get('text_month'),
                            'year'       => $this->language->get('text_year'),
                        );

                        if ($product['recurring']['trial']) {
                            $recurring = sprintf($this->language->get('text_trial_description'), $this->currency->format($this->tax->calculate($product['recurring']['trial_price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['trial_cycle'], $frequencies[$product['recurring']['trial_frequency']], $product['recurring']['trial_duration']) . ' ';
                        }

                        if ($product['recurring']['duration']) {
                            $recurring .= sprintf($this->language->get('text_payment_description'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                        } else {
                            $recurring .= sprintf($this->language->get('text_payment_cancel'), $this->currency->format($this->tax->calculate($product['recurring']['price'] * $product['quantity'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']), $product['recurring']['cycle'], $frequencies[$product['recurring']['frequency']], $product['recurring']['duration']);
                        }
                    }

                    $data['products'][] = array(
                        'cart_id'    => $product['cart_id'],
                        'product_id' => $product['product_id'],
                        'name'       => $product['name'],
                        'model'      => $product['model'],
                        'option'     => $option_data,
                        'recurring'  => $recurring,
                        'quantity'   => $product['quantity'],
                        'subtract'   => $product['subtract'],
                        'price'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
                        'total'      => $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']),
                        'href'       => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                    );
                }
                $data['vouchers'] = array();

                if (!empty($this->session->data['vouchers'])) {
                    foreach ($this->session->data['vouchers'] as $voucher) {
                        $data['vouchers'][] = array(
                            'description' => $voucher['description'],
                            'amount'      => $this->currency->format($voucher['amount'], $this->session->data['currency'])
                        );
                    }
                }

                $data['totals'] = array();

                foreach ($order_data['totals'] as $total) {
                    $data['totals'][] = array(
                        'title' => $total['title'],
                        'text'  => $this->currency->format($total['value'], $this->session->data['currency'])
                    );
                }
                $this->load->model('setting/setting');

                $from = $this->model_setting_setting->getSettingValue('config_email', $order_data['store_id']);
                if (!$from) {
                    $from = $this->config->get('config_email');
                }

                /*
                 * Отправляю письмо
                 * */
                $download_status = false;

                $language = new Language($this->language->get('code'));
                $language->load($this->language->get('code'));
                $language->load('mail/order_add');

                // HTML Mail
                $data['title'] = sprintf($language->get('text_subject'), $order_data['store_name'], $this->session->data['order_id']);

                $data['text_greeting'] = sprintf($language->get('text_greeting'), $order_data['store_name']);
                $data['text_link'] = $language->get('text_link');
                $data['text_download'] = $language->get('text_download');
                $data['text_order_detail'] = $language->get('text_order_detail');
                $data['text_instruction'] = $language->get('text_instruction');
                $data['text_order_id'] = $language->get('text_order_id');
                $data['text_date_added'] = $language->get('text_date_added');
                $data['text_payment_method'] = $language->get('text_payment_method');
                $data['text_shipping_method'] = $language->get('text_shipping_method');
                $data['text_email'] = $language->get('text_email');
                $data['text_telephone'] = $language->get('text_telephone');
                $data['text_ip'] = $language->get('text_ip');
                $data['text_order_status'] = $language->get('text_order_status');
                $data['text_payment_address'] = $language->get('text_payment_address');
                $data['text_shipping_address'] = $language->get('text_shipping_address');
                $data['text_product'] = $language->get('text_product');
                $data['text_model'] = $language->get('text_model');
                $data['text_quantity'] = $language->get('text_quantity');
                $data['text_price'] = $language->get('text_price');
                $data['text_total'] = $language->get('text_total');
                $data['text_footer'] = $language->get('text_footer');

                $data['logo'] = $order_data['store_url'] . 'image/' . $this->config->get('config_logo');
                $data['store_name'] = $order_data['store_name'];
                $data['store_url'] = $order_data['store_url'];
                $data['customer_id'] = $order_data['customer_id'];
                $data['link'] = $order_data['store_url'] . 'index.php?route=account/order/info&order_id=' . $this->session->data['order_id'];

                if ($download_status) {
                    $data['download'] = $order_data['store_url'] . 'index.php?route=account/download';
                } else {
                    $data['download'] = '';
                }

                $data['order_id'] = $this->session->data['order_id'];
                $data['date_added'] = date($language->get('date_format_short'), strtotime("now"));
                $data['payment_method'] =  $order_data['payment_method'];
                $data['shipping_method'] = $order_data['shipping_method'];
                $data['email'] = $order_data['email'];
                $data['telephone'] = $order_data['telephone'];
                $data['ip'] = $order_data['ip'];

                $order_status_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_status WHERE order_status_id = 1 AND language_id = '" . (int)$order_data['language_id'] . "'");

                if ($order_status_query->num_rows) {
                    $data['order_status'] = $order_status_query->row['name'];
                } else {
                    $data['order_status'] = '';
                }

                $data['comment'] = isset($this->request->post['comment']) ? nl2br($this->request->post['comment']) : '';


                if ($order_data['payment_address_format']) {
                    $format = $order_data['payment_address_format'];
                } else {
                    $format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
                }

                $find = array(
                    '{firstname}',
                    '{lastname}',
                    '{company}',
                    '{address_1}',
                    '{address_2}',
                    '{city}',
                    '{postcode}',
                    '{zone}',
                    '{zone_code}',
                    '{country}'
                );

                $replace = array(
                    'firstname' => $order_data['payment_firstname'],
                    'lastname'  => $order_data['payment_lastname'],
                    'company'   => $order_data['payment_company'],
                    'address_1' => $order_data['payment_address_1'],
                    'address_2' => $order_data['payment_address_2'],
                    'city'      => $order_data['payment_city'],
                    'postcode'  => $order_data['payment_postcode'],
                    'zone'      => $order_data['payment_zone'],
                    'zone_code' => $order_data['payment_zone'],
                    'country'   => $order_data['payment_country']
                );

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                $mail->setTo($order_data['email']);
                $mail->setFrom($from);
                $mail->setSender(html_entity_decode($order_data['store_name'], ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(html_entity_decode(sprintf($language->get('text_subject'), $order_data['store_name'], $this->session->data['order_id']), ENT_QUOTES, 'UTF-8'));
                $mail->setHtml($this->load->view('mail/order_add', $data));
                $mail->send();

                /*
                 * Очищаю корзину
                 * */
                $this->cart->clear();
            }else{
                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
            }

        }
    }
}
