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
		$data['instock'] = $this->getProductInStock($product);

		if($product->getData('gender')) {
			$attr = $product->getResource()->getAttribute("gender");
			$genderLabel = $attr->getSource()->getOptionText($product->getData('gender'));
			$data['gender'] = $genderLabel;
		}
		foreach($extraAttributes as $attribute) {
			try {
				$data[$attribute] = $product->getAttributeText($attribute);
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
		
		$price = $product->getPrice();
		if ($discountPrice) {
			$price = Mage::getModel('catalogrule/rule')->calcProductPriceRule($product, $price);
			if (!isset($price)) {
				$price = $product->getFinalPrice();
			}
		}

		$pricesIncludeTax = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_PRICE_INCLUDES_TAX);

		if (!$pricesIncludeTax) {
            $taxClassId = $product->getTaxClassId();
			$taxCalculation = Mage::getModel('tax/calculation');
			$request = $taxCalculation->getRateRequest();
			$taxRate = $taxCalculation->getRate($request->setProductClassId($taxClassId));
			$price = ($price / 100 * $taxRate) + $price;
		}

		return floatval($price);

	}

	public function getProductHierarchies($product) {
		$cats = $product->getCategoryIds();

		// Remove category id's that are parent of others
		foreach ($cats as $categoryId) {
			$category = Mage::getModel('catalog/category')->load($categoryId);
			$parentCategories = $category->getParentCategories();
			foreach ($parentCategories as $parent) {
				if($categoryId == $parent->getId()) {
					continue;
				}
				$key = array_search($parent->getId(),$cats);
				if($key !== false) {
					unset($cats[$key]);
				}
			}
		}

		$hierarchies = array();
		foreach ($cats as $categoryId) {
			$category = Mage::getModel('catalog/category')->load($categoryId);
			$parentCategories = $category->getParentCategories();
			$hierarchy = array();
			foreach ($parentCategories as $parent) {
				$name = trim($parent->getName());
				if($name != "") {
					$hierarchy[] = $name;
				}
			}
			if(count($hierarchy) > 0) {
				$hierarchies[] = $hierarchy;
			}
		}
		return $hierarchies;
	}

	public function getProductInStock($product) {
		$inStock = false;
		if($product->isConfigurable()) {
			$allProducts = $product->getTypeInstance(true)->getUsedProducts(null, $product);
			foreach ($allProducts as $productD) {
				if (!$productD->isSaleable() || $productD->getIsInStock() == 0) {
					//out of stock for check child simple product
				} else {
					$inStock = true;
				}
			}
		} else {
			$inStock = $product->isInStock();
		}
		return $inStock;
	}

}