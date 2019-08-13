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

		// Remove category id's that are parent of others
		$whiteList = array();
		foreach ($cats as $categoryId) {
			$category = Mage::getModel('catalog/category')->load($categoryId);
			$parentCategories = $category->getParentCategories();
			foreach ($parentCategories as $parent) {
				$whiteList[] = $parent->getId();
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
			$hierarchy = $this->getParentCategory($category, $whiteList);
			if(count($hierarchy) > 0) {
				$hierarchies[] = $hierarchy;
			}
		}
		return $hierarchies;
	}

	private function getParentCategory($category, $whiteList) {
		$parent = $category->getParentCategory();
		if($parent->getParentId()) {
			$list = $this->getParentCategory($parent, $whiteList);
		} else {
			$list = array();
		}
		if(in_array($category->getId(), $whiteList)) {
			$name = trim($category->getName());
			if($name != "")	{
				$list[] = $name;
			}
		}
		return $list;
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