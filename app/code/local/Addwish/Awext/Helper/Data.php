<?php

class Addwish_Awext_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getProductData($product, $extraAttributes = array()) {
		$data = array();

		$data['url'] = $product->getProductUrl();
		$data['title'] = $product->getName();
		$data['imgurl'] = $this->getProductImageUrl($product);

		$specialPrice = $this->getProductPrice($product, true);
		$regularPrice = $this->getProductPrice($product, false);
		if($specialPrice < $regularPrice) {
			$data['price'] = $specialPrice;
			$data['previousprice'] = $regularPrice;
		} else {
			$data['price'] = $regularPrice;
		}
		
		$data['keywords'] = $product->getMetaKeyword();
		$data['description'] = $product->getDescription();
		$data['id'] = $product->getId();
		$data['productnumber'] = $product->getSku();
		$data['currency'] = Mage::app()->getStore()->getCurrentCurrencyCode();
		$data['hierarchies'] = $this->getProductHierarchies($product);
		$data['brand'] = $product->getData('brand');
		$stockInfo = $this->getProductStockInfo($product);
		$data['instock'] = $stockInfo['inStock'];
		$data['qty'] = $stockInfo['qty'];
		$data['visibility'] = $product->getVisibility();


		if($product->getData('gender')) {
			$attr = $product->getResource()->getAttribute("gender");
			$genderLabel = $attr->getSource()->getOptionText($product->getData('gender'));
			$data['gender'] = $genderLabel;
		}
		foreach($extraAttributes as $attribute) {
			try {
				$attributeData = $product->getData($attribute);
				if($attributeData !== null) {
					$data[$attribute] = $product->getAttributeText($attribute);
					if($data[$attribute] === false || $data[$attribute] === array()) {
						$data[$attribute] = $attributeData;
					}
				}
			} catch(Exception $e)  {
				// Ignore attribute errors
			}
		}
		return $data;
	}

	// Helpers
	public function getProductImageUrl($product) {
		$imageUrl = Mage::helper('catalog/image')->init($product , 'thumbnail')->resize(256);
		return $imageUrl->__toString();
	}

	public function getProductPrice($product, $discountPrice = false) {
		if($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED) {
			$p = $this->cheapestProductFromGroup($product);
			if (isset($p)) {
				$product = $p;
			}
		}

		if ($discountPrice) {
			$price = $product->getFinalPrice();
		} else {
			$price = $product->getPrice();
		}

		$price = Mage::helper('tax')->getPrice($product, $price, true);
		$price = Mage::helper('core')->currency($price, false, false);
		return floatval($price);

	}

	private function cheapestProductFromGroup($product) {
		$cheapest = null;
		$associated = $product->getTypeInstance(true)->getAssociatedProducts($product);
		foreach ($associated as $p) {
			if ($cheapest == null || $cheapest->getFinalPrice() > $p->getFinalPrice()) {
				$cheapest = $p;
			}
		}
		return $cheapest;
	}


	public function getProductHierarchies($product) {
		$cats = $product->getCategoryIds();
		$categoryData = $this->getCategoryData();

		$hierarchies = array();
		$cats = array_unique($cats);
		foreach($cats as $i) {
			$inOther = false;
			foreach($cats as $j) {
				if(in_array($i, $categoryData['parentCategories'][$j])) {
					$inOther = true;
					break;
				}
			}
			if(!$inOther) {
				$hierarchy = array();
				$current = $i;
				while(isset($categoryData['parentForCategory'][$current])) {
					$next = $categoryData['parentForCategory'][$current];
					$hierarchy[] = $categoryData['categoryName'][$current];
					$current = $next;
				}
				if(count($hierarchy) > 0) {
					$hierarchies[]  = array_reverse($hierarchy);
				}
			}
		}
		return $hierarchies;
	}

	private static $_categoryData = null;
	private function getCategoryData() {

		if($this->_categoryData != null) {
			return $this->_categoryData;
		}
		$root = Mage::app()->getStore()->getRootCategoryId(); 
		
		$categories = Mage::getModel('catalog/category')
			->getCollection()
			->addAttributeToSelect('entity_id')
			->addAttributeToSelect('name')
			->addAttributeToSelect('parent_id')
			->addIsActiveFilter();

		$parentForCategory = array();
		$categoryName = array();
		foreach($categories as $cat){
			if($cat->entity_id == $root) {
				continue;
			}
			$parentForCategory[$cat->entity_id] = $cat->parent_id;
			$categoryName[$cat->entity_id] = $cat->name;
		}

		$parentList = array();
		foreach($categories as $cat){
			if($cat->entity_id == $root) {
				continue;
			}
			$parentForCategory[$cat->entity_id] = $cat->parent_id;
			$parentList[$cat->entity_id] = array();
			$i = $cat->entity_id;
			while(isset($parentForCategory[$i])) {
				$next = $parentForCategory[$i];
				$parentList[$cat->entity_id][] = $next;
				$i = $next;
			}
		}
		
		$this->_categoryData = array(
			'parentCategories' => $parentList,
			'parentForCategory' => $parentForCategory,
			'categoryName' => $categoryName
		);
		return $this->_categoryData;
	}

	public function getProductStockInfo($product) {

		if($product->isConfigurable()) {
			$allProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
			$inStock = false;
			foreach ($allProducts as $p) {
				$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($p);
				if ($p->isSaleable() && $stock->getIsInStock() != 0) {
					$inStock = true;
					break;
				}
			}
			return array('inStock' => $inStock, 'qty' => $stock ? $stock->getQty() : 0);
		} else {
			$inStock = false;
			$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
			if ($product->isSaleable() && $stock->getIsInStock() != 0) {
				$inStock = true;
			}
			return array('inStock' => $inStock, 'qty' => $stock->getQty());
		}
	}

}
